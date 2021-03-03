// @flow

import {Reflection, Loc, Event} from 'main.core';

type MoneyEditorOptions = {
	input: HTMLElement;
	currency: string;
	value: string;
	callback: function;
};

export class MoneyEditor
{
	static currencyList = null;

	static defaultFormat = {
		'CURRENCY': '',
		'NAME': '',
		'FORMAT_STRING': '#',
		'DEC_POINT': '.',
		'THOUSANDS_VARIANT': null,
		'THOUSANDS_SEP': ' ',
		'DECIMALS': 2,
		'HIDE_ZERO': 'N',
		'BASE': 'N',
		'SEPARATOR': ' '
	};

	constructor(options: MoneyEditorOptions = {})
	{
		this.input = options.input;

		this.callback = options.callback;
		this.currency = options.currency;

		this.value = options.value || '';
		this.valueEditHandler = this.valueEdit.bind(this);

		Event.ready(this.init.bind(this));
	}

	static getCurrencyFormat(currency)
	{
		const list = this.getCurrencyList();

		if (typeof list[currency] !== 'undefined')
		{
			return list[currency];
		}

		return this.defaultFormat;
	}

	static getCurrencyList()
	{
		if(this.currencyList === null)
		{
			this.currencyList = Loc.getMessage('CURRENCY');
		}

		return this.currencyList;
	}

	init()
	{
		this.formatValue();
		Event.bind(this.input, 'bxchange', this.valueEditHandler);
		Event.unbind(this.input, 'change', this.valueEditHandler);
	}

	clean()
	{
		Event.unbind(this.input, 'bxchange', this.valueEditHandler);
		this.input = null;
	}

	valueEdit(e)
	{
		if(!!e && e.type === 'keyup' && e.code === 'Tab')
		{
			return;
		}

		this.formatValue();
	}

	setCurrency(currency)
	{
		this.value = MoneyEditor.getUnFormattedValue(this.input.value, this.currency);

		this.currency = currency;

		this.input.value = MoneyEditor.getFormattedValue(
			this.value,
			this.currency
		);

		this.callValueChangeCallback();
	}

	formatValue()
	{
		const cursorPos = BX.getCaretPosition(this.input);
		const originalValue = this.input.value;

		this.changeValue();

		if(originalValue.length > 0)
		{
			BX.setCaretPosition(this.input, cursorPos - originalValue.length + this.input.value.length);
		}
	}

	changeValue()
	{
		this.value = MoneyEditor.getUnFormattedValue(this.input.value, this.currency);

		this.input.value = MoneyEditor.getFormattedValue(
			this.value,
			this.currency
		);

		this.callValueChangeCallback();
	}

	callValueChangeCallback()
	{
		if(!!this.callback)
		{
			this.callback.apply(this, [this.value]);
		}

		BX.onCustomEvent(this, 'Currency::Editor::change', [this.value]);
	}

	static getBaseCurrencyId()
	{
		const listCurrency = this.getCurrencyList();
		for(let key in listCurrency)
		{
			if(!listCurrency.hasOwnProperty(key))
			{
				continue;
			}

			if(BX.prop.getString(listCurrency[key], 'BASE', 'N') === 'Y')
			{
				return key;
			}
		}
		return '';
	}

	static trimTrailingZeros(formattedValue, currency)
	{
		formattedValue = String(formattedValue);
		const currentFormat = this.getCurrencyFormat(currency);
		const ch = BX.prop.getString(currentFormat, 'DEC_POINT', '');

		return ch !== '' ? formattedValue.replace(new RegExp('\\' + ch + '0+$'), '') : formattedValue;
	}

	static escapeRegExp(text)
	{
		text = String(text);
		return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
	}

