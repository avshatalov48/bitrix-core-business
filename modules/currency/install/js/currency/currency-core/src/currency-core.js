// @flow

import {Reflection, Type} from 'main.core';
import {CurrencyItem} from "./currency-item";

export class CurrencyCore
{
	static currencies: CurrencyItem[] = [];

	static defaultFormat = {
		'FORMAT_STRING': '#',
		'DEC_POINT': '.',
		'THOUSANDS_SEP': ' ',
		'DECIMALS': 2,
		'HIDE_ZERO': 'N'
	};

	static getCurrencyList(): CurrencyItem[]
	{
		return this.currencies;
	}

	static setCurrencyFormat(currency: string, format, replace: boolean): void
	{
		if (!Type.isStringFilled(currency) || !Type.isPlainObject(format))
		{
			return;
		}

		const index = this.getCurrencyIndex(currency);

		if (index > -1 && !replace)
		{
			return;
		}

		const innerFormat = {...this.defaultFormat, ...format};

		if (index === -1)
		{
			this.currencies.push(new CurrencyItem(currency, innerFormat));
		}
		else
		{
			this.currencies[index].setFormat(innerFormat);
		}
	}

	static setCurrencies(currencies: [], replace: boolean)
	{
		if (Type.isArray(currencies))
		{
			for (let i = 0; i < currencies.length; i++)
			{
				if (
					!Type.isPlainObject(currencies[i])
					|| !Type.isStringFilled(currencies[i].CURRENCY)
					|| !Type.isPlainObject(currencies[i].FORMAT)
				)
				{
					continue;
				}

				this.setCurrencyFormat(currencies[i].CURRENCY, currencies[i].FORMAT, replace);
			}
		}
	}

	static getCurrencyFormat(currency: string)
	{
		const index = this.getCurrencyIndex(currency);

		return (index > -1 ? this.getCurrencyList()[index].getFormat() : false);
	}

	static getCurrencyIndex(currency: string): number
	{
		const currencyList = this.getCurrencyList();

		for (let i = 0; i < currencyList.length; i++)
		{
			if (currencyList[i].getCurrency() === currency)
			{
				return i;
			}
		}

		return -1;
	}

	static clearCurrency(currency)
	{
		const index = this.getCurrencyIndex(currency);
		if (index > -1)
		{
			this.currencies = BX.util.deleteFromArray(this.currencies, index);
		}
	}

	static clean()
	{
		this.currencies = [];
	}

	static currencyFormat(price: number, currency: string, useTemplate: boolean)
	{
		let result = '';

		const format = this.getCurrencyFormat(currency);
		if (Type.isObject(format))
		{
			format.CURRENT_DECIMALS = format.DECIMALS;

			if (format.HIDE_ZERO === 'Y' && price == parseInt(price, 10))
			{
				format.CURRENT_DECIMALS = 0;
			}

			result = BX.util.number_format(price, format.CURRENT_DECIMALS, format.DEC_POINT, format.THOUSANDS_SEP);
			if (useTemplate)
			{
				result = format.FORMAT_STRING.replace(/(^|[^&])#/, '$1' + result);
			}
		}

		return result;
	}

	static loadCurrencyFormat(currency)
	{
		return new Promise((resolve, reject) => {
			const index = this.getCurrencyIndex(currency);
			if (index > -1)
			{
				resolve(this.getCurrencyList()[index].getFormat());
			}
			else
			{
				BX.ajax.runAction("currency.format.get", {data: {currencyId: currency}})
					.then((response) => {
						const format = response.data;
						this.setCurrencyFormat(currency, format);
						resolve(format);
					})
					.catch((response) => {
						reject(response.errors);
					});
			}
		})
	}
}

/** @deprecated use import { CurrencyCore } from 'currency.core' */
Reflection.namespace('BX.Currency').Core = CurrencyCore;