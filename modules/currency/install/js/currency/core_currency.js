;(function(window) {

BX.namespace('BX.Currency');

if (BX.Currency.defaultFormat)
{
	return;
}

BX.mergeEx(BX.Currency, {
	currencyList: [],
	defaultFormat: {
		'FORMAT_STRING': '#',
		'DEC_POINT': '.',
		'THOUSANDS_SEP': ' ',
		'DECIMALS': 2,
		'HIDE_ZERO': 'N'
	},

	setCurrencyFormat: function(currency, format, replace)
	{
		var index = this.getCurrencyIndex(currency),
			currencyFormat = BX.clone(this.defaultFormat, true),
			i;

		replace = !!replace;
		if (index > -1 && !replace)
		{
			return;
		}
		if (index === -1)
		{
			index = this.currencyList.length;
		}

		for (i in currencyFormat)
		{
			if (currencyFormat.hasOwnProperty(i) && typeof format[i] !== 'undefined')
			{
				currencyFormat[i] = format[i];
			}
		}
		this.currencyList[index] = {
			'currency': currency,
			'format': BX.clone(currencyFormat, true)
		};
	},

	setCurrencies: function(currencies, replace)
	{
		var i;
		if (!!currencies && BX.type.isArray(currencies))
		{
			for (i = 0; i < currencies.length; i++)
			{
				if (!!currencies[i].CURRENCY && !!currencies[i].FORMAT)
				{
					this.setCurrencyFormat(currencies[i].CURRENCY, currencies[i].FORMAT, replace);
				}
			}
		}
	},

	getCurrencyFormat: function(currency)
	{
		var index = this.getCurrencyIndex(currency);
		return (index > -1 ? this.currencyList[index].format : false);
	},

	getCurrencyIndex: function(currency)
	{
		var i, index = -1;
		if (this.currencyList.length === 0)
		{
			return index;
		}
		for (i = 0; i < this.currencyList.length; i++)
		{
			if (this.currencyList[i].currency === currency)
			{
				index = i;
				break;
			}
		}
		return index;
	},

	clearCurrency: function(currency)
	{
		var index = this.getCurrencyIndex(currency);
		if (index > -1)
			this.currencyList = BX.util.deleteFromArray(this.currencyList, index);
	},

	clean: function()
	{
		this.currencyList = [];
	},

	currencyFormat: function (price, currency, useTemplate)
	{
		var result = '',
			format;
		useTemplate = !!useTemplate;
		format = this.getCurrencyFormat(currency);
		if (!!format && typeof format === 'object')
		{
			format.CURRENT_DECIMALS = format.DECIMALS;
			if (format.HIDE_ZERO === 'Y' && price == parseInt(price, 10))
				format.CURRENT_DECIMALS = 0;

			result = BX.util.number_format(price, format.CURRENT_DECIMALS, format.DEC_POINT, format.THOUSANDS_SEP);
			if (useTemplate)
				result = format.FORMAT_STRING.replace(/(^|[^&])#/, '$1' + result);
		}
		return result;
	}
});

})(window);
