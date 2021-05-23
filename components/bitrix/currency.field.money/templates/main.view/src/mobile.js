import {Loc} from 'main.core';

let
	BX = window.BX,
	BXMobileApp = window.BXMobileApp;

let nodeMoney = (function ()
{
	let nodeMoney = function (node, containerValue, containerCurrency)
	{
		this.node = node;

		this.containerValue = containerValue;
		this.clickValue = BX.delegate(this.clickValue, this);
		BX.bind(this.containerValue, "click", this.clickValue);
		this.callbackValue = BX.delegate(this.callbackValue, this);

		this.containerCurrency = containerCurrency;
		this.clickCurrency = BX.delegate(this.clickCurrency, this);
		BX.bind(this.containerCurrency, "click", this.clickCurrency);
		this.callbackCurrency = BX.delegate(this.callbackCurrency, this);

		this.nodeValue = BX(containerValue);
		this.nodeCurrency = BX(containerCurrency);
	};
	nodeMoney.prototype = {
		clickValue: function (e)
		{
			this.showValue();
			return BX.PreventDefault(e);
		},
		showValue: function ()
		{
			window.app.exec('showPostForm', {
				attachButton: {
					items: []
				},
				attachFileSettings: {},
				attachedFiles: [],
				extraData: {},
				mentionButton: {},
				smileButton: {},
				message: {
					text: BX.util.htmlspecialcharsback(this.nodeValue.previousElementSibling.value)
				},
				okButton: {
					callback: this.callbackValue,
					name: Loc.getMessage('interface_form_save')
				},
				cancelButton: {
					callback: function ()
					{
					},
					name: Loc.getMessage('interface_form_cancel')
				}
			});
		},
		callbackValue: function (data)
		{
			data.text = (BX.util.htmlspecialchars(data.text) || '');
			this.containerValue.previousElementSibling.value = data.text;
			if (data.text == '')
			{
				this.containerValue.innerHTML = `<span class="placeholder">${this.node.getAttribute('placeholder')}</span>`;
			}
			else
			{
				this.containerValue.innerHTML = data.text;
			}
			this.node.value = data.text + '|' + this.containerCurrency.previousElementSibling.value;
			BX.onCustomEvent(this, 'onChange', [this, this.node]);
		},
		clickCurrency: function (e)
		{
			this.showCurrency();
			return BX.PreventDefault(e);
		},
		showCurrency: function ()
		{
			this.initCurrencies();
			BXMobileApp.UI.SelectPicker.show({
				callback: this.callbackCurrency,
				values: this.currencies,
				multiselect: false,
				default_value: this.defaultCurrency
			});
		},
		callbackCurrency: function(data){
			let currency = data.values[0];
			let value = this.containerValue.previousElementSibling.value;
			this.containerCurrency.innerHTML = currency;
			this.node.value = value + '|' + currency;
			BX.onCustomEvent(this, 'onChange', [this, this.node]);
		},
		initCurrencies: function ()
		{
			this.currencies = [];
			this.defaultCurrency = [];
			for (let ii = 0; ii < this.containerCurrency.previousElementSibling.options.length; ii++)
			{
				this.currencies.push(this.containerCurrency.previousElementSibling.options[ii].innerHTML);
				if (this.containerCurrency.previousElementSibling.options[ii].hasAttribute('selected'))
				{
					this.defaultCurrency.push(this.containerCurrency.previousElementSibling.options[ii].innerHTML);
				}
			}
		},
	};
	return nodeMoney;
})();

window.app.exec('enableCaptureKeyboard', true);

BX.Mobile.Field.Money = function (params)
{
	this.init(params);
};

BX.Mobile.Field.Money.prototype = {
	__proto__: BX.Mobile.Field.prototype,
	bindElement: function (node)
	{
		let result = null;
		if (BX(node))
		{
			result = new nodeMoney(node, BX(`${node.id}_value`), BX(`${node.id}_currency`));
		}
		return result;
	}
};