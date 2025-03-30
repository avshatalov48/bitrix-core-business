/* eslint-disable no-param-reassign */
/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */
/* eslint-disable @bitrix24/bitrix24-rules/no-pseudo-private */
/* eslint-disable no-underscore-dangle */
// noinspection ES6PreferShortImport

import {
	CloseReasons,
	ConnectionType,
	PullStatus,
	RpcMethod,
	ServerMode,
	SystemCommands,
	REVISION,
} from '../../client/src/consts';
import {
	buildQueryString,
	getDateForLog,
	isPlainObject,
	isNotEmptyString,
	isWebSocketSupported,
	isArray, browser, getTimestamp,
} from '../../util/src/util';
import { JsonRpc } from '../../jsonrpc/src/jsonrpc';
import type { RestCaller } from '../../minirest/src/restcaller';

import { ChannelManager } from './codec/channelmanager';
import { WebSocketConnector } from './transport/websocket';
import { LongPollingConnector } from './transport/longpolling';
import { ProtobufCodec } from './codec/protobuf';
import { LegacyCodec } from './codec/legacy';
import type { MessageCodec } from './codec/messagecodec';
import type { PullConfig } from '../../configholder/src/configholder';
import type { JsonRpcResponse } from '../../jsonrpc/src/jsonrpc';
import type { StorageManager } from '../../client/src/storage';

const RESTORE_WEBSOCKET_TIMEOUT = 30 * 60;

const MAX_IDS_TO_STORE = 10;

const PING_TIMEOUT = 10;
const JSON_RPC_PING = 'ping';
const JSON_RPC_PONG = 'pong';

const LS_SESSION = 'bx-pull-session';

// const LS_SESSION_CACHE_TIME = 20;

export interface Logger
{
	log(message: string, ...params): void,

	logForce(message: string, ...params): void,
}

type ConnectorOptions = {
	config: ?PullConfig,
	storage: ?StorageManager,
	events: { [key: $Values<typeof ConnectorEvents>]: (e: CustomEvent) => void },
	restoreSession: boolean,
	getPublicListMethod: string,
	restClient: RestCaller,
	logger: Logger,
}

export const ConnectorEvents = {
	Message: 'message',
	RevisionChanged: 'revisionChanged',
	ChannelReplaced: 'channelReplaced',
	ConfigExpired: 'configExpired',
	ConnectionStatus: 'connectionStatus',
	ConnectionError: 'connectionError',
};

type PromiseResolver = {
	resolve: () => {},
	reject: () => {},
}

export class Connector extends EventTarget
{
	config: ?PullConfig;
	codec: ?MessageCodec;
	logger: ?Logger;

	connectors = {
		webSocket: null,
		longPolling: null,
	};

	connectPromises: PromiseResolver[] = [];
	pingWaitTimeout: number | null = null;
	reconnectTimeout: number | null = null;
	isWebsocketBlocked = false;
	isLongPollingBlocked = false;
	isManualDisconnect = false;

	_status = PullStatus.Offline;

	connectionAttempt = 0;

	constructor(options: ConnectorOptions = {})
	{
		super();

		this.config = options.config;
		this.logger = options.logger;
		this.storage = options.storage;

		this.isSecure = globalThis.location.protocol === 'https:';

		this.connectors.webSocket = new WebSocketConnector({
			pathGetter: () => this.getConnectionPathByType(ConnectionType.WebSocket),
			onOpen: this.onWebSocketOpen.bind(this),
			onMessage: this.onIncomingMessage.bind(this),
			onDisconnect: this.onWebSocketDisconnect.bind(this),
			onError: this.onWebSocketError.bind(this),
		});

		this.connectors.longPolling = new LongPollingConnector({
			pathGetter: () => this.getConnectionPathByType(ConnectionType.LongPolling),
			isBinary: this.isProtobufSupported() && !this.isJsonRpc(),
			onOpen: this.onLongPollingOpen.bind(this),
			onMessage: this.onIncomingMessage.bind(this),
			onDisconnect: this.onLongPollingDisconnect.bind(this),
			onError: this.onLongPollingError.bind(this),
		});

		this.connectionType = this.isWebSocketAllowed() ? ConnectionType.WebSocket : ConnectionType.LongPolling;

		for (const eventName of Object.keys(options.events || {}))
		{
			this.addEventListener(eventName, options.events[eventName]);
		}

		this.channelManager = new ChannelManager({
			restClient: options.restClient,
			getPublicListMethod: options.getPublicListMethod,
		});

		this.jsonRpcAdapter = this.createRpcAdapter();
		this.codec = this.createCodec();

		this.session = {
			mid: null,
			tag: null,
			time: null,
			history: {},
			lastMessageIds: [],
			messageCount: 0,
		};

		if (options.restoreSession && this.storage)
		{
			const oldSession = this.storage.get(LS_SESSION);
			const now = new Date();
			if (isPlainObject(oldSession) && 'ttl' in oldSession && oldSession.ttl >= now)
			{
				this.session.mid = oldSession.mid;
			}
		}
	}

