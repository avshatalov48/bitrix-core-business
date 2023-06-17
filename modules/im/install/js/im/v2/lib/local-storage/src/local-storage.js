import {Core} from 'im.v2.application.core';

const KEY_PREFIX = 'im-v2';

export class LocalStorageManager
{
	static instance: LocalStorageManager;

	#siteId: string;
	#userId: number;

	static getInstance(): LocalStorageManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	constructor()
	{
		this.#siteId = Core.getSiteId();
		this.#userId = Core.getUserId();
	}

	set(key: string, value: any)
	{
		const preparedValue = JSON.stringify(value);
		if (localStorage.getItem(this.#buildKey(key)) === preparedValue)
		{
			return;
		}

		localStorage.setItem(this.#buildKey(key), preparedValue);
	}

	get(key: string, defaultValue: any = null)
	{
		const result = localStorage.getItem(this.#buildKey(key));
		if (result === null)
		{
			return defaultValue;
		}

		try
		{
			return JSON.parse(result);
		}
		catch
		{
			return defaultValue;
		}
	}

	remove(key: string)
	{
		localStorage.removeItem(this.#buildKey(key));
	}

	#buildKey(key: string): string
	{
		return `${KEY_PREFIX}-${this.#siteId}-${this.#userId}-${key}`;
	}
}