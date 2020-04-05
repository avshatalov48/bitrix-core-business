;(function(){
	'use strict';

	BX.namespace('BX.Currency');

	var currencyList = null,
		defaultFormat = {
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

	function getCurrencyList()
	{
		if(currencyList === null)
		{
			currencyList = BX.message('CURRENCY');
		}

		return currencyList;
	}

	function getCurrencyFormat(currency)
	{
		var list = getCurrencyList();

		if (typeof list[currency] !== 'undefined')
			return list[currency];

		return defaultFormat;
	}

	BX.Currency.Editor = function(param)
	{
		this.input = param.input;

		this.callback = param.callback;
		this.currency = param.currency;

		this.value = param.value || '';

		BX.ready(BX.delegate(this.init, this));
	};

	BX.Currency.Editor.prototype.init = function()
	{
		this.formatValue();
		BX.bind(this.input, 'bxchange', BX.proxy(this.valueEdit, this));
		BX.unbind(this.input, 'change', BX.proxy(this.valueEdit, this));
	};

	BX.Currency.Editor.prototype.clean = function()
	{
		BX.unbind(this.input, 'bxchange', BX.proxy(this.valueEdit, this));
		this.input = null;
	};

	BX.Currency.Editor.prototype.valueEdit = function(e){
		// hack to prevent selection loss while form editing with keyboard
		if(!!e && e.type === 'keyup' && e.code === 'Tab')
		{
			return;
		}

		this.formatValue();
	};

	BX.Currency.Editor.prototype.setCurrency = function(currency)
	{
		this.value = BX.Currency.Editor.getUnFormattedValue(this.input.value, this.currency);

		this.currency = currency;

		this.input.value = BX.Currency.Editor.getFormattedValue(
			this.value,
			this.currency
		);

		this.callValueChangeCallback();
	};

	BX.Currency.Editor.prototype.formatValue = function()
	{
		var cursorPos = BX.getCaretPosition(this.input),
			originalValue = this.input.value;

		this.changeValue();

		if(this.input.value.length > 0)
		{
			BX.setCaretPosition(this.input, cursorPos - originalValue.length + this.input.value.length);
		}
	};

	BX.Currency.Editor.prototype.changeValue = function()
	{
		this.value = BX.Currency.Editor.getUnFormattedValue(this.input.value, this.currency);

		this.input.value = BX.Currency.Editor.getFormattedValue(
			this.value,
			this.currency
		);

		this.callValueChangeCallback();
	};

	BX.Currency.Editor.prototype.callValueChangeCallback = function()
	{
		if(!!this.callback)
		{
			this.callback.apply(this, [this.value]);
		}

		BX.onCustomEvent(this, 'Currency::Editor::change', [this.value]);
	};

	/**
	 * static section
	 */

	BX.Currency.Editor.getBaseCurrencyId = function()
	{
		var listCurrency = getCurrencyList();
		for(var key in listCurrency)
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
	};

	BX.Currency.Editor.trimTrailingZeros = function(formattedValue, currency)
	{
		var currentFormat = getCurrencyFormat(currency),
			ch;
		ch = BX.prop.getString(currentFormat, 'DEC_POINT', '');

		return ch !== '' ? formattedValue.replace(new RegExp('\\' + ch + '0+$'), '') : formattedValue;
	};

	BX.Currency.Editor.escapeRegExp = function(text)
	{
		return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
	};

	BX.Currency.Editor.getUnFormattedValue = function(formattedValue, currency)
	{
		var currentFormat = getCurrencyFormat(currency);

		if (currentFormat['SEPARATOR'].length === 1)
		{
			return formattedValue
				.replace(new RegExp('[' + currentFormat['SEPARATOR'] + ']', 'g'), '')
				.replace(currentFormat['DEC_POINT'], '.');
		}
		else if(currentFormat['SEPARATOR'].length > 1)
		{
			return formattedValue
				.replace(new RegExp(this.escapeRegExp(currentFormat['SEPARATOR']), 'g'), '')
				.replace(currentFormat['DEC_POINT'], '.');
		}
		else
		{
			return formattedValue.replace(currentFormat['DEC_POINT'], '.');
		}
	};

	BX.Currency.Editor.getFormattedValue = function(baseValue, currency)
	{
		var valueLength = baseValue.length,
			formatValue = "",
			currentFormat = getCurrencyFormat(currency),
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
			var symbolPosition = baseValue.length - 1 - i;
			var symbol = baseValue.charAt(symbolPosition);
			var isDigit = ('0123456789'.indexOf(symbol) >= 0);
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
	};
})();