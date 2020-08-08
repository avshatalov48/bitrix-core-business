this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports,main_core) {
	'use strict';

	var BX = window.BX,
	    BXMobileApp = window.BXMobileApp;

	var nodeMoney = function () {
	  var nodeMoney = function nodeMoney(node, containerValue, containerCurrency) {
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
	    clickValue: function clickValue(e) {
	      this.showValue();
	      return BX.PreventDefault(e);
	    },
	    showValue: function showValue() {
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
	          name: main_core.Loc.getMessage('interface_form_save')
	        },
	        cancelButton: {
	          callback: function callback() {},
	          name: main_core.Loc.getMessage('interface_form_cancel')
	        }
	      });
	    },
	    callbackValue: function callbackValue(data) {
	      data.text = BX.util.htmlspecialchars(data.text) || '';
	      this.containerValue.previousElementSibling.value = data.text;

	      if (data.text == '') {
	        this.containerValue.innerHTML = "<span class=\"placeholder\">".concat(this.node.getAttribute('placeholder'), "</span>");
	      } else {
	        this.containerValue.innerHTML = data.text;
	      }

	      this.node.value = data.text + '|' + this.containerCurrency.previousElementSibling.value;
	      BX.onCustomEvent(this, 'onChange', [this, this.node]);
	    },
	    clickCurrency: function clickCurrency(e) {
	      this.showCurrency();
	      return BX.PreventDefault(e);
	    },
	    showCurrency: function showCurrency() {
	      this.initCurrencies();
	      BXMobileApp.UI.SelectPicker.show({
	        callback: this.callbackCurrency,
	        values: this.currencies,
	        multiselect: false,
	        default_value: this.defaultCurrency
	      });
	    },
	    callbackCurrency: function callbackCurrency(data) {
	      var currency = data.values[0];
	      var value = this.containerValue.previousElementSibling.value;
	      this.containerCurrency.innerHTML = currency;
	      this.node.value = value + '|' + currency;
	      BX.onCustomEvent(this, 'onChange', [this, this.node]);
	    },
	    initCurrencies: function initCurrencies() {
	      this.currencies = [];
	      this.defaultCurrency = [];

	      for (var ii = 0; ii < this.containerCurrency.previousElementSibling.options.length; ii++) {
	        this.currencies.push(this.containerCurrency.previousElementSibling.options[ii].innerHTML);

	        if (this.containerCurrency.previousElementSibling.options[ii].hasAttribute('selected')) {
	          this.defaultCurrency.push(this.containerCurrency.previousElementSibling.options[ii].innerHTML);
	        }
	      }
	    }
	  };
	  return nodeMoney;
	}();

	window.app.exec('enableCaptureKeyboard', true);

	BX.Mobile.Field.Money = function (params) {
	  this.init(params);
	};

	BX.Mobile.Field.Money.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;

	    if (BX(node)) {
	      result = new nodeMoney(node, BX("".concat(node.id, "_value")), BX("".concat(node.id, "_currency")));
	    }

	    return result;
	  }
	};

}((this.BX.Mobile.Field.Money = this.BX.Mobile.Field.Money || {}),BX));
//# sourceMappingURL=mobile.js.map
