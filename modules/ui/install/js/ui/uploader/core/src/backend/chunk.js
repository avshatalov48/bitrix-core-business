import { Type } from 'main.core';

export default class Chunk
{
	#data: Blob = null;
	#offset: number = 0;
	#retries: number[] = [];

	constructor(data, offset)
	{
		this.#data = data;
		this.#offset = offset;
	}

	getNextRetryDelay(): ?number
	{
		if (this.#retries.length === 0)
		{
			return null;
		}

		return this.#retries.shift();
	}

	setRetries(retries: number[]): void
	{
		if (Type.isArray(retries))
		{
			this.#retries = retries;
		}
	}

	getData(): Blob
	{
		return this.#data;
	}

	getOffset(): number
	{
		return this.#offset;
	}

	getSize(): number
	{
		return this.getData().size;
	}
}