	get status(): string
	{
		return this._status;
	}

	set status(status)
	{
		if (this._status === status)
		{
			return;
		}

		this._status = status;
		this.dispatchEvent(new CustomEvent(ConnectorEvents.ConnectionStatus, {
			detail: {
				status,
				connectionType: this.connector.connectionType,
			},
		}));
	}

	createRpcAdapter(): JsonRpc
	{
		return new JsonRpc({
			sender: this.connectors.webSocket,
			handlers: {
				'incoming.message': this.handleRpcIncomingMessage.bind(this),
			},
			events: {
				error: this.onRpcError.bind(this),
			},
		});
	}

	createCodec(): MessageCodec
	{
		if (this.isProtobufSupported())
		{
			return new ProtobufCodec({
				channelManager: this.channelManager,
			});
		}

		return new LegacyCodec();
	}

	get connector(): WebSocketConnector | LongPollingConnector
	{
		return this.connectors[this.connectionType];
	}

	disconnect(disconnectCode, disconnectReason)
	{
		if (this.connector)
		{
			this.isManualDisconnect = true;
			this.connector.disconnect(disconnectCode, disconnectReason);
		}
	}

	stop(disconnectCode, disconnectReason)
	{
		this.disconnect(disconnectCode, disconnectReason);
		this.stopCheckConfig();
	}

	resetSession()
	{
		this.session.mid = null;
		this.session.tag = null;
		this.session.time = null;
	}

	setConfig(config)
	{
		const wasConnected = this.isConnected();
		if (wasConnected)
		{
			this.disconnect(CloseReasons.CONFIG_REPLACED, 'config was replaced');
		}

		this.config = config;

		if (config.publicChannels)
		{
			this.channelManager.setPublicIds(Object.values(config.publicChannels));
		}

		if (wasConnected)
		{
			this.connect();
		}
	}

	connect(): Promise<void>
	{
		if (this.connector.connected)
		{
			return Promise.resolve();
		}

		if (this.reconnectTimeout)
		{
			clearTimeout(this.reconnectTimeout);
		}

		this.isManualDisconnect = false;
		this.status = PullStatus.Connecting;
		this.connectionAttempt++;

		return new Promise((resolve, reject) => {
			this.connectPromises.push({ resolve, reject });
			this.connector.connect();
		});
	}

	reconnect(disconnectCode, disconnectReason, delay = 1)
	{
		this.disconnect(disconnectCode, disconnectReason);

		this.scheduleReconnect(delay);
	}

	restoreWebSocketConnection()
	{
		if (this.connectionType === ConnectionType.WebSocket)
		{
			return;
		}

		this.connectors.webSocket.connect();
	}

	scheduleReconnect(connectionDelay)
	{
		const delay = connectionDelay ?? this.getConnectionAttemptDelay(this.connectionAttempt);

		if (this.reconnectTimeout)
		{
			clearTimeout(this.reconnectTimeout);
		}

		this.logger?.log(`Pull: scheduling reconnection in ${delay} seconds; attempt # ${this.connectionAttempt}`);

		this.reconnectTimeout = setTimeout(
			() => {
				this.connect().catch((error) => {
					console.error(error);
				});
			},
			delay * 1000,
		);
	}

