export class BaseCollection
{
	values: [] = [];

	constructor()
	{
		this.clear();
	}

	clear(): void
	{
		this.values = [];
	}

	set(rawValues: []): void
	{
		this.values = this.validateValues(rawValues);
	}

	get(): []
	{
		return this.values;
	}

	validateValues(rawValues: []): []
	{
		return rawValues;
	}
}
