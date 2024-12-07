// @flow

import { Reflection, Loc, Event, Extension } from 'main.core';

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
		CURRENCY: '',
		NAME: '',
		FORMAT_STRING: '#',
		DEC_POINT: '.',
		THOUSANDS_VARIANT: null,
		THOUSANDS_SEP: ' ',
		DECIMALS: 2,
		HIDE_ZERO: 'N',
		BASE: 'N',
		SEPARATOR: ' ',
	};

	static region: string = '';

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
		if (this.currencyList === null)
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
		if (!!e && e.type === 'keyup' && e.code === 'Tab')
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

		if (originalValue.length > 0)
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
		if (!!this.callback)
		{
			this.callback.apply(this, [this.value]);
		}

		BX.onCustomEvent(this, 'Currency::Editor::change', [this.value]);
	}

	static getBaseCurrencyId()
	{
		const listCurrency = this.getCurrencyList();
		for (let key in listCurrency)
		{
			if (!listCurrency.hasOwnProperty(key))
			{
				continue;
			}

			if (BX.prop.getString(listCurrency[key], 'BASE', 'N') === 'Y')
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
				.replace(new RegExp('[^0-9\.]', 'g'), '')
			;
		}
		else if (currentFormat['SEPARATOR'].length > 1)
		{
			return formattedValue
				.replace(new RegExp(this.escapeRegExp(currentFormat['SEPARATOR']), 'g'), '')
				.replace(currentFormat['DEC_POINT'], '.')
				.replace(new RegExp('[^0-9\.]', 'g'), '')
			;
		}
		else
		{
			return formattedValue.replace(currentFormat['DEC_POINT'], '.')
				.replace(new RegExp('[^0-9\.]', 'g'), '')
			;
		}
	}

	static getFormattedValue(baseValue, currency)
	{
		baseValue = String(baseValue);
		if (baseValue === '')
		{
			return '';
		}

		baseValue = baseValue.replace(/^0+/, '');
		if (baseValue === '')
		{
			baseValue = '0';
		}
		else if (baseValue.charAt(0) === '.')
		{
			baseValue = '0' + baseValue;
		}

		let sign = '';
		if (baseValue.charAt(0) === '-')
		{
			sign = '-';
			baseValue = baseValue.slice(1);
		}

		const currentFormat = this.getCurrencyFormat(currency);
		const decPoint: string = currentFormat.DEC_POINT;
		const decimals: number = currentFormat.DECIMALS;
		const separator: string = currentFormat.SEPARATOR || currentFormat.THOUSANDS_SEP;
		const gecPointMask =
			(decPoint === ',' || decPoint === '.')
				? new RegExp('[.,]')
				: new RegExp('[' + decPoint + '.,]')
		;

		const digitMask = new RegExp('\D', 'g');
		let wholePart;
		let fraction;
		let decimalPoint;
		const decPointPosition = baseValue.match(gecPointMask);
		if (decPointPosition === null)
		{
			wholePart = baseValue.replaceAll(digitMask, '');
			fraction = '';
			decimalPoint = '';
		}
		else
		{
			wholePart = baseValue.slice(0, decPointPosition.index).replaceAll(digitMask, '');
			fraction = baseValue.slice(decPointPosition.index + 1).replaceAll(digitMask, '');
			decimalPoint = decPoint;
		}
		if (decimals === 0)
		{
			fraction = '';
			decimalPoint = '';
		}

		let result: string = sign;
		if (this.checkInrFormat(currency))
		{
			if (wholePart.length <= 3)
			{
				result = result + wholePart;
			}
			else
			{
				let rightTriad: string = separator + wholePart.slice(-3);
				let leftBlock: string = wholePart.slice(0, -3);
				const j = (leftBlock.length > 2 ? leftBlock.length % 2 : 0);

				result =
					result
					+ (j ? leftBlock.slice(0, j) + separator : '')
					+ leftBlock.slice(j).replace(/(\d{2})(?=\d)/g, "$1" + separator)
					+ rightTriad
				;
			}
		}
		else
		{
			const j = (wholePart.length > 3 ? wholePart.length % 3 : 0);

			result =
				result
				+ (j ? wholePart.slice(0, j) + separator : '')
				+ wholePart.slice(j).replace(/(\d{3})(?=\d)/g, "$1" + separator)
			;
		}

		if (decimals > 0)
		{
			result = result + decimalPoint;
			if (fraction !== '')
			{
				if (decimals < fraction.length)
				{
					fraction = fraction.slice(0, decimals);
				}
				result = result + fraction;
			}
		}

		return result;
	}

	static initRegion(): void
	{
		if (this.region === '')
		{
			const settings = Extension.getSettings('currency.money-editor');
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
}

/** @deprecated use import { MoneyEditor } from 'currency.money-editor' */
Reflection.namespace('BX.Currency').Editor = MoneyEditor;