	scheduleRestoreWebSocketConnection()
	{
		this.logger?.log(`Pull: scheduling restoration of websocket connection in ${RESTORE_WEBSOCKET_TIMEOUT} seconds`);

		if (this.restoreWebSocketTimeout)
		{
			return;
		}

		this.restoreWebSocketTimeout = setTimeout(() => {
			this.restoreWebSocketTimeout = 0;
			this.restoreWebSocketConnection();
		}, RESTORE_WEBSOCKET_TIMEOUT * 1000);
	}

	handleInternalPullEvent(command, message)
	{
		switch (command.toUpperCase())
		{
			case SystemCommands.CHANNEL_EXPIRE:
			{
				if (message.params.action === 'reconnect' && 'new_channel' in message.params)
				{
					this.dispatchEvent(new CustomEvent(ConnectorEvents.ChannelReplaced), {
						detail: {
							type: message.params.channel.type,
							newChannel: message.params.new_channel,
						},
					});
				}
				else
				{
					this.dispatchEvent(new CustomEvent(ConnectorEvents.ConfigExpired));
				}
				break;
			}

			case SystemCommands.CONFIG_EXPIRE:
			{
				this.dispatchEvent(new CustomEvent(ConnectorEvents.ConfigExpired));
				break;
			}

			case SystemCommands.SERVER_RESTART:
			{
				this.reconnect(CloseReasons.SERVER_RESTARTED, 'server was restarted', 15);
				break;
			}
			default://
		}
	}

	getConnectionBasePath(connectionType: string): string
	{
		switch (connectionType)
		{
			case ConnectionType.WebSocket:
				return this.isSecure ? this.config.server.websocket_secure : this.config.server.websocket;
			case ConnectionType.LongPolling:
				return this.isSecure ? this.config.server.long_pooling_secure : this.config.server.long_polling;
			default:
				throw new Error(`Unknown connection type ${connectionType}`);
		}
	}

	getConnectionChannels(): string
	{
		const channels = [];
		for (const channelType of ['private', 'shared'])
		{
			if (channelType in this.config.channels)
			{
				channels.push(this.config.channels[channelType].id);
			}
		}

		if (channels.length === 0)
		{
			throw new Error('Empty channel list');
		}

		return channels.join('/');
	}

	getConnectionPath(): string
	{
		return this.getConnectionPathByType(this.connectionType);
	}

	getConnectionPathByType(connectionType): string
	{
		const params = {};
		const path = this.getConnectionBasePath(connectionType);

		if (isNotEmptyString(this.config.jwt))
		{
			params.token = this.config.jwt;
		}
		else
		{
			params.CHANNEL_ID = this.getConnectionChannels();
		}

		if (this.isJsonRpc())
		{
			params.jsonRpc = 'true';
		}
		else if (this.isProtobufSupported())
		{
			params.binaryMode = 'true';
		}

		if (this.isSharedMode())
		{
			if (!this.config.clientId)
			{
				throw new Error('Push-server is in shared mode, but clientId is not set');
			}
			params.clientId = this.config.clientId;
		}

		if (this.session.mid)
		{
			params.mid = this.session.mid;
		}

		if (this.session.tag)
		{
			params.tag = this.session.tag;
		}

		if (this.session.time)
		{
			params.time = this.session.time;
		}
		params.revision = REVISION;

		return `${path}?${buildQueryString(params)}`;
	}

	getPublicationPath(): string
	{
		const path = this.isSecure ? this.config.server.publish_secure : this.config.server.publish;
		if (!path)
		{
			return '';
		}

		const channels = [];
		for (const type of Object.keys(this.config.channels))
		{
			channels.push(this.config.channels[type].id);
		}

		const params = {
			CHANNEL_ID: channels.join('/'),
		};

		return `${path}?${buildQueryString(params)}`;
	}

	emitMessage(message)
	{
		if (!isPlainObject(message.extra))
		{
			message.extra = {};
		}

		if (message.extra.server_time_unix)
		{
			const timeShift = this.config.server.timeShift ?? 0;
			const timeAgo = ((getTimestamp() - (message.extra.server_time_unix * 1000)) / 1000) - timeShift;
			message.extra.server_time_ago = timeAgo > 0 ? timeAgo : 0;
		}

		this.dispatchEvent(new CustomEvent(ConnectorEvents.Message, { detail: message }));
	}

