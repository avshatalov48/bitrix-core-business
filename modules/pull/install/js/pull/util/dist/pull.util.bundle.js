/* eslint-disable */
this.BX = this.BX || {};
this.BX.Pull = this.BX.Pull || {};
(function (exports) {
	'use strict';

	/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */

	const browser = {
	  IsChrome() {
	    return navigator.userAgent.toLowerCase().includes('chrome');
	  },
	  IsFirefox() {
	    return navigator.userAgent.toLowerCase().includes('firefox');
	  },
	  IsIe() {
	    return navigator.userAgent.match(/(Trident\/|MSIE\/)/) !== null;
	  }
	};
	function getTimestamp() {
	  return Date.now();
	}

	/**
	 * Reduces errors array to single string.
	 * @param {array} errors
	 * @return {string}
	 */
	function errorsToString(errors) {
	  if (!isArray(errors)) {
	    return '';
	  }
	  return errors.reduce((accum, currentValue) => {
	    const result = accum === '' ? accum : `${accum}; `;
	    return `${result + currentValue.code}: ${currentValue.message}`;
	  }, '');
	}
	function isString(item) {
	  return item === '' ? true : item ? typeof item === 'string' || item instanceof String : false;
	}
	function isArray(item) {
	  return item && Object.prototype.toString.call(item) === '[object Array]';
	}
	function isFunction(item) {
	  return item === null ? false : typeof item === 'function' || item instanceof Function;
	}
	function getFunction(f) {
	  return isFunction(f) ? f : function () {};
	}
	function isDomNode(item) {
	  return item && typeof item === 'object' && 'nodeType' in item;
	}
	function isDate(item) {
	  return item && Object.prototype.toString.call(item) === '[object Date]';
	}
	function isNumber(item) {
	  return typeof item === 'number' && Number.isFinite(item);
	}
	function isObject(item) {
	  return Boolean(item) && typeof item === 'object';
	}
	function isPlainObject(item) {
	  return Boolean(item) && typeof item === 'object' && item.constructor === Object;
	}
	function isNotEmptyString(item) {
	  return isString(item) ? item.length > 0 : false;
	}
	function isJsonRpcRequest(item) {
	  return typeof item === 'object' && item && 'jsonrpc' in item && isNotEmptyString(item.jsonrpc) && 'method' in item && isNotEmptyString(item.method);
	}
	function isJsonRpcResponse(item) {
	  return typeof item === 'object' && item && 'jsonrpc' in item && isNotEmptyString(item.jsonrpc) && 'id' in item && ('result' in item || 'error' in item);
	}
	function buildQueryString(params) {
	  let result = '';
	  for (const key of Object.keys(params)) {
	    const value = params[key];
	    if (isArray(value)) {
	      for (const [index, valueElement] of value.entries()) {
	        const left = encodeURIComponent(`${key}[${index}]`);
	        const right = `${encodeURIComponent(valueElement)}&`;
	        result += `${left}=${right}`;
	      }
	    } else {
	      result += `${encodeURIComponent(key)}=${encodeURIComponent(value)}&`;
	    }
	  }
	  if (result.length > 0) {
	    result = result.slice(0, Math.max(0, result.length - 1));
	  }
	  return result;
	}
	function clone(obj, bCopyObj = true) {
	  let _obj, i, l;
	  if (obj === null) {
	    return null;
	  }
	  if (isDomNode(obj)) {
	    _obj = obj.cloneNode(bCopyObj);
	  } else if (typeof obj === 'object') {
	    if (isArray(obj)) {
	      _obj = [];
	      for (i = 0, l = obj.length; i < l; i++) {
	        if (typeof obj[i] === 'object' && bCopyObj) {
	          _obj[i] = clone(obj[i], bCopyObj);
	        } else {
	          _obj[i] = obj[i];
	        }
	      }
	    } else {
	      _obj = {};
	      if (obj.constructor) {
	        if (isDate(obj)) {
	          _obj = new Date(obj);
	        } else {
	          _obj = new obj.constructor();
	        }
	      }
	      for (i in obj) {
	        if (!obj.hasOwnProperty(i)) {
	          continue;
	        }
	        if (typeof obj[i] === 'object' && bCopyObj) {
	          _obj[i] = clone(obj[i], bCopyObj);
	        } else {
	          _obj[i] = obj[i];
	        }
	      }
	    }
	  } else {
	    _obj = obj;
	  }
	  return _obj;
	}
	function getDateForLog() {
	  const d = new Date();
	  return `${d.getFullYear()}-${lpad(d.getMonth(), 2, '0')}-${lpad(d.getDate(), 2, '0')} ${lpad(d.getHours(), 2, '0')}:${lpad(d.getMinutes(), 2, '0')}`;
	}
	function lpad(str, length, chr = ' ') {
	  if (str.length > length) {
	    return str;
	  }
	  let result = '';
	  for (let i = 0; i < length - result.length; i++) {
	    result += chr;
	  }
	  return result + str;
	}
	function isWebSocketSupported() {
	  return typeof 'WebSocket' !== 'undefined';
	}
	class CircularBuffer {
	  constructor(capacity) {
	    this.pointer = 0;
	    if (capacity <= 0) {
	      throw new Error('capacity must be > 0');
	    }
	    this.capacity = capacity;
	    this.storage = [];
	  }
	  push(element) {
	    this.storage[this.pointer] = element;
	    this.pointer++;
	    if (this.pointer >= this.capacity) {
	      this.pointer = 0;
	    }
	  }
	  getAll() {
	    if (this.pointer === 0) {
	      return this.storage;
	    }
	    return [...this.storage.slice(this.pointer), ...this.storage.slice(0, this.pointer)];
	  }
	}

	exports.browser = browser;
	exports.getTimestamp = getTimestamp;
	exports.errorsToString = errorsToString;
	exports.isString = isString;
	exports.isArray = isArray;
	exports.isFunction = isFunction;
	exports.getFunction = getFunction;
	exports.isDomNode = isDomNode;
	exports.isDate = isDate;
	exports.isNumber = isNumber;
	exports.isObject = isObject;
	exports.isPlainObject = isPlainObject;
	exports.isNotEmptyString = isNotEmptyString;
	exports.isJsonRpcRequest = isJsonRpcRequest;
	exports.isJsonRpcResponse = isJsonRpcResponse;
	exports.buildQueryString = buildQueryString;
	exports.clone = clone;
	exports.getDateForLog = getDateForLog;
	exports.lpad = lpad;
	exports.isWebSocketSupported = isWebSocketSupported;
	exports.CircularBuffer = CircularBuffer;

}((this.BX.Pull.Util = this.BX.Pull.Util || {})));
//# sourceMappingURL=pull.util.bundle.js.map
