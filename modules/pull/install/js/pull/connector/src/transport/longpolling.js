/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */

import { ConnectionType } from '../../../client/src/consts';
import { AbstractConnector } from './base';
import { getDateForLog, isNotEmptyString } from '../../../util/src/util';
import type { ConnectorOptions } from './base';

const LONG_POLLING_TIMEOUT = 60;

type LongPollingConnectorOptions = ConnectorOptions & {
	isBinary: boolean,
	getPublicationPath: () => string,
}

export class LongPollingConnector extends AbstractConnector
{
	connectionType = ConnectionType.LongPolling;
	active = false;
	requestTimeout = null;
	failureTimeout = null;
	requestAborted = false;

	constructor(config: LongPollingConnectorOptions)
	{
		super(config);

		this.xhr = this.createXhr();
		this.isBinary = config.isBinary;
	}

	createXhr(): XMLHttpRequest
	{
		const result = new XMLHttpRequest();
		if (this.isBinary)
		{
			result.responseType = 'arraybuffer';
		}
		result.addEventListener('readystatechange', this.onXhrReadyStateChange.bind(this));

		return result;
	}

	connect()
	{
		this.active = true;
		this.performRequest();
	}

	disconnect(code, reason)
	{
		this.active = false;

		if (this.failureTimeout)
		{
			clearTimeout(this.failureTimeout);
			this.failureTimeout = null;
		}

		if (this.requestTimeout)
		{
			clearTimeout(this.requestTimeout);
			this.requestTimeout = null;
		}

		if (this.xhr)
		{
			this.requestAborted = true;
			this.xhr.abort();
		}

		this.disconnectCode = code;
		this.disconnectReason = reason;
		this.connected = false;
	}

	performRequest()
	{
		if (!this.active)
		{
			return;
		}

		if (!this.path)
		{
			throw new Error('Long polling connection path is not defined');
		}

		if (this.xhr.readyState !== 0 && this.xhr.readyState !== 4)
		{
			return;
		}

		clearTimeout(this.failureTimeout);
		clearTimeout(this.requestTimeout);

		this.failureTimeout = setTimeout(
			() => {
				this.connected = true;
			},
			5000,
		);
		this.requestTimeout = setTimeout(this.onRequestTimeout.bind(this), LONG_POLLING_TIMEOUT * 1000);

		this.xhr.open('GET', this.path);
		this.xhr.send();
	}

	onRequestTimeout()
	{
		this.requestAborted = true;
		this.xhr.abort();
		this.performRequest();
	}

	onXhrReadyStateChange()
	{
		if (this.xhr.readyState === 4)
		{
			if (!this.requestAborted || this.xhr.status === 200)
			{
				this.onResponse(this.xhr.response);
			}
			this.requestAborted = false;
		}
	}

	/**
	 * Sends some data to the server via http request.
	 */
	send(buffer: ArrayBuffer): void
	{
		const path = this.parent.getPublicationPath();
		if (!path)
		{
			console.error(`${getDateForLog()}: Pull: publication path is empty`);

			return;
		}

		const xhr = new XMLHttpRequest();
		xhr.open('POST', path);
		xhr.send(buffer);
	}

	onResponse(response)
	{
		if (this.failureTimeout)
		{
			clearTimeout(this.failureTimeout);
			this.failureTimeout = 0;
		}

		if (this.requestTimeout)
		{
			clearTimeout(this.requestTimeout);
			this.requestTimeout = 0;
		}

		if (this.xhr.status === 200)
		{
			this.connected = true;
			if (isNotEmptyString(response) || (response instanceof ArrayBuffer))
			{
				this.callbacks.onMessage(response);
			}
			else
			{
				this.parent.session.mid = null;
			}
			this.performRequest();
		}
		else if (this.xhr.status === 304)
		{
			this.connected = true;
			if (this.xhr.getResponseHeader('Expires') === 'Thu, 01 Jan 1973 11:11:01 GMT')
			{
				const lastMessageId = this.xhr.getResponseHeader('Last-Message-Id');
				if (isNotEmptyString(lastMessageId))
				{
					this.parent.setLastMessageId(lastMessageId);
				}
			}
			this.performRequest();
		}
		else
		{
			this.callbacks.onError('Could not connect to the server');
			this.connected = false;
		}
	}
}
