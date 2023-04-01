this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Logger class
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var _types = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("types");
	var _config = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("config");
	var _custom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("custom");
	var _localStorageKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localStorageKey");
	var _save = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("save");
	var _load = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("load");
	var _getStyles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStyles");
	var _getRemoveString = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRemoveString");
	class Logger {
	  constructor() {
	    Object.defineProperty(this, _load, {
	      value: _load2
	    });
	    Object.defineProperty(this, _save, {
	      value: _save2
	    });
	    Object.defineProperty(this, _types, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _config, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _custom, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _localStorageKey, {
	      writable: true,
	      value: 'bx-messenger-logger'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _types)[_types] = {
	      desktop: true,
	      log: false,
	      info: false,
	      warn: false,
	      error: true,
	      trace: true
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config] = babelHelpers.classPrivateFieldLooseBase(this, _types)[_types];
	    babelHelpers.classPrivateFieldLooseBase(this, _load)[_load]();
	  }
	  setConfig(types) {
	    Object.entries(types).forEach(([type, value]) => {
	      if (!main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _types)[_types][type])) {
	        babelHelpers.classPrivateFieldLooseBase(this, _types)[_types][type] = !!value;
	        babelHelpers.classPrivateFieldLooseBase(this, _config)[_config][type] = !!value;
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _load)[_load]();
	  }
	  enable(type) {
	    if (main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _types)[_types][type])) {
	      return false;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _types)[_types][type] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _custom)[_custom][type] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _save)[_save]();
	    return true;
	  }
	  disable(type) {
	    if (main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _types)[_types][type])) {
	      return false;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _types)[_types][type] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _custom)[_custom][type] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _save)[_save]();
	    return true;
	  }
	  isEnabled(type) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _types)[_types][type] === true;
	  }
	  desktop(...params) {
	    if (!this.isEnabled('desktop')) {
	      return false;
	    }
	    console.log(...babelHelpers.classPrivateFieldLooseBase(Logger, _getStyles)[_getStyles]('desktop'), ...params);
	  }
	  log(...params) {
	    if (!this.isEnabled('log')) {
	      return false;
	    }
	    console.log(...babelHelpers.classPrivateFieldLooseBase(Logger, _getStyles)[_getStyles]('log'), ...params);
	  }
	  info(...params) {
	    if (!this.isEnabled('info')) {
	      return false;
	    }
	    console.info(...babelHelpers.classPrivateFieldLooseBase(Logger, _getStyles)[_getStyles]('info'), ...params);
	  }
	  warn(...params) {
	    if (!this.isEnabled('warn')) {
	      return false;
	    }
	    console.warn(...babelHelpers.classPrivateFieldLooseBase(Logger, _getStyles)[_getStyles]('warn'), ...params);
	  }
	  error(...params) {
	    if (!this.isEnabled('error')) {
	      return false;
	    }
	    console.error(...babelHelpers.classPrivateFieldLooseBase(Logger, _getStyles)[_getStyles]('error'), ...params);
	  }
	  trace(...params) {
	    if (!this.isEnabled('trace')) {
	      return false;
	    }
	    console.trace(...params);
	  }
	}
	function _save2() {
	  if (main_core.Type.isUndefined(window.localStorage)) {
	    return false;
	  }
	  try {
	    const custom = {};
	    Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _custom)[_custom]).forEach(([type, value]) => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _config)[_config][type] !== babelHelpers.classPrivateFieldLooseBase(this, _custom)[_custom][type]) {
	        custom[type] = !!value;
	      }
	    });
	    console.warn('Logger: saving custom types', JSON.stringify(custom));
	    window.localStorage.setItem(babelHelpers.classPrivateFieldLooseBase(this, _localStorageKey)[_localStorageKey], JSON.stringify(custom));
	  } catch (error) {
	    console.error('Logger: save error', error);
	  }
	}
	function _load2() {
	  if (main_core.Type.isUndefined(window.localStorage)) {
	    return false;
	  }
	  try {
	    const custom = window.localStorage.getItem(babelHelpers.classPrivateFieldLooseBase(this, _localStorageKey)[_localStorageKey]);
	    if (main_core.Type.isString(custom)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _custom)[_custom] = JSON.parse(custom);
	      babelHelpers.classPrivateFieldLooseBase(this, _types)[_types] = {
	        ...babelHelpers.classPrivateFieldLooseBase(this, _types)[_types],
	        ...babelHelpers.classPrivateFieldLooseBase(this, _custom)[_custom]
	      };
	    }
	  } catch (error) {
	    console.error('Logger: load error', error);
	  }
	}
	function _getStyles2(type = 'all') {
	  const styles = {
	    'desktop': ["%cDESKTOP", "color: white; font-style: italic; background-color: #29619b; padding: 0 6px"],
	    'log': ["%cLOG", "color: #2a323b; font-style: italic; background-color: #ccc; padding: 0 6px"],
	    'info': ["%cINFO", "color: #fff; font-style: italic; background-color: #6b7f96; padding: 0 6px"],
	    'warn': ["%cWARNING", "color: white; font-style: italic; padding: 0 6px; border: 1px solid #f0a74f"],
	    'error': ["%cERROR", "color: white; font-style: italic; padding: 0 6px; border: 1px solid #8a3232"]
	  };
	  if (type === 'all') {
	    return styles;
	  }
	  if (styles[type]) {
	    return styles[type];
	  }
	  return [];
	}
	function _getRemoveString2() {
	  const styles = babelHelpers.classPrivateFieldLooseBase(Logger, _getStyles)[_getStyles]();
	  const result = [];
	  Object.entries(styles).forEach(([, style]) => {
	    result.push(style[1]);
	  });
	  return result;
	}
	Object.defineProperty(Logger, _getRemoveString, {
	  value: _getRemoveString2
	});
	Object.defineProperty(Logger, _getStyles, {
	  value: _getStyles2
	});
	const logger = new Logger();

	exports.Logger = logger;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX));
//# sourceMappingURL=logger.bundle.js.map