	/**
	 * Returns reconnect delay in seconds
	 * @param attemptNumber
	 * @return {number}
	 */
	getConnectionAttemptDelay(attemptNumber): number
	{
		let result = 60;
		if (attemptNumber < 1)
		{
			result = 0.5;
		}
		else if (attemptNumber < 3)
		{
			result = 5;
		}
		else if (attemptNumber < 5)
		{
			result = 25;
		}
		else if (attemptNumber < 10)
		{
			result = 45;
		}

		return result + (result * Math.random() * 0.2);
	}

	onLongPollingOpen()
	{
		this.unloading = false;
		this.starting = false;
		this.connectionAttempt = 0;
		this.isManualDisconnect = false;
		this.status = PullStatus.Online;

		this.logger?.log('Pull: Long polling connection with push-server opened');
		if (this.isWebSocketEnabled())
		{
			this.scheduleRestoreWebSocketConnection();
		}

		this.connectPromises.forEach((resolver) => {
			resolver.resolve();
		});
		this.connectPromises = [];
	}

	onWebSocketOpen()
	{
		this.status = PullStatus.Online;
		this.isWebsocketBlocked = false;
		this.connectionAttempt = 0;

		// to prevent fallback to long polling in case of networking problems
		this.isLongPollingBlocked = true;

		if (this.connectionType === ConnectionType.LongPolling)
		{
			this.connectionType = ConnectionType.WebSocket;
			this.connectors.longPolling.disconnect();
		}

		if (this.restoreWebSocketTimeout)
		{
			clearTimeout(this.restoreWebSocketTimeout);
			this.restoreWebSocketTimeout = null;
		}
		this.logger?.log('Pull: Websocket connection with push-server opened');
		this.connectPromises.forEach((resolver) => {
			resolver.resolve();
		});
		this.connectPromises = [];
	}

	onWebSocketDisconnect(e = {})
	{
		if (this.connectionType === ConnectionType.WebSocket)
		{
			this.status = PullStatus.Offline;
		}

		if (this.isManualDisconnect)
		{
			this.logger?.logForce('Pull: Websocket connection with push-server manually closed');
		}
		else
		{
			this.logger?.logForce(`Pull: Websocket connection with push-server closed. Code: ${e.code}, reason: ${e.reason}`);
			if (e.code === CloseReasons.WRONG_CHANNEL_ID)
			{
				this.dispatchEvent(new CustomEvent(ConnectorEvents.ConnectionError, {
					detail: {
						code: e.code,
						reason: 'wrong channel signature',
					},
				}));
			}
			else
			{
				this.scheduleReconnect();
			}
		}

		// to prevent fallback to long polling in case of networking problems
		this.isLongPollingBlocked = true;
		this.isManualDisconnect = false;

		this.clearPingWaitTimeout();
	}

	onWebSocketError(e)
	{
		this.starting = false;
		if (this.connectionType === ConnectionType.WebSocket)
		{
			this.status = PullStatus.Offline;
		}

		console.error(`${getDateForLog()}: Pull: WebSocket connection error`, e);
		this.scheduleReconnect();
		this.connectPromises.forEach((resolver) => {
			resolver.reject();
		});
		this.connectPromises = [];

		this.clearPingWaitTimeout();
	}

	onWebSocketBlockChanged(e)
	{
		const isWebSocketBlocked = e.isWebSocketBlocked;

		if (isWebSocketBlocked && this.connectionType === ConnectionType.WebSocket && !this.isConnected())
		{
			clearTimeout(this.reconnectTimeout);

			this.connectionAttempt = 0;
			this.connectionType = ConnectionType.LongPolling;
			this.scheduleReconnect(1);
		}
		else if (!isWebSocketBlocked && this.connectionType === ConnectionType.LongPolling)
		{
			clearTimeout(this.reconnectTimeout);
			clearTimeout(this.restoreWebSocketTimeout);

			this.connectionAttempt = 0;
			this.connectionType = ConnectionType.WebSocket;
			this.scheduleReconnect(1);
		}
	}

