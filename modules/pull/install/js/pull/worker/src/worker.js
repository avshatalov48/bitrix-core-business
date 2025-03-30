/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */
// noinspection ES6PreferShortImport

import { BufferedLogger } from './bufferedlogger';
import { ConfigHolder, ConfigHolderEvents } from '../../configholder/src/configholder';
import { Connector } from '../../connector/src/connector';
import type { Logger } from '../../connector/src/connector';
import { JsonRpc } from '../../jsonrpc/src/jsonrpc';
import { MiniRest } from '../../minirest/src/minirest';
import { CloseReasons, PullStatus } from '../../client/src/consts';
import type { JsonRpcResponse, Sender } from '../../jsonrpc/src/jsonrpc';

type Consumer = {
	port: MessagePort,
	rpcAdapter: JsonRpc,
	disconnectTimeout: null,
	userSubscriptions: Set<number>, // userId
}

export class Worker
{
	consumers: Consumer[] = [];
	configHolder: ConfigHolder;
	connector: Connector;
	userSubscriptions: Map<number, Set<MessagePort>> = new Map(); // userId -> set of subscribed ports
	status: $Values<typeof PullStatus> = PullStatus.Online;
	logger: BufferedLogger;

	constructor()
	{
		this.restClient = new MiniRest();
		this.logger = new BufferedLogger(100);

		this.bindEvents();
		this.init();
	}

	bindEvents()
	{
		/* eslint-disable no-undef */
		// globalThis seem to exist in all browsers supporting SharedWorker
		globalThis.addEventListener('connect', this.onConnect.bind(this));
		globalThis.addEventListener('offline', this.onOffline.bind(this));
		globalThis.addEventListener('online', this.onOnline.bind(this));
	}

	async init()
	{
		this.configHolder = new ConfigHolder({
			restClient: this.restClient,
			events: {
				[ConfigHolderEvents.ConfigExpired]: () => {
					this.logger.log('Stale config detected. Restarting');
					this.restart(CloseReasons.CONFIG_EXPIRED, 'config expired');
				},
				[ConfigHolderEvents.RevisionChanged]: this.onRevisionChanged.bind(this),
			},
		});
		try
		{
			const config = await this.configHolder.loadConfig('client_start');
			this.connector = this.createConnector(config, this.restClient);
			await this.connector.connect();
		}
		catch (e)
		{
			this.logger.error('load config', e);
			this.scheduleRestart(CloseReasons.BACKEND_ERROR, 'backend error');
		}
	}

	createConnector(config, restClient): Connector
	{
		return new Connector({
			config,
			restClient,
			restoreSession: true,
			getPublicListMethod: 'pull.channel.public.list',
			logger: this.getLogger(),
			events: {
				message: this.onConnectorMessage.bind(this),
				connectionStatus: this.onConnectionStatus.bind(this),
				revisionChanged: this.onRevisionChanged.bind(this),
				channelExpire: this.onChannelExpired.bind(this),
				connectionError: this.onConnectionError.bind(this),
			},
		});
	}

	createRpcAdapter(port: MessagePort): JsonRpc
	{
		const rpcAdapter = new JsonRpc({
			sender: this.createSender(port),
			handlers: {
				notifyConfigTimestamp: this.handleNotifyConfigTimestamp.bind(this),
				notifyLogin: this.handleNotifyLogin.bind(this),
				notifyOnline: this.handleNotifyOnline.bind(this),
				notifyOffline: this.handleNotifyOffline.bind(this),
				setPublicIds: this.handleSetPublicIds.bind(this),
				sendMessage: this.handleSendMessage.bind(this),
				sendMessageBatch: this.handleSendMessageBatch.bind(this),
				sendMessageToChannels: this.handleSendMessageToChannels.bind(this),
				getUsersLastSeen: this.handleGetUsersLastSeen.bind(this),
				listChannels: this.handleListChannels.bind(this),
				subscribeUserStatusChange: this.createSubscribeUserStatusChangeHandler(port),
				unsubscribeUserStatusChange: this.createUnsubscribeUserStatusChangeHandler(port),
				bye: this.createByeHandler(port),
				// used for manual debug
				ping: this.handlePing.bind(this),
				getConfig: this.handleGetConfig.bind(this),
				getLog: this.handleGetLog.bind(this),
			},
			events: {},
		});

		port.addEventListener('message', (me: MessageEvent) => rpcAdapter.handleIncomingMessage(me.data));

		return rpcAdapter;
	}

	createSender(port: MessagePort): Sender
	{
		return {
			send: (m: string): boolean => {
				port.postMessage(m);

				return true;
			},
		};
	}

