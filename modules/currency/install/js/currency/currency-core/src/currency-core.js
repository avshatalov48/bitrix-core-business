// @flow

import { Reflection, Type, Extension } from 'main.core';
import { CurrencyItem } from './currency-item';

export class CurrencyCore
{
	static currencies: CurrencyItem[] = [];

	static defaultFormat = {
		FORMAT_STRING: '#',
		DEC_POINT: '.',
		THOUSANDS_SEP: ' ',
		DECIMALS: 2,
		HIDE_ZERO: 'N',
	};

	static region: string = '';

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

		const innerFormat = { ...this.defaultFormat, ...format };

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

	static initRegion(): void
	{
		if (this.region === '')
		{
			const settings = Extension.getSettings('currency.currency-core');
			this.region = settings.get('region') || '-';
		}
	}

	static checkInrFormat(currency: string): boolean
	{
		this.initRegion();

		return (
			currency === 'INR'
			&& (this.region === 'hi' || this.region === 'in')
		);
	}

	static currencyFormat(price: number, currency: string, useTemplate: boolean)
	{
		let result = '';

		const format = this.getCurrencyFormat(currency);
		if (Type.isObject(format))
		{
			price = Number(price);

			let currentDecimals = format.DECIMALS;
			const separator = format.SEPARATOR || format.THOUSANDS_SEP;

			if (format.HIDE_ZERO === 'Y' && Type.isInteger(price))
			{
				currentDecimals = 0;
			}

			if (this.checkInrFormat(currency))
			{
				result = this.numberFormatInr(
					price,
					currentDecimals,
					format.DEC_POINT,
					separator,
				);
			}
			else
			{
				result = BX.util.number_format(
					price,
					currentDecimals,
					format.DEC_POINT,
					separator,
				);
			}

			if (useTemplate)
			{
				result = format.FORMAT_STRING.replace(/(^|[^&])#/, '$1' + result);
			}
		}

		return result;
	}

	static getPriceControl(control: Element, currency: string)
	{
		let result = '';

		const format = this.getCurrencyFormat(currency);
		if (Type.isObject(format))
		{
			result = format.FORMAT_STRING.replace(/(^|[^&])#/, '$1' + control.outerHTML);
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
				BX.ajax.runAction('currency.format.get', { data: { currencyId: currency } })
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

	static numberFormatInr(value: number, decimals: number, decPoint: string, thousandsSep: string): string
	{
		if (Number.isNaN(decimals) || decimals < 0)
		{
			decimals = 2;
		}
		decPoint = decPoint || ',';
		thousandsSep = thousandsSep || '.';

		let sign: string = '';
		value = (+value || 0).toFixed(decimals);
		if (value < 0)
		{
			sign = '-';
			value = -value;
		}

		let i: string = parseInt(value, 10).toString();

		let km = '';
		let kw;

		if (i.length <= 3)
		{
			kw = i;
		}
		else
		{
			const rightTriad: string = thousandsSep + i.slice(-3);
			const leftBlock: string = i.slice(0,-3);
			const j = (leftBlock.length > 2 ? leftBlock.length % 2 : 0);

			km = (j ? leftBlock.slice(0, j) + thousandsSep : '');
			kw = leftBlock.slice(j).replace(/(\d{2})(?=\d)/g, "$1" + thousandsSep) + rightTriad;
		}
		let kd = (
			decimals
				? decPoint + Math.abs(value - i).toFixed(decimals).replace(/-/, '0').slice(2)
				: ''
		);

		return sign + km + kw + kd;
	}
}

/** @deprecated use import { CurrencyCore } from 'currency.core' */
Reflection.namespace('BX.Currency').Core = CurrencyCore;