	static getUnFormattedValue(formattedValue, currency)
	{
		formattedValue = String(formattedValue);
		const currentFormat = this.getCurrencyFormat(currency);

		if (currentFormat['SEPARATOR'].length === 1)
		{
			return formattedValue
				.replace(new RegExp('[' + currentFormat['SEPARATOR'] + ']', 'g'), '')
				.replace(currentFormat['DEC_POINT'], '.')
				.replace(new RegExp('[^0-9\.]', 'g'), '');
		}
		else if(currentFormat['SEPARATOR'].length > 1)
		{
			return formattedValue
				.replace(new RegExp(this.escapeRegExp(currentFormat['SEPARATOR']), 'g'), '')
				.replace(currentFormat['DEC_POINT'], '.')
				.replace(new RegExp('[^0-9\.]', 'g'), '');
		}
		else
		{
			return formattedValue.replace(currentFormat['DEC_POINT'], '.')
				.replace(new RegExp('[^0-9\.]', 'g'), '');
		}
	}

	static getFormattedValue(baseValue, currency)
	{
		baseValue = String(baseValue);
		let valueLength = baseValue.length,
			formatValue = "",
			currentFormat = this.getCurrencyFormat(currency),
			regExp,
			decPointPosition,
			countDigit,
			i;

		if(valueLength > 0)
		{
			baseValue = baseValue.replace(/^0+/, '');
			if(baseValue.length <= 0)
			{
				baseValue = '0';
			}
			else if(baseValue.charAt(0) === '.')
			{
				baseValue = '0' + baseValue;
			}

			valueLength = baseValue.length;
		}

		if(currentFormat['SEPARATOR'] === ',' || currentFormat['SEPARATOR'] === '.')
		{
			regExp = new RegExp('[.,]');
		}
		else
		{
			regExp = new RegExp('[' + currentFormat['DEC_POINT'] + ',.]');
		}

		decPointPosition = baseValue.match(regExp);

		decPointPosition = decPointPosition === null ? baseValue.length : decPointPosition.index;
		countDigit = 0;
		for (i = 0; i < baseValue.length; i++)
		{
			const symbolPosition = baseValue.length - 1 - i;
			let symbol = baseValue.charAt(symbolPosition);
			const isDigit = ('0123456789'.indexOf(symbol) >= 0);
			if(isDigit)
			{
				countDigit++;
			}
			if(symbolPosition === decPointPosition)
			{
				countDigit = 0;
			}

			if(symbolPosition >= decPointPosition)
			{
				if(currentFormat['DEC_POINT'] === '.' && symbol === ',')
				{
					symbol = currentFormat['DEC_POINT'];
				}
				if(currentFormat['DEC_POINT'] === ',' && symbol === '.')
				{
					symbol = currentFormat['DEC_POINT'];
				}

				if(isDigit || (symbolPosition === decPointPosition && symbol === currentFormat['DEC_POINT']))
				{
					formatValue = symbol + formatValue;
				}
				else if(valueLength > symbolPosition)
				{
					valueLength--;
				}
			}
			else
			{
				if(isDigit)
				{
					formatValue = symbol + formatValue;
				}
				else if(valueLength > symbolPosition)
				{
					valueLength--;
				}
				if(isDigit && countDigit % 3 === 0 && countDigit !== 0 && symbolPosition !== 0)
				{
					formatValue = currentFormat['SEPARATOR'] + formatValue;
					if(valueLength >= symbolPosition)
					{
						valueLength++;
					}
				}
			}
		}

		if(currentFormat['DECIMALS'] > 0)
		{
			decPointPosition = formatValue.match(new RegExp('[' + currentFormat['DEC_POINT'] + ']'));
			decPointPosition = decPointPosition === null ? formatValue.length : decPointPosition.index;
			while(formatValue.length - 1 - decPointPosition > currentFormat['DECIMALS'])
			{
				if(valueLength >= formatValue.length - 1)
				{
					valueLength--;
				}
				formatValue = formatValue.substr(0, formatValue.length - 1);
			}
		}
		return formatValue;
	}
}

/** @deprecated use import { MoneyEditor } from 'currency.money-editor' */
Reflection.namespace('BX.Currency').Editor = MoneyEditor;