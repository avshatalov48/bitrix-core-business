/* eslint-disable @bitrix24/bitrix24-rules/no-bx-message */

import { isNotEmptyString } from '../../util/src/util';

export class StorageManager
{
	constructor(params = {})
	{
		this.userId = params.userId ?? (BX.message && BX.message.USER_ID ? BX.message.USER_ID : 0);
		this.siteId = params.siteId ?? (BX.message && BX.message.SITE_ID ? BX.message.SITE_ID : 'none');
	}

	set(name: string, value: any): void
	{
		if (!window.localStorage)
		{
			return false;
		}

		let encoded = value;
		if (isNotEmptyString(value))
		{
			encoded = JSON.stringify(value);
		}

		return window.localStorage.setItem(this.getKey(name), encoded);
	}

	get(name: string, defaultValue: any = null): any
	{
		if (!window.localStorage)
		{
			return defaultValue;
		}

		const result = window.localStorage.getItem(this.getKey(name));
		if (result === null)
		{
			return defaultValue;
		}

		return JSON.parse(result);
	}

	remove(name: string): void
	{
		if (!window.localStorage)
		{
			return;
		}

		window.localStorage.removeItem(this.getKey(name));
	}

	getKey(name: string): string
	{
		return `bx-pull-${this.userId}-${this.siteId}-${name}`;
	}

	compareKey(eventKey, userKey): boolean
	{
		return eventKey === this.getKey(userKey);
	}
}
