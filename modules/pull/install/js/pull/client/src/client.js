/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
/* eslint-disable @bitrix24/bitrix24-rules/no-bx */
/* eslint-disable @bitrix24/bitrix24-rules/no-bx-message */
/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */
/* eslint-disable sonarjs/cognitive-complexity */
// noinspection ES6PreferShortImport

import {
	CloseReasons,
	ConnectionType,
	PullStatus,
	SubscriptionType,
	REVISION, ServerMode,
} from './consts';
import { getDateForLog, isArray, clone, isWebSocketSupported } from '../../util/src/util';
import { Emitter } from './emitter';
import { Connector, ConnectorEvents } from 'pull.connector';
import type { Logger } from 'pull.connector';
import { ConfigHolder, ConfigHolderEvents } from 'pull.configholder';
import { WorkerConnector, WorkerConnectorEvents } from './workerconnector';
import { MiniRest } from '../../minirest/src/minirest';
import type { PullConfig } from 'pull.configholder';
import { TagWatcher } from './tagwatcher';
import { StorageManager } from './storage';
import type { RestCaller } from '../../minirest/src/restcaller';

const OFFLINE_STATUS_DELAY = 5000;

const LS_SESSION = 'bx-pull-session';
const LS_SESSION_CACHE_TIME = 20;

export class PullClient
{
	static PullStatus = PullStatus;
	static SubscriptionType = SubscriptionType;
	static CloseReasons = CloseReasons;
	static StorageManager = StorageManager;

	#status = '';
	#emitter: Emitter;

	#connector: Connector | WorkerConnector | null;
	restClient: ?RestCaller;