	onLongPollingDisconnect(e = {})
	{
		if (this.connectionType === ConnectionType.LongPolling)
		{
			this.status = PullStatus.Offline;
		}

		this.logger?.log(`Pull: Long polling connection with push-server closed. Code: ${e.code}, reason: ${e.reason}`);
		if (!this.isManualDisconnect)
		{
			this.scheduleReconnect();
		}
		this.isManualDisconnect = false;
		this.clearPingWaitTimeout();
	}

	onLongPollingError(e)
	{
		this.starting = false;
		if (this.connectionType === ConnectionType.LongPolling)
		{
			this.status = PullStatus.Offline;
		}
		console.error(`${getDateForLog()}: Pull: Long polling connection error`, e);
		this.scheduleReconnect();
		this.connectPromises.forEach((resolver) => {
			resolver.reject();
		});
		this.connectPromises = [];
		this.clearPingWaitTimeout();
	}

	onIncomingMessage(message)
	{
		if (this.isJsonRpc())
		{
			if (message === JSON_RPC_PING)
			{
				this.onJsonRpcPing();
			}
			else
			{
				this.jsonRpcAdapter.handleIncomingMessage(message);
			}
		}
		else
		{
			const events = this.codec.extractMessages(message);
			this.handleIncomingEvents(events);
		}
	}

	handleRpcIncomingMessage(messageFields): {}
	{
		this.session.mid = messageFields.mid;
		const body = messageFields.body;

		if (!messageFields.body.extra)
		{
			body.extra = {};
		}
		body.extra.sender = messageFields.sender;

		if ('user_params' in messageFields && isPlainObject(messageFields.user_params))
		{
			Object.assign(body.params, messageFields.user_params);
		}

		if ('dictionary' in messageFields && isPlainObject(messageFields.dictionary))
		{
			Object.assign(body.params, messageFields.dictionary);
		}

		if (this.checkDuplicate(messageFields.mid))
		{
			this.addMessageToStat(body);
			this.trimDuplicates();
			if (body.module_id === 'pull')
			{
				this.handleInternalPullEvent(body.command, body);
			}
			else
			{
				this.emitMessage(body);
			}

			if (body.extra && body.extra.revision_web)
			{
				this.checkRevision(body.extra.revision_web);
			}
		}

		this.connector.send(`mack:${messageFields.mid}`);

		return {};
	}

	onRpcError(event)
	{
		// probably, fire event
	}

	onJsonRpcPing()
	{
		this.updatePingWaitTimeout();
		this.connector.send(JSON_RPC_PONG);
	}

	handleIncomingEvents(events)
	{
		const messages = [];
		if (events.length === 0)
		{
			this.session.mid = null;

			return;
		}

		for (const event of events)
		{
			this.updateSessionFromEvent(event);
			if (event.mid && !this.checkDuplicate(event.mid))
			{
				continue;
			}

			this.addMessageToStat(event.text);
			messages.push(event.text);
		}
		this.trimDuplicates();
		messages.forEach((message) => {
			if (message.module_id === 'pull')
			{
				this.handleInternalPullEvent(message.command, message);
			}
			else
			{
				this.emitMessage(message);
			}

			if (message.extra && message.extra.revision_web)
			{
				this.checkRevision(message.extra.revision_web);
			}
		});
	}

	checkRevision(serverRevision: number)
	{
		if (serverRevision > 0 && serverRevision !== REVISION)
		{
			this.logger?.log(`Pull revision changed from ${REVISION} to ${serverRevision}. Reload required`);

			this.dispatchEvent(new CustomEvent(ConnectorEvents.RevisionChanged, { detail: { revision: serverRevision } }));
		}
	}

	updateSessionFromEvent(event)
	{
		this.session.mid = event.mid || null;
		this.session.tag = event.tag || null;
		this.session.time = event.time || null;
	}

	checkDuplicate(mid): boolean
	{
		if (this.session.lastMessageIds.includes(mid))
		{
			// eslint-disable-next-line no-console
			console.warn(`Duplicate message ${mid} skipped`);

			return false;
		}

		this.session.lastMessageIds.push(mid);

		return true;
	}

