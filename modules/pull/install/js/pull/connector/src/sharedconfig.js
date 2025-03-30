/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */

import { getDateForLog, getFunction, getTimestamp } from '../../util/src/util';

export class SharedConfig
{
	constructor(params = {})
	{
		this.storage = params.storage;

		this.ttl = 24 * 60 * 60;

		this.lsKeys = {
			websocketBlocked: 'bx-pull-websocket-blocked',
			longPollingBlocked: 'bx-pull-longpolling-blocked',
			loggingEnabled: 'bx-pull-logging-enabled',
		};

		this.callbacks = {
			onWebSocketBlockChanged: getFunction(params.onWebSocketBlockChanged),
		};

		if (this.storage)
		{
			window.addEventListener('storage', this.onLocalStorageSet.bind(this));
		}
	}

	onLocalStorageSet(params)
	{
		if (
			this.storage.compareKey(params.key, this.lsKeys.websocketBlocked)
			&& params.newValue !== params.oldValue
		)
		{
			this.callbacks.onWebSocketBlockChanged({
				isWebSocketBlocked: this.isWebSocketBlocked(),
			});
		}
	}

	isWebSocketBlocked(): boolean
	{
		if (!this.storage)
		{
			return false;
		}

		return this.storage.get(this.lsKeys.websocketBlocked, 0) > getTimestamp();
	}

	setWebSocketBlocked(isWebSocketBlocked)
	{
		if (!this.storage)
		{
			return;
		}

		try
		{
			this.storage.set(this.lsKeys.websocketBlocked, (isWebSocketBlocked ? getTimestamp() + this.ttl : 0));
		}
		catch (e)
		{
			console.error(`${getDateForLog()} Pull: Could not save WS_blocked flag in local storage. Error:`, e);
		}
	}

	isLongPollingBlocked(): boolean
	{
		if (!this.storage)
		{
			return false;
		}

		return this.storage.get(this.lsKeys.longPollingBlocked, 0) > getTimestamp();
	}

	setLongPollingBlocked(isLongPollingBlocked)
	{
		if (!this.storage)
		{
			return;
		}

		try
		{
			this.storage.set(this.lsKeys.longPollingBlocked, (isLongPollingBlocked ? getTimestamp() + this.ttl : 0));
		}
		catch (e)
		{
			console.error(`${getDateForLog()} Pull: Could not save LP_blocked flag in local storage. Error:`, e);
		}
	}
}
