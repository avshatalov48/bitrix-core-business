import { Type } from 'main.core';

export class BaseCollection
{
	#values: [] = [];

	constructor()
	{
		this.clear();
	}

	clear(): void
	{
		this.#values = [];
	}

	set(rawValues: []): void
	{
		this.#values = this.prepare(rawValues);
	}

	get(): []
	{
		return this.#values;
	}

	prepare(rawValues: []): []
	{
		const result = [];
		rawValues.forEach((value): void => {
			if (!Type.isArray(value) || value.length !== 2)
			{
				return;
			}

			if (!this.validateEntityId(value[0] ?? null))
			{
				return;
			}

			if (!this.validateValue(value[1] ?? null))
			{
				return;
			}

			result.push(value);
		});

		return result;
	}

	validateEntityId(entityId): boolean
	{
		return (Type.isStringFilled(entityId));
	}

	validateValue(value): boolean
	{
		return false;
	}
}