	trimDuplicates()
	{
		if (this.session.lastMessageIds.length > MAX_IDS_TO_STORE)
		{
			this.session.lastMessageIds = this.session.lastMessageIds.slice(-MAX_IDS_TO_STORE);
		}
	}

	addMessageToStat(message)
	{
		if (!this.session.history[message.module_id])
		{
			this.session.history[message.module_id] = {};
		}

		if (!this.session.history[message.module_id][message.command])
		{
			this.session.history[message.module_id][message.command] = 0;
		}
		this.session.history[message.module_id][message.command]++;

		this.session.messageCount++;
	}

	getRevision(): number | null
	{
		return (this.config && this.config.api) ? this.config.api.revision_web : null;
	}

	getServerVersion(): number
	{
		return (this.config && this.config.server) ? this.config.server.version : 0;
	}

	getServerMode(): string | null
	{
		return (this.config && this.config.server) ? this.config.server.mode : null;
	}

	isConnected(): boolean
	{
		return this.connector.connected;
	}

	isWebSocketConnected(): boolean
	{
		return this.connector.connected && this.connector.connectionType === ConnectionType.WebSocket;
	}

	isWebSocketAllowed(): boolean
	{
		return !this.isWebsocketBlocked && this.isWebSocketEnabled();
	}

	isWebSocketEnabled(): boolean
	{
		if (!isWebSocketSupported())
		{
			return false;
		}

		return (this.config && this.config.server && this.config.server.websocket_enabled === true);
	}

	isPublishingSupported(): boolean
	{
		return this.getServerVersion() > 3;
	}

	isPublishingEnabled(): boolean
	{
		if (!this.isPublishingSupported())
		{
			return false;
		}

		return (this.config && this.config.server && this.config.server.publish_enabled === true);
	}

	isProtobufSupported(): boolean
	{
		return (this.getServerVersion() === 4 && !browser.IsIe());
	}

	isJsonRpc(): boolean
	{
		return (this.getServerVersion() >= 5);
	}

	isSharedMode(): boolean
	{
		return (this.getServerMode() === ServerMode.Shared);
	}

	setPublicIds(publicIds)
	{
		this.channelManager.setPublicIds(publicIds);
	}

	/**
	 * Sends batch of messages to the multiple public channels.
	 *
	 * @param {object[]} messageBatch Array of messages to send.
	 * @param  {int[]} messageBatch.userList User ids the message receivers.
	 * @param  {string[]|object[]} messageBatch.channelList Public ids of the channels to send messages.
	 * @param {string} messageBatch.moduleId Name of the module to receive message,
	 * @param {string} messageBatch.command Command name.
	 * @param {object} messageBatch.params Command parameters.
	 * @param {integer} [messageBatch.expiry] Message expiry time in seconds.
	 * @return void
	 */
	async sendMessageBatch(messageBatch)
	{
		if (!this.isPublishingEnabled())
		{
			throw new Error('Client publishing is not supported or is disabled');
		}

		try
		{
			const packet = await this.codec.encodeMessageBatch(messageBatch);
			this.connector.send(packet);
		}
		catch (e)
		{
			console.error('sendMessageBatch error:', e);
			throw e;
		}
	}

	/**
	 * Send single message to the specified users.
	 *
	 * @param {integer[]} users User ids of the message receivers.
	 * @param {string} moduleId Name of the module to receive message,
	 * @param {string} command Command name.
	 * @param {object} params Command parameters.
	 * @param {integer} [expiry] Message expiry time in seconds.
	 */
	async sendMessage(users, moduleId, command, params, expiry): Promise<JsonRpcResponse> | void
	{
		const message = {
			userList: users,
			body: {
				module_id: moduleId,
				command,
				params,
			},
			expiry,
		};

		if (this.isJsonRpc())
		{
			return this.jsonRpcAdapter.executeOutgoingRpcCommand(RpcMethod.Publish, message);
		}

		return this.sendMessageBatch([message]);
	}

