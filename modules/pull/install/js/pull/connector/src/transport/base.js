/* eslint-disable @bitrix24/bitrix24-rules/no-pseudo-private */
/* eslint-disable no-underscore-dangle */
// noinspection ES6PreferShortImport

import { isFunction } from '../../../util/src/util';

export type ConnectorOptions = {
	pathGetter: () => string,
	onOpen: () => void,
	onDisconnect: () => void,
	onError: () => void,
	onMessage: () => void,
}

export class AbstractConnector
{
	_connected = false;
	connectionType = '';

	disconnectCode = '';
	disconnectReason = '';

	constructor(config: ConnectorOptions)
	{
		this.pathGetter = config.pathGetter;
		this.callbacks = {
			onOpen: isFunction(config.onOpen) ? config.onOpen : function() {},
			onDisconnect: isFunction(config.onDisconnect) ? config.onDisconnect : function() {},
			onError: isFunction(config.onError) ? config.onError : function() {},
			onMessage: isFunction(config.onMessage) ? config.onMessage : function() {},
		};
	}

	get connected(): boolean
	{
		return this._connected;
	}

	set connected(value: boolean)
	{
		if (value === this._connected)
		{
			return;
		}

		this._connected = value;

		if (this._connected)
		{
			this.callbacks.onOpen();
		}
		else
		{
			this.callbacks.onDisconnect({
				code: this.disconnectCode,
				reason: this.disconnectReason,
			});
		}
	}

	get path(): string
	{
		return this.pathGetter();
	}
}
