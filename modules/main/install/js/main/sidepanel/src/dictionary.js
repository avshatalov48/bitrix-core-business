import { Type, type JsonObject, type JsonValue } from 'main.core';

export class Dictionary
{
	#data: JsonObject = null;

	constructor(data: JsonObject)
	{
		if (!Type.isPlainObject(data))
		{
			throw new TypeError('The argument must be a plain object.');
		}

		this.#data = data;
	}

	set(key: string, value: JsonValue): void
	{
		if (!Type.isStringFilled(key))
		{
			throw new Error('The \'key\' must be a string.');
		}

		this.#data[key] = value;
	}

	get(key: string): JsonValue | undefined
	{
		return this.#data[key];
	}

	delete(key: string): void
	{
		delete this.#data[key];
	}

	has(key: string): boolean
	{
		return key in this.#data;
	}

	clear(): void
	{
		this.#data = {};
	}

	entries(): JsonObject
	{
		return this.#data;
	}
}
