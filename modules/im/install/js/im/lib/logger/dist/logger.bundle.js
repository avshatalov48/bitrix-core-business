/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _types = /*#__PURE__*/new WeakMap();
	var _config = /*#__PURE__*/new WeakMap();
	var _custom = /*#__PURE__*/new WeakMap();
	/**
	 * Bitrix Messenger
	 * Logger class
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var Logger = /*#__PURE__*/function () {
	  function Logger() {
	    babelHelpers.classCallCheck(this, Logger);
	    _classPrivateFieldInitSpec(this, _types, {
	      writable: true,
	      value: {}
	    });
	    _classPrivateFieldInitSpec(this, _config, {
	      writable: true,
	      value: {}
	    });
	    _classPrivateFieldInitSpec(this, _custom, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldSet(this, _types, {
	      desktop: true,
	      log: false,
	      info: false,
	      warn: false,
	      error: true,
	      trace: true
	    });
	    babelHelpers.classPrivateFieldSet(this, _config, babelHelpers.classPrivateFieldGet(this, _types));
	    this.__load();
	  }
	  babelHelpers.createClass(Logger, [{
	    key: "setConfig",
	    value: function setConfig(types) {
	      for (var type in types) {
	        if (types.hasOwnProperty(type) && typeof babelHelpers.classPrivateFieldGet(this, _types)[type] !== 'undefined') {
	          babelHelpers.classPrivateFieldGet(this, _types)[type] = !!types[type];
	          babelHelpers.classPrivateFieldGet(this, _config)[type] = !!types[type];
	        }
	      }
	      this.__load();
	    }
	  }, {
	    key: "enable",
	    value: function enable(type) {
	      if (typeof babelHelpers.classPrivateFieldGet(this, _types)[type] === 'undefined') {
	        return false;
	      }
	      babelHelpers.classPrivateFieldGet(this, _types)[type] = true;
	      babelHelpers.classPrivateFieldGet(this, _custom)[type] = true;
	      this.__save();
	      return true;
	    }
	  }, {
	    key: "disable",
	    value: function disable(type) {
	      if (typeof babelHelpers.classPrivateFieldGet(this, _types)[type] === 'undefined') {
	        return false;
	      }
	      babelHelpers.classPrivateFieldGet(this, _types)[type] = false;
	      babelHelpers.classPrivateFieldGet(this, _custom)[type] = false;
	      this.__save();
	      return true;
	    }
	  }, {
	    key: "isEnabled",
	    value: function isEnabled(type) {
	      return babelHelpers.classPrivateFieldGet(this, _types)[type] === true;
	    }
	  }, {
	    key: "desktop",
	    value: function desktop() {
	      if (this.isEnabled('desktop')) {
	        var _console;
	        for (var _len = arguments.length, params = new Array(_len), _key = 0; _key < _len; _key++) {
	          params[_key] = arguments[_key];
	        }
	        (_console = console).log.apply(_console, [].concat(babelHelpers.toConsumableArray(this.__getStyles('desktop')), params));
	      }
	    }
	  }, {
	    key: "log",
	    value: function log() {
	      if (this.isEnabled('log')) {
	        var _console2;
	        for (var _len2 = arguments.length, params = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	          params[_key2] = arguments[_key2];
	        }
	        (_console2 = console).log.apply(_console2, [].concat(babelHelpers.toConsumableArray(this.__getStyles('log')), params));
	      }
	    }
	  }, {
	    key: "info",
	    value: function info() {
	      if (this.isEnabled('info')) {
	        var _console3;
	        for (var _len3 = arguments.length, params = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	          params[_key3] = arguments[_key3];
	        }
	        (_console3 = console).info.apply(_console3, [].concat(babelHelpers.toConsumableArray(this.__getStyles('info')), params));
	      }
	    }
	  }, {
	    key: "warn",
	    value: function warn() {
	      if (this.isEnabled('warn')) {
	        var _console4;
	        for (var _len4 = arguments.length, params = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
	          params[_key4] = arguments[_key4];
	        }
	        (_console4 = console).warn.apply(_console4, [].concat(babelHelpers.toConsumableArray(this.__getStyles('warn')), params));
	      }
	    }
	  }, {
	    key: "error",
	    value: function error() {
	      if (this.isEnabled('error')) {
	        var _console5;
	        for (var _len5 = arguments.length, params = new Array(_len5), _key5 = 0; _key5 < _len5; _key5++) {
	          params[_key5] = arguments[_key5];
	        }
	        (_console5 = console).error.apply(_console5, [].concat(babelHelpers.toConsumableArray(this.__getStyles('error')), params));
	      }
	    }
	  }, {
	    key: "trace",
	    value: function trace() {
	      if (this.isEnabled('trace')) {
	        var _console6;
	        (_console6 = console).trace.apply(_console6, arguments);
	      }
	    }
	  }, {
	    key: "__save",
	    value: function __save() {
	      if (typeof window.localStorage !== 'undefined') {
	        try {
	          var custom = {};
	          for (var type in babelHelpers.classPrivateFieldGet(this, _custom)) {
	            if (babelHelpers.classPrivateFieldGet(this, _custom).hasOwnProperty(type) && babelHelpers.classPrivateFieldGet(this, _config)[type] !== babelHelpers.classPrivateFieldGet(this, _custom)[type]) {
	              custom[type] = !!babelHelpers.classPrivateFieldGet(this, _custom)[type];
	            }
	          }
	          console.warn(JSON.stringify(custom));
	          window.localStorage.setItem('bx-messenger-logger', JSON.stringify(custom));
	        } catch (e) {}
	      }
	    }
	  }, {
	    key: "__load",
	    value: function __load() {
	      if (typeof window.localStorage !== 'undefined') {
	        try {
	          var custom = window.localStorage.getItem('bx-messenger-logger');
	          if (typeof custom === 'string') {
	            babelHelpers.classPrivateFieldSet(this, _custom, JSON.parse(custom));
	            babelHelpers.classPrivateFieldSet(this, _types, _objectSpread(_objectSpread({}, babelHelpers.classPrivateFieldGet(this, _types)), babelHelpers.classPrivateFieldGet(this, _custom)));
	          }
	        } catch (e) {}
	      }
	    }
	  }, {
	    key: "__getStyles",
	    value: function __getStyles() {
	      var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'all';
	      var styles = {
	        'desktop': ["%cDESKTOP", "color: white; font-style: italic; background-color: #29619b; padding: 0 6\px"],
	        'log': ["%cLOG", "color: #2a323b; font-style: italic; background-color: #ccc; padding: 0 6\px"],
	        'info': ["%cINFO", "color: #fff; font-style: italic; background-color: #6b7f96; padding: 0 6\px"],
	        'warn': ["%cWARNING", "color: white; font-style: italic; padding: 0 6\px; border: 1px solid #f0a74f"],
	        'error': ["%cERROR", "color: white; font-style: italic; padding: 0 6\px; border: 1px solid #8a3232"]
	      };
	      if (type === 'all') {
	        return styles;
	      }
	      if (styles[type]) {
	        return styles[type];
	      }
	      return [];
	    }
	  }, {
	    key: "__getRemoveString",
	    value: function __getRemoveString() {
	      var styles = this.__getStyles();
	      var result = [];
	      for (var type in styles) {
	        if (styles.hasOwnProperty(type)) {
	          result.push(styles[type][1]);
	        }
	      }
	      return result;
	    }
	  }]);
	  return Logger;
	}();
	var logger = new Logger();

	exports.Logger = logger;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {})));
//# sourceMappingURL=logger.bundle.js.map
