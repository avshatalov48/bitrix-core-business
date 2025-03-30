/* eslint-disable max-classes-per-file */

import { buildQueryString, isNotEmptyString, isPlainObject } from '../../util/src/util';

export class MiniRest
{
	sessid = '';
	queryParams = {};

	constructor(options = {})
	{
		if (isNotEmptyString(options.sessid))
		{
			this.sessid = options.sessid;
		}

		if (isPlainObject(options.queryParams))
		{
			this.queryParams = options.queryParams;
		}
	}

	async callMethod(method: string, params: ?Object = {}, _ = null, __ = null, logTag: string = ''): Promise
	{
		const lt = logTag ? `?logTag=${logTag}` : '';
		const url = `/rest/${method}.json${lt}`;

		let decoded = null;
		let responseStatus = 0;
		try
		{
			const response = await fetch(url, this.getFetchOptions({ ...this.queryParams, ...params }));
			responseStatus = response.status;
			decoded = await response.json();
		}
		catch
		{
			throw new RestCompatResult(0, { error: 'NETWORK_ERROR', error_description: 'Network error' });
		}

		if (decoded && 'error' in decoded && decoded.error === 'session_failed' && 'sessid' in decoded && isNotEmptyString(decoded.sessid))
		{
			this.sessid = decoded.sessid;
			// after setting sessid fetch options should differ
			try
			{
				const fallbackResponse = await fetch(url, this.getFetchOptions({ ...this.queryParams, ...params }));
				responseStatus = fallbackResponse.status;
				decoded = await fallbackResponse.json();
			}
			catch
			{
				throw new RestCompatResult(0, { error: 'NETWORK_ERROR', error_description: 'Network error' });
			}
		}

		const result = new RestCompatResult(responseStatus, decoded);
		if (result.isError)
		{
			throw result;
		}

		return result;
	}

	getFetchOptions(params: ?Object = {}): Object
	{
		const query = buildQueryString({
			sessid: this.getSessid(),
			...params,
		});

		return {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'X-Bitrix-Csrf-Token': this.getSessid(),
			},
			credentials: 'same-origin',
			body: query,
		};
	}

	getSessid(): string
	{
		if (this.sessid !== '')
		{
			return this.sessid;
		}

		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
		if (typeof BX !== 'undefined' && BX.bitrix_sessid)
		{
			// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
			return BX.bitrix_sessid();
		}

		return '';
	}
}

class RestCompatResult
{
	constructor(status, answer)
	{
		this.isError = false;
		this.status = status;
		this.answer = answer;

		if (typeof this.answer.error !== 'undefined')
		{
			this.isError = true;
			this.answer.ex = new RestCompatError(this.status, typeof this.answer.error === 'string' ? this.answer : this.answer.error)
		}
	}

	data()
	{
		return this.answer.result;
	}

	time()
	{
		return this.answer.time;
	}

	error()
	{
		return this.answer.ex;
	}
}

class RestCompatError
{
	constructor(status, ex)
	{
		this.status = status;
		this.ex = ex;
	}

	getError()
	{
		return this.ex;
	}

	getStatus()
	{
		return this.status;
	}

	toString(): string
	{
		const description = this.ex.error_description ? `: ${this.ex.error_description}` : '';

		return `${this.ex.error}${description} (${this.status})`;
	}
}
