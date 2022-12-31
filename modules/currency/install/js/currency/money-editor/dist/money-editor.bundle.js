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
	      var valueLength = baseValue.length,
	          formatValue = "",
	          currentFormat = this.getCurrencyFormat(currency),
	          regExp,
	          decPointPosition,
	          countDigit,
	          i;

	      if (valueLength > 0) {
	        baseValue = baseValue.replace(/^0+/, '');

	        if (baseValue.length <= 0) {
	          baseValue = '0';
	        } else if (baseValue.charAt(0) === '.') {
	          baseValue = '0' + baseValue;
	        }

	        valueLength = baseValue.length;
	      }

	      if (currentFormat['SEPARATOR'] === ',' || currentFormat['SEPARATOR'] === '.') {
	        regExp = new RegExp('[.,]');
	      } else {
	        regExp = new RegExp('[' + currentFormat['DEC_POINT'] + ',.]');
	      }

	      decPointPosition = baseValue.match(regExp);
	      decPointPosition = decPointPosition === null ? baseValue.length : decPointPosition.index;
	      countDigit = 0;

	      for (i = 0; i < baseValue.length; i++) {
	        var symbolPosition = baseValue.length - 1 - i;
	        var symbol = baseValue.charAt(symbolPosition);
	        var isDigit = '0123456789'.indexOf(symbol) >= 0;

	        if (isDigit) {
	          countDigit++;
	        }

	        if (symbolPosition === decPointPosition) {
	          countDigit = 0;
	        }

	        if (symbolPosition >= decPointPosition) {
	          if (currentFormat['DEC_POINT'] === '.' && symbol === ',') {
	            symbol = currentFormat['DEC_POINT'];
	          }

	          if (currentFormat['DEC_POINT'] === ',' && symbol === '.') {
	            symbol = currentFormat['DEC_POINT'];
	          }

	          if (isDigit || symbolPosition === decPointPosition && symbol === currentFormat['DEC_POINT']) {
	            formatValue = symbol + formatValue;
	          } else if (valueLength > symbolPosition) {
	            valueLength--;
	          }
	        } else {
	          if (isDigit) {
	            formatValue = symbol + formatValue;
	          } else if (valueLength > symbolPosition) {
	            valueLength--;
	          }

	          if (isDigit && countDigit % 3 === 0 && countDigit !== 0 && symbolPosition !== 0) {
	            formatValue = currentFormat['SEPARATOR'] + formatValue;

	            if (valueLength >= symbolPosition) {
	              valueLength++;
	            }
	          }
	        }
	      }

	      decPointPosition = formatValue.match(new RegExp('[' + currentFormat['DEC_POINT'] + ']'));
	      decPointPosition = decPointPosition === null ? formatValue.length : decPointPosition.index;

	      if (currentFormat['DECIMALS'] > 0) {
	        while (formatValue.length - 1 - decPointPosition > currentFormat['DECIMALS']) {
	          if (valueLength >= formatValue.length - 1) {
	            valueLength--;
	          }

	          formatValue = formatValue.substr(0, formatValue.length - 1);
	        }
	      } else {
	        formatValue = formatValue.substr(0, decPointPosition);
	      }

	      return formatValue;
	    }
	  }]);
	  return MoneyEditor;
	}();
	/** @deprecated use import { MoneyEditor } from 'currency.money-editor' */

	babelHelpers.defineProperty(MoneyEditor, "currencyList", null);
	babelHelpers.defineProperty(MoneyEditor, "defaultFormat", {
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
	});
	main_core.Reflection.namespace('BX.Currency').Editor = MoneyEditor;

	exports.MoneyEditor = MoneyEditor;

}((this.BX.Currency = this.BX.Currency || {}),BX));
//# sourceMappingURL=money-editor.bundle.js.map
