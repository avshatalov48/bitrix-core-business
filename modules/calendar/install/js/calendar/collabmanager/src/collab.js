import { Text } from 'main.core';

export class Collab
{
	#id = null;
	#name = null;

	constructor(data)
	{
		this.updateData(data);
	}

	updateData(data): void
	{
		this.#id = Text.toNumber(data.ID);
		this.#name = data.NAME?.toString();
	}

	getId(): number|null
	{
		return this.#id;
	}

	getName(): string|null
	{
		return this.#name;
	}
}
