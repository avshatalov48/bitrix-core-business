// @flow
export class CurrencyItem
{
	#currency = '';
	#format = {};

	constructor(currency: string, format: {})
	{
		this.#currency = currency;
		this.#format = format;
	}

	getCurrency(): string
	{
		return this.#currency;
	}

	getFormat(): {}
	{
		return this.#format;
	}

	setFormat(format: {}): void
	{
		this.#format = format;
	}
}