	/**
	 * Send single message to the specified public channels.
	 *
	 * @param {string[]} publicChannels Public ids of the channels to receive message.
	 * @param {string} moduleId Name of the module to receive message,
	 * @param {string} command Command name.
	 * @param {object} params Command parameters.
	 * @param {integer} [expiry] Message expiry time in seconds.
	 * @return {Promise}
	 */
	sendMessageToChannels(publicChannels, moduleId, command, params, expiry): Promise<JsonRpcResponse> | void
	{
		const message = {
			channelList: publicChannels,
			body: {
				module_id: moduleId,
				command,
				params,
			},
			expiry,
		};

		if (this.isJsonRpc())
		{
			return this.jsonRpcAdapter.executeOutgoingRpcCommand(RpcMethod.Publish, message);
		}

		return this.sendMessageBatch([message]);
	}

	/**
	 * @param userId {number}
	 */
	async subscribeUserStatusChange(userId): Promise<void>
	{
		if (typeof (userId) !== 'number')
		{
			throw new TypeError('userId must be a number');
		}

		await this.jsonRpcAdapter.executeOutgoingRpcCommand(RpcMethod.SubscribeStatusChange, { userId });
	}

	/**
	 * @param userId {number}
	 * @returns {Promise}
	 */
	async unsubscribeUserStatusChange(userId): Promise<void>
	{
		if (typeof (userId) !== 'number')
		{
			throw new TypeError('userId must be a number');
		}

		await this.jsonRpcAdapter.executeOutgoingRpcCommand(RpcMethod.UnsubscribeStatusChange, { userId });
	}

	/**
	 * Returns "last seen" time in seconds for the users. Result format: Object{userId: int}
	 * If the user is currently connected - will return 0.
	 * If the user if offline - will return diff between current timestamp and last seen timestamp in seconds.
	 * If the user was never online - the record for user will be missing from the result object.
	 *
	 * @param {integer[]} userList List of user ids.
	 * @returns {Promise}
	 */
	getUsersLastSeen(userList: number[]): Promise<{ [number]: number }>
	{
		if (!isArray(userList) || !userList.every((item) => typeof (item) === 'number'))
		{
			throw new Error('userList must be an array of numbers');
		}
		const result = {};

		return new Promise((resolve, reject) => {
			this.jsonRpcAdapter.executeOutgoingRpcCommand(RpcMethod.GetUsersLastSeen, {
				userList,
			}).then((response) => {
				const unresolved = [];

				for (const userId of userList)
				{
					if (!(userId in response))
					{
						unresolved.push(userId);
					}
					result[userId] = response[userId];
				}

				if (unresolved.length === 0)
				{
					resolve(result);

					return;
				}

				const params = {
					userIds: unresolved,
					sendToQueueSever: true,
				};

				this.restClient.callMethod('pull.api.user.getLastSeen', params);
			}).then((response) => {
				const data = response.data();
				for (const userId of Object.keys(data))
				{
					result[userId] = data[userId];
				}

				resolve(result);
			}).catch((error) => {
				reject(error);
			});
		});
	}

	/**
	 * Pings server. In case of success promise will be resolved, otherwise - rejected.
	 *
	 * @param {int} timeout Request timeout in seconds
	 * @returns {Promise}
	 */
	ping(timeout): Promise<JsonRpcResponse>
	{
		return this.jsonRpcAdapter.executeOutgoingRpcCommand(RpcMethod.Ping, {}, timeout);
	}

	/**
	 * Returns list channels that the connection is subscribed to.
	 *
	 * @returns {Promise}
	 */
	listChannels(): Promise<JsonRpcResponse>
	{
		return this.jsonRpcAdapter.executeOutgoingRpcCommand(RpcMethod.ListChannels, {});
	}

	updatePingWaitTimeout()
	{
		clearTimeout(this.pingWaitTimeout);
		this.pingWaitTimeout = setTimeout(this.onPingTimeout.bind(this), PING_TIMEOUT * 2 * 1000);
	}

	clearPingWaitTimeout()
	{
		clearTimeout(this.pingWaitTimeout);
		this.pingWaitTimeout = null;
	}

	onPingTimeout()
	{
		this.pingWaitTimeout = null;
		if (!this.isConnected())
		{
			return;
		}

		// eslint-disable-next-line no-console
		console.warn(`No pings are received in ${PING_TIMEOUT * 2} seconds. Reconnecting`);
		this.disconnect(CloseReasons.STUCK, 'connection stuck');
		this.scheduleReconnect();
	}
}
