/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */
// noinspection ES6PreferShortImport

import { JsonRpc } from '../../jsonrpc/src/jsonrpc';
import { ConnectionType, PullStatus } from './consts';
import type { JsonRpcResponse } from '../../jsonrpc/src/jsonrpc';

export const WorkerConnectorEvents = {
	Message: 'message',
	RevisionChanged: 'revisionChanged',
	ConnectionStatus: 'connectionStatus',
};

type WorkerConnectorOptions = {
	bundleTimestamp: number,
	configTimestamp: number,
	events: { [string]: Function }
}

const WORKER_PATH = '/bitrix/js/pull/worker/dist/pull.worker.bundle.js';
const WORKER_NAME = 'Bitrix24 Push&Pull';

export class WorkerConnector extends EventTarget
{
	connectionType = ConnectionType.WebSocket;
	connectionStatus = PullStatus.Offline;
	isJsonRpcConnection = false;

	static isSharedWorkerSupported(): boolean
	{
		return 'SharedWorker' in window;
	}

	constructor(options: WorkerConnectorOptions)
	{
		super();

		this.bundleTimestamp = options.bundleTimestamp;
		this.configTimestamp = options.configTimestamp;

		for (const eventName of Object.keys(options.events || {}))
		{
			this.addEventListener(eventName, options.events[eventName]);
		}

		this.worker = new SharedWorker(`${WORKER_PATH}?${this.bundleTimestamp}`, WORKER_NAME);

		this.rpcAdapter = this.createRpcAdapter();

		this.worker.port.start();
		this.worker.port.addEventListener('message', this.onPortMessage.bind(this));

		window.addEventListener('offline', this.onOffline.bind(this));
		window.addEventListener('online', this.onOnline.bind(this));
		window.addEventListener('pagehide', this.onPageHide.bind(this));
	}

	createRpcAdapter(): JsonRpc
	{
		return new JsonRpc({
			sender: {
				send: (m: string) => this.worker.port.postMessage(m),
			},
			handlers: {
				ready: this.handleReady.bind(this),
				incomingMessage: this.handleIncomingMessage.bind(this),
				revisionChanged: this.handleRevisionChanged.bind(this),
				connectionStatusChanged: this.handleConnectionStatusChanged.bind(this),
			},
			events: {
				error: (error) => console.error('rpc error', error),
			},
		});
	}

	setPublicIds(publicIds): Promise<void>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand(
			'setPublicIds',
			{ publicIds },
		);
	}

	sendMessage(users, moduleId, command, params, expiry): Promise<void>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand(
			'sendMessage',
			{ users, moduleId, command, params, expiry },
		);
	}

	sendMessageBatch(messageBatch): Promise<void>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('sendMessageBatch', { messageBatch });
	}

	sendMessageToChannels(publicChannels, moduleId, command, params, expiry): Promise<void>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand(
			'sendMessageToChannels',
			{ publicChannels, moduleId, command, params, expiry },
		);
	}

	connect(): Promise<void>
	{
		return Promise.resolve();
	}

	getUsersLastSeen(userList: number[]): Promise<{ [number]: number }>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('getUsersLastSeen', { userList });
	}

	listChannels(): Promise<JsonRpcResponse>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('listChannels');
	}

	isJsonRpc(): boolean
	{
		return this.isJsonRpcConnection;
	}

	subscribeUserStatusChange(userId): Promise<void>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('subscribeUserStatusChange', { userId });
	}

	unsubscribeUserStatusChange(userId): Promise<void>
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('unsubscribeUserStatusChange', { userId });
	}

	isWebSocketConnected(): boolean
	{
		return this.connectionType === ConnectionType.WebSocket && this.connectionStatus === PullStatus.Online;
	}

	getConnectionPath(): string
	{
		return 'not available in SharedWorker mode';
	}

	getServerMode(): string
	{
		return 'n/a';
	}

	onLoginSuccess()
	{
		this.rpcAdapter.executeOutgoingRpcCommand('notifyLogin');
	}

	handleReady()
	{
		this.rpcAdapter.executeOutgoingRpcCommand(
			'notifyConfigTimestamp',
			{ configTimestamp: this.configTimestamp },
		);
	}

	handleIncomingMessage({ payload })
	{
		this.dispatchEvent(new CustomEvent(WorkerConnectorEvents.Message, { detail: payload }));
	}

	handleRevisionChanged({ revision })
	{
		this.dispatchEvent(new CustomEvent(WorkerConnectorEvents.RevisionChanged, { detail: { revision } }));
	}

	handleConnectionStatusChanged({ status, connectionType, isJsonRpc })
	{
		this.dispatchEvent(new CustomEvent(WorkerConnectorEvents.ConnectionStatus, { detail: { status } }));
		this.connectionType = connectionType;
		this.connectionStatus = status;
		this.isJsonRpcConnection = isJsonRpc;
	}

	onPortMessage(e: MessageEvent)
	{
		const message = e.data;
		this.rpcAdapter.handleIncomingMessage(message);
	}

	onOffline()
	{
		this.rpcAdapter.executeOutgoingRpcCommand('notifyOffline');
	}

	onOnline()
	{
		this.rpcAdapter.executeOutgoingRpcCommand('notifyOnline');
	}

	onPageHide()
	{
		this.rpcAdapter.executeOutgoingRpcCommand('bye');
	}

	isConnected()
	{
		return this.connectionStatus === PullStatus.Online;
	}

	async pingWorker()
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('bye');
	}

	async getWorkerLog()
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('getLog');
	}

	async getWorkerConfig()
	{
		return this.rpcAdapter.executeOutgoingRpcCommand('getConfig');
	}

	disconnect()
	{
		console.warn('Pull: SharedWorker mode: disconnection request ignored');
	}

	scheduleReconnect()
	{
		// nothing
	}

	resetSession()
	{
		// nothing
	}
}
