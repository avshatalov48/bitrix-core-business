/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */

import { ConnectionType } from '../../../client/src/consts';
import { AbstractConnector } from './base';
import { getDateForLog } from '../../../util/src/util';

export class WebSocketConnector extends AbstractConnector
{
	socket: ?WebSocket;
	connectionType = ConnectionType.WebSocket;

	onSocketOpenHandler = this.onSocketOpen.bind(this);
	onSocketCloseHandler = this.onSocketClose.bind(this);
	onSocketErrorHandler = this.onSocketError.bind(this);
	onSocketMessageHandler = this.onSocketMessage.bind(this);

	connect()
	{
		if (this.socket)
		{
			if (this.socket.readyState === WebSocket.OPEN || this.socket.readyState === WebSocket.CONNECTING)
			{
				return;
			}

			this.socket.removeEventListener('open', this.onSocketOpenHandler);
			this.socket.removeEventListener('close', this.onSocketCloseHandler);
			this.socket.removeEventListener('error', this.onSocketErrorHandler);
			this.socket.removeEventListener('message', this.onSocketMessageHandler);

			this.socket.close();
			this.socket = null;
		}

		this.createSocket();
	}

	disconnect(code, message)
	{
		if (this.socket !== null)
		{
			this.socket.removeEventListener('open', this.onSocketOpenHandler);
			this.socket.removeEventListener('close', this.onSocketCloseHandler);
			this.socket.removeEventListener('error', this.onSocketErrorHandler);
			this.socket.removeEventListener('message', this.onSocketMessageHandler);

			this.socket.close(code, message);
		}
		this.socket = null;
		this.disconnectCode = code;
		this.disconnectReason = message;
		this.connected = false;
	}

	createSocket()
	{
		if (this.socket)
		{
			throw new Error('Socket already exists');
		}

		if (!this.path)
		{
			throw new Error('Websocket connection path is not defined');
		}

		this.socket = new WebSocket(this.path);
		this.socket.binaryType = 'arraybuffer';

		this.socket.addEventListener('open', this.onSocketOpenHandler);
		this.socket.addEventListener('close', this.onSocketCloseHandler);
		this.socket.addEventListener('error', this.onSocketErrorHandler);
		this.socket.addEventListener('message', this.onSocketMessageHandler);
	}

	/**
	 * Sends some data to the server via websocket connection.
	 * @param {ArrayBuffer} buffer Data to send.
	 */
	send(buffer): boolean
	{
		if (!this.socket || this.socket.readyState !== 1)
		{
			console.error(`${getDateForLog()}: Pull: WebSocket is not connected`);

			return false;
		}

		this.socket.send(buffer);

		return true;
	}

	onSocketOpen()
	{
		this.connected = true;
	}

	onSocketClose(e)
	{
		this.socket = null;
		this.disconnectCode = e.code;
		this.disconnectReason = e.reason;
		this.connected = false;
	}

	onSocketError(e)
	{
		this.callbacks.onError(e);
	}

	onSocketMessage(e)
	{
		this.callbacks.onMessage(e.data);
	}

	destroy()
	{
		if (this.socket)
		{
			this.socket.close();
			this.socket = null;
		}
	}
}
