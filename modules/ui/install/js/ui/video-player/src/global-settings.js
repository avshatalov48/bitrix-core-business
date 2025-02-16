import { type JsonObject, type JsonValue, Type } from 'main.core';

export class GlobalSettings
{
	#options: JsonObject = null;
	#localStorageKey: string = null;
	#batchStarted: boolean = false;

	constructor(localStorageKey: string)
	{
		this.#localStorageKey = localStorageKey;
	}

	#init()
	{
		if (this.#options === null)
		{
			this.#options = JSON.parse(window.localStorage.getItem(this.#localStorageKey)) || {};
		}
	}

	get(option: string, defaultValue?: any): any
	{
		this.#init();

		if (!Type.isUndefined(this.#options[option]))
		{
			return this.#options[option];
		}

		if (!Type.isUndefined(defaultValue))
		{
			return defaultValue;
		}

		return null;
	}

	set(option: string, value: JsonValue): void
	{
		this.#init();

		this.#options[option] = value;

		if (!this.#batchStarted)
		{
			this.#batchStarted = true;
			queueMicrotask(() => {
				this.#batchStarted = false;
				window.localStorage.setItem(this.#localStorageKey, JSON.stringify(this.#options));
			});
		}
	}
}