	/* eslint-disable no-param-reassign */
	constructor(params = {})
	{
		this.#emitter = new Emitter({
			logger: this.getLogger(),
		});

		this.#connector = null;

		if (params.restApplication)
		{
			if (typeof params.configGetMethod === 'undefined')
			{
				params.configGetMethod = 'pull.application.config.get';
			}

			if (typeof params.skipCheckRevision === 'undefined')
			{
				params.skipCheckRevision = true;
			}

			if (typeof params.restApplication === 'string')
			{
				params.siteId = params.restApplication;
			}

			params.serverEnabled = true;
		}

		this.context = 'master';

		this.guestMode = params.guestMode ?? (getGlobalParam('pull_guest_mode', 'N') === 'Y');
		this.guestUserId = params.guestUserId ?? getGlobalParamInt('pull_guest_user_id', 0);
		if (this.guestMode && this.guestUserId)
		{
			this.userId = this.guestUserId;
		}
		else
		{
			this.userId = params.userId ?? getGlobalParamInt('USER_ID', 0);
		}
		this.siteId = params.siteId ?? getGlobalParam('SITE_ID', 'none');

		this.restClient = params.restClient ?? this.createRestClint();
		this.customRestClient = Boolean(params.restClient);

		this.enabled = typeof params.serverEnabled === 'undefined' ? (typeof BX.message !== 'undefined' && BX.message.pull_server_enabled === 'Y') : (params.serverEnabled === 'Y' || params.serverEnabled === true);
		this.unloading = false;
		this.starting = false;
		this.connectionAttempt = 0;
		this.connectionType = ConnectionType.WebSocket;
		this.restartTimeout = null;
		this.restoreWebSocketTimeout = null;

		this.configGetMethod = typeof params.configGetMethod === 'string' ? params.configGetMethod : 'pull.config.get';
		this.getPublicListMethod = typeof params.getPublicListMethod === 'string' ? params.getPublicListMethod : 'pull.channel.public.list';

		this.skipStorageInit = params.skipStorageInit === true;
		this.skipCheckRevision = params.skipCheckRevision === true;

		this.tagWatcher = new TagWatcher({
			restClient: this.restClient,
		});

		this.configTimestamp = params.configTimestamp ?? getGlobalParamInt('pull_config_timestamp', 0);

		this.config = null;

		this.storage = null;
		if (this.userId && !this.skipStorageInit)
		{
			this.storage = new StorageManager({
				userId: this.userId,
				siteId: this.siteId,
			});
		}
		this.notificationPopup = null;

		// timers
		this.checkInterval = null;
		this.offlineTimeout = null;

		// manual stop workaround
		this.isManualDisconnect = false;

		this.loggingEnabled = false;

		this.status = PullStatus.Offline;
	}

	get connector(): Connector
	{
		return this.#connector;
	}

	get session()
	{
		return this.#connector.session;
	}

	get status(): string
	{
		return this.#status;
	}

	set status(status)
	{
		if (this.#status === status)
		{
			return;
		}

		this.#status = status;

		if (!this.enabled)
		{
			return;
		}

		if (this.offlineTimeout)
		{
			clearTimeout(this.offlineTimeout);
			this.offlineTimeout = null;
		}

		if (status === PullStatus.Offline)
		{
			this.sendPullStatusDelayed(status, OFFLINE_STATUS_DELAY);
		}
		else
		{
			this.sendPullStatus(status);
		}
	}

	subscribe(params): Function
	{
		return this.#emitter.subscribe(params);
	}

	attachCommandHandler(handler): Function
	{
		return this.#emitter.attachCommandHandler(handler);
	}

	async start(startConfig: ?PullConfig): Promise<boolean>
	{
		if (!this.enabled)
		{
			throw new Error('Push & Pull server is disabled');
		}

		if (this.isConnected())
		{
			return true;
		}

		const sharedWorkerAllowed = getGlobalParamBool('shared_worker_allowed')
			&& WorkerConnector.isSharedWorkerSupported()
		;

		/* if config exists - initialize PullConnector with this config, otherwise start SharedWorker */
		if (startConfig)
		{
			let restoreSession = true;
			if (typeof startConfig.skipReconnectToLastSession !== 'undefined')
			{
				restoreSession = !startConfig.skipReconnectToLastSession;
				delete startConfig.skipReconnectToLastSession;
			}

			this.#connector = this.createConnector(startConfig, restoreSession);
		}
		else if (!this.guestMode && !this.customRestClient && sharedWorkerAllowed)
		{
			this.#connector = this.createWorkerConnector();
		}
		else
		{
			window.addEventListener('beforeunload', this.onBeforeUnload.bind(this));
			window.addEventListener('offline', this.onOffline.bind(this));
			window.addEventListener('online', this.onOnline.bind(this));

			this.configHolder = this.createConfigHolder(this.restClient);

			let config = null;
			try
			{
				config = await this.configHolder.loadConfig('client_start');
				this.#connector = this.createConnector(config, true);
			}
			catch (e)
			{
				console.error(`${getDateForLog()} Pull: load config`, e);
				this.#connector = this.createConnector(null, true);
				this.scheduleRestart(CloseReasons.BACKEND_ERROR, 'backend error');

				return false;
			}
		}

		await this.#connector.connect();

		this.init();
		this.tagWatcher.scheduleUpdate();

		return true;
	}

	createConnector(config: PullConfig, restoreSession: boolean): Connector
	{
		return new Connector({
			config,
			restoreSession,
			restClient: this.restClient,
			getPublicListMethod: this.getPublicListMethod,
			logger: this.getLogger(),
			events: {
				[ConnectorEvents.Message]: this.onMessage.bind(this),
				[ConnectorEvents.ChannelReplaced]: this.onChannelReplaced.bind(this),
				[ConnectorEvents.ConfigExpired]: this.onConfigExpired.bind(this),
				[ConnectorEvents.ConnectionStatus]: this.onConnectionStatus.bind(this),
				[ConnectorEvents.ConnectionError]: this.onConnectionError.bind(this),
				[ConnectorEvents.RevisionChanged]: this.onRevisionChanged.bind(this),
			},
		});
	}

	createWorkerConnector(): WorkerConnector
	{
		return new WorkerConnector({
			bundleTimestamp: getGlobalParamInt('pull_worker_mtime', 0),
			configTimestamp: this.configTimestamp,
			events: {
				[WorkerConnectorEvents.Message]: this.onMessage.bind(this),
				[WorkerConnectorEvents.RevisionChanged]: this.onRevisionChanged.bind(this),
				[WorkerConnectorEvents.ConnectionStatus]: this.onConnectionStatus.bind(this),
			},
		});
	}

	init()
	{
		if (BX && BX.desktop)
		{
			BX.addCustomEvent('BXLinkOpened', this.connect.bind(this));
			BX.addCustomEvent('onDesktopReload', () => this.#connector?.resetSession());
			BX.desktop.addCustomEvent('BXLoginSuccess', this.onLoginSuccess.bind(this));
		}
	}

	onLoginSuccess()
	{
		if (this.#connector instanceof WorkerConnector)
		{
			this.#connector.onLoginSuccess();
		}
		else
		{
			this.restart(1000, 'desktop login');
		}
	}

	createConfigHolder(restClient: RestCaller): ConfigHolder
	{
		return new ConfigHolder({
			restClient,
			configGetMethod: this.configGetMethod,
			events: {
				[ConfigHolderEvents.ConfigExpired]: (e: CustomEvent) => {
					this.logToConsole('Stale config detected. Restarting');
					this.restart(CloseReasons.CONFIG_EXPIRED, 'config expired');
				},
				[ConfigHolderEvents.RevisionChanged]: this.onRevisionChanged.bind(this),
			},
		});
	}

	createRestClint(): RestCaller
	{
		const options = {};

		if (this.guestMode && this.guestUserId !== 0)
		{
			options.queryParams = {
				pull_guest_id: this.guestUserId,
			};
		}

		return new MiniRest(options);
	}

	setLastMessageId(lastMessageId)
	{
		this.session.mid = lastMessageId;
	}

	setPublicIds(publicIds)
	{
		this.#connector.setPublicIds(publicIds);
	}

	/**
	 * Send single message to the specified users.
	 *
	 * @param {integer[]} users User ids of the message receivers.
	 * @param {string} moduleId Name of the module to receive message,
	 * @param {string} command Command name.
	 * @param {object} params Command parameters.
	 * @param {integer} [expiry] Message expiry time in seconds.
	 * @return {Promise}
	 */
	sendMessage(users, moduleId, command, params, expiry): Promise<void>
	{
		return this.#connector.sendMessage(users, moduleId, command, params, expiry);
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
	sendMessageToChannels(publicChannels, moduleId, command, params, expiry): Promise<void>
	{
		return this.#connector.sendMessageToChannels(publicChannels, moduleId, command, params, expiry);
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
		try
		{
			await this.#connector.sendMessageBatch(messageBatch);
		}
		catch (e)
		{
			console.error(e);
		}
	}

	/**
	 * @param userId {number}
	 * @param callback {UserStatusCallback}
	 * @returns {Promise}
	 */
	async subscribeUserStatusChange(userId, callback)
	{
		if (typeof (userId) !== 'number')
		{
			throw new TypeError('userId must be a number');
		}

		await this.#connector.subscribeUserStatusChange(userId);
		this.#emitter.addUserStatusCallback(userId, callback);
	}

	/**
	 * @param userId {number}
	 * @param callback {UserStatusCallback}
	 * @returns {Promise}
	 */
	async unsubscribeUserStatusChange(userId, callback): Promise<void>
	{
		if (typeof (userId) !== 'number')
		{
			throw new TypeError('userId must be a number');
		}

		this.#emitter.removeUserStatusCallback(userId, callback);
		if (!this.#emitter.hasUserStatusCallbacks(userId))
		{
			await this.#connector.unsubscribeUserStatusChange(userId);
		}
	}

	restoreUserStatusSubscription()
	{
		for (const userId of this.#emitter.getSubscribedUsersList())
		{
			this.#connector.subscribeUserStatusChange(userId);
		}
	}

	emitAuthError()
	{
		if (BX && BX.onCustomEvent)
		{
			BX.onCustomEvent(window, 'onPullError', ['AUTHORIZE_ERROR']);
		}
	}

	isJsonRpc(): boolean
	{
		return this.connector ? this.connector.isJsonRpc() : false;
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

		return this.#connector.getUsersLastSeen(userList);
	}

	/**
	 * Pings server. In case of success promise will be resolved, otherwise - rejected.
	 *
	 * @param {int} timeout Request timeout in seconds
	 * @returns {Promise}
	 */
	ping(timeout): Promise<JsonRpcResponse>
	{
		return this.#connector.ping(timeout);
	}

	/**
	 * Returns list channels that the connection is subscribed to.
	 *
	 * @returns {Promise}
	 */
	listChannels(): Promise<JsonRpcResponse>
	{
		return this.#connector.listChannels();
	}

	scheduleRestart(disconnectCode, disconnectReason, restartDelay)
	{
		clearTimeout(this.restartTimeout);
		let delay = restartDelay;
		if (!delay || delay < 1)
		{
			delay = Math.ceil(Math.random() * 30) + 5;
		}

		this.restartTimeout = setTimeout(
			() => this.restart(disconnectCode, disconnectReason),
			delay * 1000,
		);
	}

	async restart(disconnectCode = CloseReasons.NORMAL_CLOSURE, disconnectReason = 'manual restart')
	{
		if (this.configHolder && this.#connector instanceof Connector)
		{
			this.logToConsole(`Pull: restarting with code ${disconnectCode}`);
			this.disconnect(disconnectCode, disconnectReason);

			const loadConfigReason = `${disconnectCode}_${disconnectReason.replaceAll(' ', '_')}`;
			try
			{
				const config = await this.configHolder.loadConfig(loadConfigReason);
				this.#connector.setConfig(config);
			}
			catch (error)
			{
				if ('status' in error && (error.status === 401 || error.status === 403))
				{
					this.emitAuthError();
				}
				this.scheduleRestart(CloseReasons.BACKEND_ERROR, 'backend error');

				return;
			}

			try
			{
				await this.#connector.connect();
			}
			catch
			{
				this.#connector.scheduleReconnect();
			}

			this.tagWatcher.scheduleUpdate();
		}
		else
		{
			this.logToConsole('Pull: restart request ignored in shared worker mode');
		}
	}

	disconnect(disconnectCode, disconnectReason)
	{
		this.#connector?.disconnect(disconnectCode, disconnectReason);
	}

	stop(disconnectCode, disconnectReason)
	{
		this.disconnect(disconnectCode, disconnectReason);
	}

	/**
	 * @returns {Promise}
	 */
	connect(): Promise<void>
	{
		if (!this.enabled)
		{
			return Promise.reject();
		}

		return this.#connector.connect();
	}

	logToConsole(message, ...params)
	{
		if (this.loggingEnabled)
		{
			// eslint-disable-next-line no-console
			console.log(`${getDateForLog()}: ${message}`, ...params);
		}
	}

	getLogger(): Logger
	{
		return {
			log: this.logToConsole.bind(this),
			logForce: (message, ...params) => {
				console.log(`${getDateForLog()}: ${message}`, ...params);
			},
		};
	}

	isConnected(): boolean
	{
		return this.#connector ? this.#connector.isConnected() : false;
	}

	// can't be disabled anymore, now when we dropped support for nginx servers
	isPublishingSupported(): boolean
	{
		return true;
	}

	// can't be disabled anymore
	isPublishingEnabled(): boolean
	{
		return true;
	}

	onMessage(e: CustomEvent)
	{
		this.#emitter.broadcastMessage(e.detail);
	}

	onChannelReplaced(e: CustomEvent)
	{
		this.logToConsole(`Pull: new config for ${e.detail.type} channel set\n`);
	}

	onConfigExpired()
	{
		this.restart(CloseReasons.CONFIG_EXPIRED, 'config expired');
	}

	onConnectionStatus(e: CustomEvent)
	{
		this.status = e.detail.status;
		if (this.status === PullStatus.Online && e.detail.connectionType === ConnectionType.WebSocket)
		{
			this.restoreUserStatusSubscription();
		}
	}

	onConnectionError(e: CustomEvent)
	{
		if (e.detail.code === CloseReasons.WRONG_CHANNEL_ID)
		{
			this.scheduleRestart(CloseReasons.WRONG_CHANNEL_ID, 'wrong channel signature');
		}
		else
		{
			this.restart(e.detail.code, e.detail.reason);
		}
	}

	onRevisionChanged(e: CustomEvent)
	{
		this.checkRevision(e.detail.revision);
	}

	onBeforeUnload()
	{
		this.unloading = true;

		const session = clone(this.session);
		session.ttl = Date.now() + LS_SESSION_CACHE_TIME * 1000;
		if (this.storage)
		{
			try
			{
				this.storage.set(LS_SESSION, JSON.stringify(session), LS_SESSION_CACHE_TIME);
			}
			catch (e)
			{
				console.error(`${getDateForLog()} Pull: Could not save session info in local storage. Error:`, e);
			}
		}

		this.#connector.scheduleReconnect(15);
	}

	onOffline()
	{
		this.disconnect('1000', 'offline');
	}

	onOnline()
	{
		this.connect();
	}

	checkRevision(serverRevision: number): boolean
	{
		if (this.skipCheckRevision)
		{
			return true;
		}

		if (serverRevision > 0 && serverRevision !== REVISION)
		{
			this.enabled = false;
			if (typeof BX.message !== 'undefined')
			{
				this.showNotification(BX.message('PULL_OLD_REVISION'));
			}
			this.disconnect(CloseReasons.NORMAL_CLOSURE, 'check_revision');

			if (typeof BX.onCustomEvent !== 'undefined')
			{
				BX.onCustomEvent(window, 'onPullRevisionUp', [serverRevision, REVISION]);
			}

			this.#emitter.emit({
				type: SubscriptionType.Revision,
				data: {
					server: serverRevision,
					client: REVISION,
				},
			});

			this.logToConsole(`Pull revision changed from ${REVISION} to ${serverRevision}. Reload required`);

			return false;
		}

		return true;
	}

	showNotification(text)
	{
		if (this.notificationPopup || typeof BX.PopupWindow === 'undefined')
		{
			return;
		}

		this.notificationPopup = new BX.PopupWindow('bx-notifier-popup-confirm', null, {
			zIndex: 200,
			autoHide: false,
			closeByEsc: false,
			overlay: true,
			content: BX.create('div', {
				props: { className: 'bx-messenger-confirm' },
				html: text,
			}),
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message('JS_CORE_WINDOW_CLOSE'),
					className: 'popup-window-button-decline',
					events: {
						click: () => this.notificationPopup.close(),
					},
				}),
			],
			events: {
				onPopupClose: () => this.notificationPopup.destroy(),
				onPopupDestroy: () => {
					this.notificationPopup = null;
				},
			},
		});
		this.notificationPopup.show();
	}

	getServerMode(): string
	{
		switch (this.#connector.getServerMode())
		{
			case ServerMode.Shared:
				return 'cloud';
			case ServerMode.Personal:
				return 'local';
			default:
				return 'n/a';
		}
	}

	getDebugInfo(): any
	{
		if (!JSON || !JSON.stringify)
		{
			return false;
		}

		let configDump = { 'Config error': 'config is not loaded' };
		if (this.config && this.config.channels)
		{
			configDump = {
				ChannelID: (this.config.channels.private ? this.config.channels.private.id : 'n/a'),
				ChannelDie: (this.config.channels.private ? this.config.channels.private.end : 'n/a'),
				ChannelDieShared: ('shared' in this.config.channels ? this.config.channels.shared.end : 'n/a'),
			};
		}

		let websocketMode = '-';
		if (this.#connector instanceof Connector && this.#connector.isWebSocketConnected())
		{
			if (this.#connector.isJsonRpc())
			{
				websocketMode = 'json-rpc';
			}
			else
			{
				websocketMode = (this.#connector.isProtobufSupported() ? 'protobuf' : 'text');
			}
		}

		return {
			UserId: this.userId + (this.userId > 0 ? '' : '(guest)'),
			'Guest userId': (this.guestMode && this.guestUserId !== 0 ? this.guestUserId : '-'),
			'Browser online': (navigator.onLine ? 'Y' : 'N'),
			Connect: (this.isConnected() ? 'Y' : 'N'),
			'Server type': this.getServerMode(),
			'WebSocket supported': (isWebSocketSupported() ? 'Y' : 'N'),
			'WebSocket connected': (this.#connector?.isWebSocketConnected() ? 'Y' : 'N'),
			'WebSocket mode': websocketMode,

			'Try connect': (this.#connector?.reconnectTimeout ? 'Y' : 'N'),
			'Try number': (this.connectionAttempt),

			Path: (this.#connector ? this.#connector.getConnectionPath() : '-'),
			...configDump,

			'Last message': (this.session?.mid > 0 ? this.session?.mid : '-'),
			'Session history': this.session?.history ?? null,
			'Watch tags': this.tagWatcher.queue,
		};
	}

	enableLogging(loggingFlag: boolean = true)
	{
		this.loggingEnabled = loggingFlag;
	}

	capturePullEvent(debugFlag: boolean = true)
	{
		this.#emitter.capturePullEvent(debugFlag);
	}

	sendPullStatusDelayed(status, delay)
	{
		if (this.offlineTimeout)
		{
			clearTimeout(this.offlineTimeout);
		}
		this.offlineTimeout = setTimeout(
			() => {
				this.offlineTimeout = null;
				this.sendPullStatus(status);
			},
			delay,
		);
	}

	sendPullStatus(status)
	{
		if (this.unloading)
		{
			return;
		}

		if (typeof BX.onCustomEvent !== 'undefined')
		{
			BX.onCustomEvent(window, 'onPullStatus', [status]);
		}

		this.#emitter.emit({
			type: SubscriptionType.Status,
			data: {
				status,
			},
		});
	}

	extendWatch(tag, force)
	{
		this.tagWatcher.extend(tag, force);
	}

	clearWatch(tagId)
	{
		this.tagWatcher.clear(tagId);
	}

	// old functions, not used anymore.
	setPrivateVar() {}

	returnPrivateVar() {}

	expireConfig() {}

	updateChannelID() {}

	tryConnect() {}

	tryConnectDelay() {}

	tryConnectSet() {}

	updateState() {}

	setUpdateStateStepCount() {}

	supportWebSocket(): boolean
	{
		return this.isWebSocketSupported();
	}

	isWebSoketConnected(): boolean
	{
		return this.isConnected() && this.connectionType === ConnectionType.WebSocket;
	}

	getPullServerStatus(): boolean
	{
		return this.isConnected();
	}

	closeConfirm()
	{
		if (this.notificationPopup)
		{
			this.notificationPopup.destroy();
		}
	}
}

function getGlobalParam(name: string, defaultValue: string): string
{
	if (typeof BX.message !== 'undefined' && name in BX.message)
	{
		return BX.message[name];
	}

	return defaultValue;
}

function getGlobalParamInt(name: string, defaultValue: number): number
{
	if (typeof BX.message !== 'undefined' && name in BX.message)
	{
		return parseInt(BX.message[name], 10);
	}

	return defaultValue;
}

function getGlobalParamBool(name: string, defaultValue: boolean): boolean
{
	if (typeof BX.message !== 'undefined' && name in BX.message)
	{
		return BX.message[name] === 'Y';
	}

	return defaultValue;
}
