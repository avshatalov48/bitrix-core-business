/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var MoneyEditor = /*#__PURE__*/function () {
	  function MoneyEditor() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MoneyEditor);
	    this.input = options.input;
	    this.callback = options.callback;
	    this.currency = options.currency;
	    this.value = options.value || '';
	    this.valueEditHandler = this.valueEdit.bind(this);
	    main_core.Event.ready(this.init.bind(this));
	  }
	  babelHelpers.createClass(MoneyEditor, [{
	    key: "init",
	    value: function init() {
	      this.formatValue();
	      main_core.Event.bind(this.input, 'bxchange', this.valueEditHandler);
	      main_core.Event.unbind(this.input, 'change', this.valueEditHandler);
	    }
	  }, {
	    key: "clean",
	    value: function clean() {
	      main_core.Event.unbind(this.input, 'bxchange', this.valueEditHandler);
	      this.input = null;
	    }
	  }, {
	    key: "valueEdit",
	    value: function valueEdit(e) {
	      if (!!e && e.type === 'keyup' && e.code === 'Tab') {
	        return;
	      }
	      this.formatValue();
	    }
	  }, {
	    key: "setCurrency",
	    value: function setCurrency(currency) {
	      this.value = MoneyEditor.getUnFormattedValue(this.input.value, this.currency);
	      this.currency = currency;
	      this.input.value = MoneyEditor.getFormattedValue(this.value, this.currency);
	      this.callValueChangeCallback();
	    }
	  }, {
	    key: "formatValue",
	    value: function formatValue() {
	      var cursorPos = BX.getCaretPosition(this.input);
	      var originalValue = this.input.value;
	      this.changeValue();
	      if (originalValue.length > 0) {
	        BX.setCaretPosition(this.input, cursorPos - originalValue.length + this.input.value.length);
	      }
	    }
	  }, {
	    key: "changeValue",
	    value: function changeValue() {
	      this.value = MoneyEditor.getUnFormattedValue(this.input.value, this.currency);
	      this.input.value = MoneyEditor.getFormattedValue(this.value, this.currency);
	      this.callValueChangeCallback();
	    }
	  }, {
	    key: "callValueChangeCallback",
	    value: function callValueChangeCallback() {
	      if (!!this.callback) {
	        this.callback.apply(this, [this.value]);
	      }
	      BX.onCustomEvent(this, 'Currency::Editor::change', [this.value]);
	    }
	  }], [{
	    key: "getCurrencyFormat",
	    value: function getCurrencyFormat(currency) {
	      var list = this.getCurrencyList();
	      if (typeof list[currency] !== 'undefined') {
	        return list[currency];
	      }
	      return this.defaultFormat;
	    }
	  }, {
	    key: "getCurrencyList",
	    value: function getCurrencyList() {
	      if (this.currencyList === null) {
	        this.currencyList = main_core.Loc.getMessage('CURRENCY');
	      }
	      return this.currencyList;
	    }
	  }, {
	    key: "getBaseCurrencyId",
	    value: function getBaseCurrencyId() {
	      var listCurrency = this.getCurrencyList();
	      for (var key in listCurrency) {
	        if (!listCurrency.hasOwnProperty(key)) {
	          continue;
	        }
	        if (BX.prop.getString(listCurrency[key], 'BASE', 'N') === 'Y') {
	          return key;
	        }
	      }
	      return '';
	    }
	  }, {
	    key: "trimTrailingZeros",
	    value: function trimTrailingZeros(formattedValue, currency) {
	      formattedValue = String(formattedValue);
	      var currentFormat = this.getCurrencyFormat(currency);
	      var ch = BX.prop.getString(currentFormat, 'DEC_POINT', '');
	      return ch !== '' ? formattedValue.replace(new RegExp('\\' + ch + '0+$'), '') : formattedValue;
	    }
	  }, {
	    key: "escapeRegExp",
	    value: function escapeRegExp(text) {
	      text = String(text);
	      return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
	    }
	  }, {
	    key: "getUnFormattedValue",
	    value: function getUnFormattedValue(formattedValue, currency) {
	      formattedValue = String(formattedValue);
	      var currentFormat = this.getCurrencyFormat(currency);
	      if (currentFormat['SEPARATOR'].length === 1) {
	        return formattedValue.replace(new RegExp('[' + currentFormat['SEPARATOR'] + ']', 'g'), '').replace(currentFormat['DEC_POINT'], '.').replace(new RegExp('[^0-9\.]', 'g'), '');
	      } else if (currentFormat['SEPARATOR'].length > 1) {
	        return formattedValue.replace(new RegExp(this.escapeRegExp(currentFormat['SEPARATOR']), 'g'), '').replace(currentFormat['DEC_POINT'], '.').replace(new RegExp('[^0-9\.]', 'g'), '');
	      } else {
	        return formattedValue.replace(currentFormat['DEC_POINT'], '.').replace(new RegExp('[^0-9\.]', 'g'), '');
	      }
	    }
	  }, {
	    key: "getFormattedValue",
	    value: function getFormattedValue(baseValue, currency) {
	      baseValue = String(baseValue);
	      if (baseValue === '') {
	        return '';
	      }
	      baseValue = baseValue.replace(/^0+/, '');
	      if (baseValue === '') {
	        baseValue = '0';
	      } else if (baseValue.charAt(0) === '.') {
	        baseValue = '0' + baseValue;
	      }
	      var sign = '';
	      if (baseValue.charAt(0) === '-') {
	        sign = '-';
	        baseValue = baseValue.slice(1);
	      }
	      var currentFormat = this.getCurrencyFormat(currency);
	      var decPoint = currentFormat.DEC_POINT;
	      var decimals = currentFormat.DECIMALS;
	      var separator = currentFormat.SEPARATOR || currentFormat.THOUSANDS_SEP;
	      var gecPointMask = decPoint === ',' || decPoint === '.' ? new RegExp('[.,]') : new RegExp('[' + decPoint + '.,]');
	      var digitMask = new RegExp('\D', 'g');
	      var wholePart;
	      var fraction;
	      var decimalPoint;
	      var decPointPosition = baseValue.match(gecPointMask);
	      if (decPointPosition === null) {
	        wholePart = baseValue.replaceAll(digitMask, '');
	        fraction = '';
	        decimalPoint = '';
	      } else {
	        wholePart = baseValue.slice(0, decPointPosition.index).replaceAll(digitMask, '');
	        fraction = baseValue.slice(decPointPosition.index + 1).replaceAll(digitMask, '');
	        decimalPoint = decPoint;
	      }
	      if (decimals === 0) {
	        fraction = '';
	        decimalPoint = '';
	      }
	      var result = sign;
	      if (this.checkInrFormat(currency)) {
	        if (wholePart.length <= 3) {
	          result = result + wholePart;
	        } else {
	          var rightTriad = separator + wholePart.slice(-3);
	          var leftBlock = wholePart.slice(0, -3);
	          var j = leftBlock.length > 2 ? leftBlock.length % 2 : 0;
	          result = result + (j ? leftBlock.slice(0, j) + separator : '') + leftBlock.slice(j).replace(/(\d{2})(?=\d)/g, "$1" + separator) + rightTriad;
	        }
	      } else {
	        var _j = wholePart.length > 3 ? wholePart.length % 3 : 0;
	        result = result + (_j ? wholePart.slice(0, _j) + separator : '') + wholePart.slice(_j).replace(/(\d{3})(?=\d)/g, "$1" + separator);
	      }
	      if (decimals > 0) {
	        result = result + decimalPoint;
	        if (fraction !== '') {
	          if (decimals < fraction.length) {
	            fraction = fraction.slice(0, decimals);
	          }
	          result = result + fraction;
	        }
	      }
	      return result;
	    }
	  }, {
	    key: "initRegion",
	    value: function initRegion() {
	      if (this.region === '') {
	        var settings = main_core.Extension.getSettings('currency.money-editor');
	        this.region = settings.get('region') || '-';
	      }
	    }
	  }, {
	    key: "checkInrFormat",
	    value: function checkInrFormat(currency) {
	      this.initRegion();
	      return currency === 'INR' && (this.region === 'hi' || this.region === 'in');
	    }
	  }]);
	  return MoneyEditor;
	}();

	/** @deprecated use import { MoneyEditor } from 'currency.money-editor' */
	babelHelpers.defineProperty(MoneyEditor, "currencyList", null);
	babelHelpers.defineProperty(MoneyEditor, "defaultFormat", {
	  CURRENCY: '',
	  NAME: '',
	  FORMAT_STRING: '#',
	  DEC_POINT: '.',
	  THOUSANDS_VARIANT: null,
	  THOUSANDS_SEP: ' ',
	  DECIMALS: 2,
	  HIDE_ZERO: 'N',
	  BASE: 'N',
	  SEPARATOR: ' '
	});
	babelHelpers.defineProperty(MoneyEditor, "region", '');
	main_core.Reflection.namespace('BX.Currency').Editor = MoneyEditor;

	exports.MoneyEditor = MoneyEditor;

}((this.BX.Currency = this.BX.Currency || {}),BX));
//# sourceMappingURL=money-editor.bundle.js.map
