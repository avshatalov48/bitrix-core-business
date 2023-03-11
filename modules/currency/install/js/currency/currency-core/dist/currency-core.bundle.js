this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _currency = /*#__PURE__*/new WeakMap();

	var _format = /*#__PURE__*/new WeakMap();

	var CurrencyItem = /*#__PURE__*/function () {
	  function CurrencyItem(currency, format) {
	    babelHelpers.classCallCheck(this, CurrencyItem);

	    _classPrivateFieldInitSpec(this, _currency, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _format, {
	      writable: true,
	      value: {}
	    });

	    babelHelpers.classPrivateFieldSet(this, _currency, currency);
	    babelHelpers.classPrivateFieldSet(this, _format, format);
	  }

	  babelHelpers.createClass(CurrencyItem, [{
	    key: "getCurrency",
	    value: function getCurrency() {
	      return babelHelpers.classPrivateFieldGet(this, _currency);
	    }
	  }, {
	    key: "getFormat",
	    value: function getFormat() {
	      return babelHelpers.classPrivateFieldGet(this, _format);
	    }
	  }, {
	    key: "setFormat",
	    value: function setFormat(format) {
	      babelHelpers.classPrivateFieldSet(this, _format, format);
	    }
	  }]);
	  return CurrencyItem;
	}();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var CurrencyCore = /*#__PURE__*/function () {
	  function CurrencyCore() {
	    babelHelpers.classCallCheck(this, CurrencyCore);
	  }

	  babelHelpers.createClass(CurrencyCore, null, [{
	    key: "getCurrencyList",
	    value: function getCurrencyList() {
	      return this.currencies;
	    }
	  }, {
	    key: "setCurrencyFormat",
	    value: function setCurrencyFormat(currency, format, replace) {
	      if (!main_core.Type.isStringFilled(currency) || !main_core.Type.isPlainObject(format)) {
	        return;
	      }

	      var index = this.getCurrencyIndex(currency);

	      if (index > -1 && !replace) {
	        return;
	      }

	      var innerFormat = _objectSpread(_objectSpread({}, this.defaultFormat), format);

	      if (index === -1) {
	        this.currencies.push(new CurrencyItem(currency, innerFormat));
	      } else {
	        this.currencies[index].setFormat(innerFormat);
	      }
	    }
	  }, {
	    key: "setCurrencies",
	    value: function setCurrencies(currencies, replace) {
	      if (main_core.Type.isArray(currencies)) {
	        for (var i = 0; i < currencies.length; i++) {
	          if (!main_core.Type.isPlainObject(currencies[i]) || !main_core.Type.isStringFilled(currencies[i].CURRENCY) || !main_core.Type.isPlainObject(currencies[i].FORMAT)) {
	            continue;
	          }

	          this.setCurrencyFormat(currencies[i].CURRENCY, currencies[i].FORMAT, replace);
	        }
	      }
	    }
	  }, {
	    key: "getCurrencyFormat",
	    value: function getCurrencyFormat(currency) {
	      var index = this.getCurrencyIndex(currency);
	      return index > -1 ? this.getCurrencyList()[index].getFormat() : false;
	    }
	  }, {
	    key: "getCurrencyIndex",
	    value: function getCurrencyIndex(currency) {
	      var currencyList = this.getCurrencyList();

	      for (var i = 0; i < currencyList.length; i++) {
	        if (currencyList[i].getCurrency() === currency) {
	          return i;
	        }
	      }

	      return -1;
	    }
	  }, {
	    key: "clearCurrency",
	    value: function clearCurrency(currency) {
	      var index = this.getCurrencyIndex(currency);

	      if (index > -1) {
	        this.currencies = BX.util.deleteFromArray(this.currencies, index);
	      }
	    }
	  }, {
	    key: "clean",
	    value: function clean() {
	      this.currencies = [];
	    }
	  }, {
	    key: "currencyFormat",
	    value: function currencyFormat(price, currency, useTemplate) {
	      var result = '';
	      var format = this.getCurrencyFormat(currency);

	      if (main_core.Type.isObject(format)) {
	        format.CURRENT_DECIMALS = format.DECIMALS;

	        if (format.HIDE_ZERO === 'Y' && price == parseInt(price, 10)) {
	          format.CURRENT_DECIMALS = 0;
	        }

	        result = BX.util.number_format(price, format.CURRENT_DECIMALS, format.DEC_POINT, format.THOUSANDS_SEP);

	        if (useTemplate) {
	          result = format.FORMAT_STRING.replace(/(^|[^&])#/, '$1' + result);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getPriceControl",
	    value: function getPriceControl(control, currency) {
	      var result = '';
	      var format = this.getCurrencyFormat(currency);

	      if (main_core.Type.isObject(format)) {
	        result = format.FORMAT_STRING.replace(/(^|[^&])#/, '$1' + control.outerHTML);
	      }

	      return result;
	    }
	  }, {
	    key: "loadCurrencyFormat",
	    value: function loadCurrencyFormat(currency) {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        var index = _this.getCurrencyIndex(currency);

	        if (index > -1) {
	          resolve(_this.getCurrencyList()[index].getFormat());
	        } else {
	          BX.ajax.runAction("currency.format.get", {
	            data: {
	              currencyId: currency
	            }
	          }).then(function (response) {
	            var format = response.data;

	            _this.setCurrencyFormat(currency, format);

	            resolve(format);
	          })["catch"](function (response) {
	            reject(response.errors);
	          });
	        }
	      });
	    }
	  }]);
	  return CurrencyCore;
	}();
	/** @deprecated use import { CurrencyCore } from 'currency.core' */

	babelHelpers.defineProperty(CurrencyCore, "currencies", []);
	babelHelpers.defineProperty(CurrencyCore, "defaultFormat", {
	  'FORMAT_STRING': '#',
	  'DEC_POINT': '.',
	  'THOUSANDS_SEP': ' ',
	  'DECIMALS': 2,
	  'HIDE_ZERO': 'N'
	});
	main_core.Reflection.namespace('BX.Currency').Core = CurrencyCore;

	exports.CurrencyCore = CurrencyCore;

}((this.BX.Currency = this.BX.Currency || {}),BX));
//# sourceMappingURL=currency-core.bundle.js.map
