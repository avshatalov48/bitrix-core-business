(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Logger class
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	if (!window.BX) {
	  window.BX = {};
	}

	if (typeof window.BX.Messenger == 'undefined') {
	  window.BX.Messenger = {};
	}

	if (typeof window.BX.Messenger.Const == 'undefined') {
	  window.BX.Messenger.Const = {};
	}

	if (typeof window.BX.Messenger.Utils == 'undefined') {
	  window.BX.Messenger.Utils = {};
	}

	BX.Messenger.Const.dateFormat = Object.freeze({
	  groupTitle: 'groupTitle',
	  message: 'message',
	  recentTitle: 'recentTitle',
	  recentLinesTitle: 'recentLinesTitle',
	  default: 'default'
	});
	BX.Messenger.Utils = {
	  browser: {
	    isSafari: function isSafari() {
	      if (!navigator.userAgent.toLowerCase().includes('safari')) {
	        return false;
	      }

	      return !this.isSafariBased();
	    },
	    isSafariBased: function isSafariBased() {
	      if (!navigator.userAgent.toLowerCase().includes('applewebkit')) {
	        return false;
	      }

	      return navigator.userAgent.toLowerCase().includes('yabrowser') || navigator.userAgent.toLowerCase().includes('yaapp_ios_browser') || navigator.userAgent.toLowerCase().includes('crios');
	    },
	    isChrome: function isChrome() {
	      return navigator.userAgent.toLowerCase().includes('chrome');
	    },
	    isFirefox: function isFirefox() {
	      return navigator.userAgent.toLowerCase().includes('firefox');
	    },
	    isIe: function isIe() {
	      return navigator.userAgent.match(/(Trident\/|MSIE\/)/) !== null;
	    }
	  },
	  platform: {
	    isMac: function isMac() {
	      return navigator.userAgent.toLowerCase().includes('macintosh');
	    },
	    isLinux: function isLinux() {
	      return navigator.userAgent.toLowerCase().includes('linux');
	    },
	    isWindows: function isWindows() {
	      return navigator.userAgent.toLowerCase().includes('windows') || !this.isMac() && !this.isLinux();
	    },
	    isBitrixMobile: function isBitrixMobile() {
	      return navigator.userAgent && navigator.userAgent.toLowerCase().includes('bitrixmobile');
	    },
	    isBitrixDesktop: function isBitrixDesktop() {
	      return navigator.userAgent.toLowerCase().includes('bitrixdesktop');
	    },
	    isMobile: function isMobile() {
	      return this.isAndroid() || this.isIos() || this.isBitrixMobile();
	    },
	    isIos: function isIos() {
	      return navigator.userAgent.toLowerCase().includes('iphone') || navigator.userAgent.toLowerCase().includes('ipad');
	    },
	    getIosVersion: function getIosVersion() {
	      if (!this.isIos()) {
	        return null;
	      }

	      var matches = navigator.userAgent.toLowerCase().match(/(iphone|ipad)(.+)(OS\s([0-9]+))/i);

	      if (!matches || !matches[4]) {
	        return null;
	      }

	      return matches[4];
	    },
	    isAndroid: function isAndroid() {
	      return navigator.userAgent.toLowerCase().includes('android');
	    }
	  },
	  device: {
	    isDesktop: function isDesktop() {
	      return !this.isMobile();
	    },
	    isMobile: function isMobile() {
	      if (typeof this.isMobileStatic !== 'undefined') {
	        return this.isMobileStatic;
	      }

	      this.isMobileStatic = navigator.userAgent.toLowerCase().includes('android') || navigator.userAgent.toLowerCase().includes('webos') || navigator.userAgent.toLowerCase().includes('iphone') || navigator.userAgent.toLowerCase().includes('ipad') || navigator.userAgent.toLowerCase().includes('ipod') || navigator.userAgent.toLowerCase().includes('blackberry') || navigator.userAgent.toLowerCase().includes('windows phone');
	      return this.isMobileStatic;
	    },
	    orientationHorizontal: 'horizontal',
	    orientationPortrait: 'portrait',
	    getOrientation: function getOrientation() {
	      if (!this.isMobile()) {
	        return this.orientationHorizontal;
	      }

	      return Math.abs(window.orientation) === 0 ? this.orientationPortrait : this.orientationHorizontal;
	    }
	  },
	  types: {
	    isString: function isString(item) {
	      return item === '' ? true : item ? typeof item == "string" || item instanceof String : false;
	    },
	    isArray: function isArray(item) {
	      return item && Object.prototype.toString.call(item) == "[object Array]";
	    },
	    isFunction: function isFunction(item) {
	      return item === null ? false : typeof item == "function" || item instanceof Function;
	    },
	    isDomNode: function isDomNode(item) {
	      return item && babelHelpers.typeof(item) == "object" && "nodeType" in item;
	    },
	    isDate: function isDate(item) {
	      return item && Object.prototype.toString.call(item) == "[object Date]";
	    },
	    isPlainObject: function isPlainObject(item) {
	      if (!item || babelHelpers.typeof(item) !== "object" || item.nodeType) {
	        return false;
	      }

	      var hasProp = Object.prototype.hasOwnProperty;

	      try {
	        if (item.constructor && !hasProp.call(item, "constructor") && !hasProp.call(item.constructor.prototype, "isPrototypeOf")) {
	          return false;
	        }
	      } catch (e) {
	        return false;
	      }

	      var key;

	      return typeof key === "undefined" || hasProp.call(item, key);
	    }
	  },
	  isDarkColor: function isDarkColor(hex) {
	    if (!hex || !hex.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
	      return false;
	    }

	    if (hex.length === 4) {
	      hex = hex.replace(/#([A-Fa-f0-9])/gi, "$1$1");
	    } else {
	      hex = hex.replace(/#([A-Fa-f0-9])/gi, "$1");
	    }

	    hex = hex.toLowerCase();
	    var darkColor = ["#17a3ea", "#00aeef", "#00c4fb", "#47d1e2", "#75d900", "#ffab00", "#ff5752", "#468ee5", "#1eae43"];

	    if (darkColor.includes('#' + hex)) {
	      return true;
	    }

	    var bigint = parseInt(hex, 16);
	    var red = bigint >> 16 & 255;
	    var green = bigint >> 8 & 255;
	    var blue = bigint & 255;
	    var brightness = (red * 299 + green * 587 + blue * 114) / 1000;
	    return brightness < 128;
	  },
	  getDateFormatType: function getDateFormatType() {
	    var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : BX.Messenger.Const.dateFormat.default;
	    var localize = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	    if (!localize) {
	      localize = BX.message;
	    }

	    var format = [];

	    if (type === BX.Messenger.Const.dateFormat.groupTitle) {
	      format = [["tommorow", "tommorow"], ["today", "today"], ["yesterday", "yesterday"], ["", BX.Main.Date.convertBitrixFormat(localize["IM_UTILS_FORMAT_DATE"])]];
	    } else if (type === BX.Messenger.Const.dateFormat.message) {
	      format = [["", localize["IM_UTILS_FORMAT_TIME"]]];
	    } else if (type === BX.Messenger.Const.dateFormat.recentTitle) {
	      format = [["tommorow", "today"], ["today", "today"], ["yesterday", "yesterday"], ["", BX.Main.Date.convertBitrixFormat(localize["IM_UTILS_FORMAT_DATE_RECENT"])]];
	    } else if (type === BX.Messenger.Const.dateFormat.recentLinesTitle) {
	      format = [["tommorow", "tommorow"], ["today", "today"], ["yesterday", "yesterday"], ["", BX.Main.Date.convertBitrixFormat(localize["IM_UTILS_FORMAT_DATE_RECENT"])]];
	    } else {
	      format = [["tommorow", "tommorow, " + localize["IM_UTILS_FORMAT_TIME"]], ["today", "today, " + localize["IM_UTILS_FORMAT_TIME"]], ["yesterday", "yesterday, " + localize["IM_UTILS_FORMAT_TIME"]], ["", BX.Main.Date.convertBitrixFormat(localize["FORMAT_DATETIME"])]];
	    }

	    return format;
	  },
	  hashCode: function hashCode() {
	    var string = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	    var hash = 0;

	    if (babelHelpers.typeof(string) === 'object' && string) {
	      string = JSON.stringify(string);
	    } else if (typeof string !== 'string') {
	      string = string.toString();
	    }

	    if (typeof string !== 'string') {
	      return hash;
	    }

	    for (var i = 0; i < string.length; i++) {
	      var char = string.charCodeAt(i);
	      hash = (hash << 5) - hash + char;
	      hash = hash & hash;
	    }

	    return hash;
	  },

	  /**
	   * The method compares versions, and returns - 0 if they are the same, 1 if version1 is greater, -1 if version1 is less
	   *
	   * @param version1
	   * @param version2
	   * @returns {number|NaN}
	   */
	  versionCompare: function versionCompare(version1, version2) {
	    var isNumberRegExp = /^([\d+\.]+)$/;

	    if (!isNumberRegExp.test(version1) || !isNumberRegExp.test(version2)) {
	      return NaN;
	    }

	    version1 = version1.toString().split('.');
	    version2 = version2.toString().split('.');

	    if (version1.length < version2.length) {
	      while (version1.length < version2.length) {
	        version1.push(0);
	      }
	    } else if (version2.length < version1.length) {
	      while (version2.length < version1.length) {
	        version2.push(0);
	      }
	    }

	    for (var i = 0; i < version1.length; i++) {
	      if (version1[i] > version2[i]) {
	        return 1;
	      } else if (version1[i] < version2[i]) {
	        return -1;
	      }
	    }

	    return 0;
	  },

	  /**
	   * Throttle function. Callback will be executed no more than 'wait' period (in ms).
	   *
	   * @param callback
	   * @param wait
	   * @param context
	   * @returns {Function}
	   */
	  throttle: function throttle(callback, wait) {
	    var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : this;
	    var timeout = null;
	    var callbackArgs = null;

	    var nextCallback = function nextCallback() {
	      callback.apply(context, callbackArgs);
	      timeout = null;
	    };

	    return function () {
	      if (!timeout) {
	        callbackArgs = arguments;
	        timeout = setTimeout(nextCallback, wait);
	      }
	    };
	  },

	  /**
	   * Debounce function. Callback will be executed if it hast been called for longer than 'wait' period (in ms).
	   *
	   * @param callback
	   * @param wait
	   * @param context
	   * @returns {Function}
	   */
	  debounce: function debounce(callback, wait) {
	    var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : this;
	    var timeout = null;
	    var callbackArgs = null;

	    var nextCallback = function nextCallback() {
	      callback.apply(context, callbackArgs);
	    };

	    return function () {
	      callbackArgs = arguments;
	      clearTimeout(timeout);
	      timeout = setTimeout(nextCallback, wait);
	    };
	  },
	  htmlspecialchars: function htmlspecialchars(string) {
	    if (typeof string !== 'string') {
	      return string;
	    }

	    return string.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	  },
	  htmlspecialcharsback: function htmlspecialcharsback(string) {
	    if (typeof string !== 'string') {
	      return string;
	    }

	    return string.replace(/\&quot;/g, '"').replace(/&#39;/g, "'").replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&amp;/g, '&').replace(/\&nbsp;/g, ' ');
	  },
	  getLogTrackingParams: function getLogTrackingParams() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var result = [];
	    var _params$name = params.name,
	        name = _params$name === void 0 ? 'tracking' : _params$name,
	        _params$data = params.data,
	        data = _params$data === void 0 ? [] : _params$data,
	        _params$dialog = params.dialog,
	        dialog = _params$dialog === void 0 ? null : _params$dialog,
	        _params$message = params.message,
	        message = _params$message === void 0 ? null : _params$message,
	        _params$files = params.files,
	        files = _params$files === void 0 ? null : _params$files;
	    name = encodeURIComponent(name);

	    if (data && !(data instanceof Array) && babelHelpers.typeof(data) === 'object') {
	      var dataArray = [];

	      for (var _name in data) {
	        if (data.hasOwnProperty(_name)) {
	          dataArray.push(encodeURIComponent(_name) + "=" + encodeURIComponent(data[_name]));
	        }
	      }

	      data = dataArray;
	    } else if (!data instanceof Array) {
	      data = [];
	    }

	    if (dialog) {
	      result.push('timType=' + dialog.type);

	      if (dialog.type === 'lines') {
	        result.push('timLinesType=' + dialog.entityId.split('|')[0]);
	      }
	    }

	    if (files) {
	      var type = 'file';

	      if (files instanceof Array && files[0]) {
	        type = files[0].type;
	      } else {
	        type = files.type;
	      }

	      result.push('timMessageType=' + type);
	    } else if (message) {
	      result.push('timMessageType=text');
	    }

	    if (this.platform.isBitrixMobile()) {
	      result.push('timDevice=bitrixMobile');
	    } else if (this.platform.isBitrixDesktop()) {
	      result.push('timDevice=bitrixDesktop');
	    } else if (this.platform.isIos() || this.platform.isAndroid()) {
	      result.push('timDevice=mobile');
	    } else {
	      result.push('timDevice=web');
	    }

	    return name + (data.length ? '&' + data.join('&') : '') + (result.length ? '&' + result.join('&') : '');
	  }
	};

}((this.window = this.window || {})));
//# sourceMappingURL=messenger.utils.bundle.js.map
