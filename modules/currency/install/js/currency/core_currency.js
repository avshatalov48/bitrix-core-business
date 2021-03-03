;(function(window) {

if (!window.BX)
{
	window.BX = {};
}

var BX = window.BX;

BX.namespace('BX.Currency');

if (!BX.Currency.Core || BX.Currency.instance instanceof BX.Currency.Core)
{
	return;
}

BX.Currency.instance = new BX.Currency.Core();

BX.mergeEx(BX.Currency,
{
	setCurrencyFormat: function(currency, format, replace)
	{
		return BX.Currency.Core.setCurrencyFormat(currency, format, replace);
	},

	setCurrencies: function(currencies, replace)
	{
		return BX.Currency.Core.setCurrencies(currencies, replace);
	},

	getCurrencyFormat: function(currency)
	{
		return BX.Currency.Core.getCurrencyFormat(currency);
	},

	getCurrencyIndex: function(currency)
	{
		return BX.Currency.Core.getCurrencyIndex(currency);
	},

	clearCurrency: function(currency)
	{
		return BX.Currency.Core.clearCurrency(currency);
	},

	clean: function()
	{
		return BX.Currency.Core.clean();
	},

	currencyFormat: function (price, currency, useTemplate)
	{
		return BX.Currency.Core.currencyFormat(price, currency, useTemplate);
	},

	loadCurrencyFormat: function (currency)
	{
		return BX.Currency.Core.loadCurrencyFormat(currency);
	}
});

})(window);