	getLogger(): Logger
	{
		return {
			log: (message, ...params) => {
				this.logger.log(message, ...params);
			},
			logForce: (message, ...params) => {
				this.logger.log(message, ...params);
			},
		};
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

	removeConsumer(port: MessagePort)
	{
		const consumerIndex = this.consumers.findIndex((consumer: Consumer) => consumer.port === port);
		if (consumerIndex !== -1)
		{
			const consumer = this.consumers[consumerIndex];
			for (const [userId] of consumer.userSubscriptions.entries())
			{
				this.unsubscribeUserStatus(userId, port);
			}
			this.consumers.splice(consumerIndex, 1);
		}
	}

	async restart(disconnectCode = CloseReasons.NORMAL_CLOSURE, disconnectReason = 'manual restart')
	{
		const loadConfigReason = `${disconnectCode}_${disconnectReason.replaceAll(' ', '_')}`;
		this.connector?.disconnect(disconnectCode, disconnectReason);
		try
		{
			const config = await this.configHolder.loadConfig(loadConfigReason);
			if (this.connector)
			{
				this.connector.setConfig(config);
			}
			else
			{
				this.connector = this.createConnector(config, this.restClient);
			}
			await this.connector.connect();
		}
		catch (e)
		{
			this.logger.error('load config', e);
			this.scheduleRestart(CloseReasons.BACKEND_ERROR, 'backend error');
		}
	}

	async handleSendMessage({ users, moduleId, command, params, expiry }): void
	{
		return this.connector.sendMessage(users, moduleId, command, params, expiry);
	}

	async handleSendMessageBatch({ messageBatch }): void
	{
		return this.connector.sendMessageBatch(messageBatch);
	}

	async handleSendMessageToChannels({ publicChannels, moduleId, command, params, expiry }): void
	{
		return this.connector.sendMessageToChannels(publicChannels, moduleId, command, params, expiry);
	}

	handleNotifyConfigTimestamp({ configTimestamp })
	{
		const config = this.configHolder?.config || {};
		if (config && config.server && config.server.config_timestamp !== configTimestamp)
		{
			this.restart(CloseReasons.CONFIG_EXPIRED, 'config expired');
		}
	}

	handleNotifyLogin()
	{
		this.restart(CloseReasons.NORMAL_CLOSURE, 'desktop login');
	}

	handleNotifyOnline()
	{
		if (this.connector && !this.connector.isConnected())
		{
			this.connector.connect();
		}
	}

	handleNotifyOffline()
	{
		this.connector?.disconnect('1000', 'offline');
	}

	handleSetPublicIds({ publicIds })
	{
		this.connector.setPublicIds(publicIds);
	}

	async handleGetUsersLastSeen({ userList }): Promise<{ [number]: number }>
	{
		return this.connector.getUsersLastSeen(userList);
	}

	async handleListChannels(): Promise<JsonRpcResponse>
	{
		return this.connector.listChannels();
	}

	handlePing()
	{
		return 'pong';
	}

	handleGetLog()
	{
		return this.logger.getAll();
	}

	handleGetConfig()
	{
		return { config: this.configHolder.config };
	}

	createSubscribeUserStatusChangeHandler(port: MessagePort): ({ userId: number }) => void
	{
		return ({ userId }) => {
			if (this.userSubscriptions.has(userId))
			{
				this.userSubscriptions.get(userId).add(port);
			}
			else
			{
				const newSet = new Set();
				newSet.add(port);
				this.userSubscriptions.set(userId, newSet);
			}

			this.connector.subscribeUserStatusChange(userId);
		};
	}

	createUnsubscribeUserStatusChangeHandler(port: MessagePort): ({ userId: number }) => void
	{
		return ({ userId }) => {
			this.unsubscribeUserStatus(userId, port);
		};
	}

	createByeHandler(port: MessagePort): () => void
	{
		return () => {
			this.removeConsumer(port);
		};
	}

	unsubscribeUserStatus(userId: number, port: MessagePort)
	{
		if (!this.userSubscriptions.has(userId))
		{
			return;
		}
		const ports = this.userSubscriptions.get(userId);
		ports.delete(port);
		if (ports.size === 0)
		{
			this.userSubscriptions.delete(userId);
			this.connector.unsubscribeUserStatusChange(userId);
		}

		const consumerIndex = this.consumers.findIndex((consumer: Consumer) => consumer.port === port);
		if (consumerIndex !== -1)
		{
			this.consumers[consumerIndex].userSubscriptions.delete(userId);
		}
	}

	sendConnectionStatus(rpcAdapter: JsonRpc)
	{
		rpcAdapter.executeOutgoingRpcCommand(
			'connectionStatusChanged',
			{
				status: this.status,
				connectionType: this.connector.connectionType,
				isJsonRpc: this.connector.isJsonRpc(),
			},
			0,
		);
	}

	onConnectorMessage(e: CustomEvent)
	{
		this.consumers.forEach((consumer) => {
			consumer.rpcAdapter.executeOutgoingRpcCommand('incomingMessage', { payload: e.detail }, 0);
		});
	}

	onConnectionStatus(e: CustomEvent)
	{
		this.status = e.detail.status;
		this.consumers.forEach((consumer) => {
			this.sendConnectionStatus(consumer.rpcAdapter);
		});
	}

	onChannelExpired()
	{
		this.restart();
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

	onRevisionChanged = (e: CustomEvent) => {
		this.consumers.forEach((consumer) => {
			consumer.rpcAdapter.executeOutgoingRpcCommand('revisionChanged', { revision: e.detail.revision }, 0);
		});
	};

	onConnect(e: MessageEvent)
	{
		const port = e.ports[0];
		port.start();
		const rpcAdapter = this.createRpcAdapter(port);
		const userSubscriptions = new Set();

		this.consumers.push({ port, rpcAdapter, userSubscriptions });
		rpcAdapter.executeOutgoingRpcCommand('ready');
		if (this.connector)
		{
			this.sendConnectionStatus(rpcAdapter);
		}
	}

	onOffline()
	{
		this.logger.log('offline');
		this.connector?.disconnect('1000', 'offline');
	}

	onOnline()
	{
		this.logger.log('online');
		this.connector?.connect();
	}
}
