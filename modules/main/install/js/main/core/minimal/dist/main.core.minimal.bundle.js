(function (exports) {
	'use strict';

	/**
	 * Gets object.toString result
	 * @param value
	 * @return {string}
	 */
	function getTag(value) {
	  return Object.prototype.toString.call(value);
	}

	var objectCtorString = Function.prototype.toString.call(Object);
	/**
	 * @memberOf BX
	 */

	var Type =
	/*#__PURE__*/
	function () {
	  function Type() {
	    babelHelpers.classCallCheck(this, Type);
	  }

	  babelHelpers.createClass(Type, null, [{
	    key: "isString",

	    /**
	     * Checks that value is string
	     * @param value
	     * @return {boolean}
	     */
	    value: function isString(value) {
	      return typeof value === 'string';
	    }
	    /**
	     * Returns true if a value is not empty string
	     * @param value
	     * @returns {boolean}
	     */

	  }, {
	    key: "isStringFilled",
	    value: function isStringFilled(value) {
	      return this.isString(value) && value !== '';
	    }
	    /**
	     * Checks that value is function
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isFunction",
	    value: function isFunction(value) {
	      return typeof value === 'function';
	    }
	    /**
	     * Checks that value is object
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isObject",
	    value: function isObject(value) {
	      return !!value && (babelHelpers.typeof(value) === 'object' || typeof value === 'function');
	    }
	    /**
	     * Checks that value is object like
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isObjectLike",
	    value: function isObjectLike(value) {
	      return !!value && babelHelpers.typeof(value) === 'object';
	    }
	    /**
	     * Checks that value is plain object
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isPlainObject",
	    value: function isPlainObject(value) {
	      if (!Type.isObjectLike(value) || getTag(value) !== '[object Object]') {
	        return false;
	      }

	      var proto = Object.getPrototypeOf(value);

	      if (proto === null) {
	        return true;
	      }

	      var ctor = proto.hasOwnProperty('constructor') && proto.constructor;
	      return typeof ctor === 'function' && Function.prototype.toString.call(ctor) === objectCtorString;
	    }
	    /**
	     * Checks that value is boolean
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isBoolean",
	    value: function isBoolean(value) {
	      return value === true || value === false;
	    }
	    /**
	     * Checks that value is number
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isNumber",
	    value: function isNumber(value) {
	      return !Number.isNaN(value) && typeof value === 'number';
	    }
	    /**
	     * Checks that value is integer
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isInteger",
	    value: function isInteger(value) {
	      return Type.isNumber(value) && value % 1 === 0;
	    }
	    /**
	     * Checks that value is float
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isFloat",
	    value: function isFloat(value) {
	      return Type.isNumber(value) && !Type.isInteger(value);
	    }
	    /**
	     * Checks that value is nil
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isNil",
	    value: function isNil(value) {
	      return value === null || value === undefined;
	    }
	    /**
	     * Checks that value is array
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isArray",
	    value: function isArray(value) {
	      return !Type.isNil(value) && Array.isArray(value);
	    }
	    /**
	     * Returns true if a value is an array and it has at least one element
	     * @param value
	     * @returns {boolean}
	     */

	  }, {
	    key: "isArrayFilled",
	    value: function isArrayFilled(value) {
	      return this.isArray(value) && value.length > 0;
	    }
	    /**
	     * Checks that value is array like
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isArrayLike",
	    value: function isArrayLike(value) {
	      return !Type.isNil(value) && !Type.isFunction(value) && value.length > -1 && value.length <= Number.MAX_SAFE_INTEGER;
	    }
	    /**
	     * Checks that value is Date
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isDate",
	    value: function isDate(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object Date]';
	    }
	    /**
	     * Checks that is DOM node
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isDomNode",
	    value: function isDomNode(value) {
	      return Type.isObjectLike(value) && !Type.isPlainObject(value) && 'nodeType' in value;
	    }
	    /**
	     * Checks that value is element node
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isElementNode",
	    value: function isElementNode(value) {
	      return Type.isDomNode(value) && value.nodeType === Node.ELEMENT_NODE;
	    }
	    /**
	     * Checks that value is text node
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isTextNode",
	    value: function isTextNode(value) {
	      return Type.isDomNode(value) && value.nodeType === Node.TEXT_NODE;
	    }
	    /**
	     * Checks that value is Map
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isMap",
	    value: function isMap(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object Map]';
	    }
	    /**
	     * Checks that value is Set
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isSet",
	    value: function isSet(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object Set]';
	    }
	    /**
	     * Checks that value is WeakMap
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isWeakMap",
	    value: function isWeakMap(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object WeakMap]';
	    }
	    /**
	     * Checks that value is WeakSet
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isWeakSet",
	    value: function isWeakSet(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object WeakSet]';
	    }
	    /**
	     * Checks that value is prototype
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isPrototype",
	    value: function isPrototype(value) {
	      return (typeof (value && value.constructor) === 'function' && value.constructor.prototype || Object.prototype) === value;
	    }
	    /**
	     * Checks that value is regexp
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isRegExp",
	    value: function isRegExp(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object RegExp]';
	    }
	    /**
	     * Checks that value is null
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isNull",
	    value: function isNull(value) {
	      return value === null;
	    }
	    /**
	     * Checks that value is undefined
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isUndefined",
	    value: function isUndefined(value) {
	      return typeof value === 'undefined';
	    }
	    /**
	     * Checks that value is ArrayBuffer
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isArrayBuffer",
	    value: function isArrayBuffer(value) {
	      return Type.isObjectLike(value) && getTag(value) === '[object ArrayBuffer]';
	    }
	    /**
	     * Checks that value is typed array
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isTypedArray",
	    value: function isTypedArray(value) {
	      var regExpTypedTag = /^\[object (?:Float(?:32|64)|(?:Int|Uint)(?:8|16|32)|Uint8Clamped)]$/;
	      return Type.isObjectLike(value) && regExpTypedTag.test(getTag(value));
	    }
	    /**
	     * Checks that value is Blob
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isBlob",
	    value: function isBlob(value) {
	      return Type.isObjectLike(value) && Type.isNumber(value.size) && Type.isString(value.type) && Type.isFunction(value.slice);
	    }
	    /**
	     * Checks that value is File
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isFile",
	    value: function isFile(value) {
	      return Type.isBlob(value) && Type.isObjectLike(value.lastModifiedDate) && Type.isNumber(value.lastModified) && Type.isString(value.name);
	    }
	    /**
	     * Checks that value is FormData
	     * @param value
	     * @return {boolean}
	     */

	  }, {
	    key: "isFormData",
	    value: function isFormData(value) {
	      return value instanceof FormData;
	    }
	  }]);
	  return Type;
	}();

	/**
	 * @memberOf BX
	 */

	var Reflection =
	/*#__PURE__*/
	function () {
	  function Reflection() {
	    babelHelpers.classCallCheck(this, Reflection);
	  }

	  babelHelpers.createClass(Reflection, null, [{
	    key: "getClass",

	    /**
	     * Gets link to function by function name
	     * @param className
	     * @return {?Function}
	     */
	    value: function getClass(className) {
	      if (Type.isString(className) && !!className) {
	        var classFn = null;
	        var currentNamespace = window;
	        var namespaces = className.split('.');

	        for (var i = 0; i < namespaces.length; i += 1) {
	          var namespace = namespaces[i];

	          if (!currentNamespace[namespace]) {
	            return null;
	          }

	          currentNamespace = currentNamespace[namespace];
	          classFn = currentNamespace;
	        }

	        return classFn;
	      }

	      if (Type.isFunction(className)) {
	        return className;
	      }

	      return null;
	    }
	    /**
	     * Creates a namespace or returns a link to a previously created one
	     * @param {String} namespaceName
	     * @return {Object<string, any> | Function | null}
	     */

	  }, {
	    key: "namespace",
	    value: function namespace(namespaceName) {
	      var parts = namespaceName.split('.');
	      var parent = window.BX;

	      if (parts[0] === 'BX') {
	        parts = parts.slice(1);
	      }

	      for (var i = 0; i < parts.length; i += 1) {
	        if (Type.isUndefined(parent[parts[i]])) {
	          parent[parts[i]] = {};
	        }

	        parent = parent[parts[i]];
	      }

	      return parent;
	    }
	  }]);
	  return Reflection;
	}();

	var reEscape = /[&<>'"]/g;
	var reUnescape = /&(?:amp|#38|lt|#60|gt|#62|apos|#39|quot|#34);/g;
	var escapeEntities = {
	  '&': '&amp;',
	  '<': '&lt;',
	  '>': '&gt;',
	  "'": '&#39;',
	  '"': '&quot;'
	};
	var unescapeEntities = {
	  '&amp;': '&',
	  '&#38;': '&',
	  '&lt;': '<',
	  '&#60;': '<',
	  '&gt;': '>',
	  '&#62;': '>',
	  '&apos;': "'",
	  '&#39;': "'",
	  '&quot;': '"',
	  '&#34;': '"'
	};
	/**
	 * @memberOf BX
	 */

	var Text =
	/*#__PURE__*/
	function () {
	  function Text() {
	    babelHelpers.classCallCheck(this, Text);
	  }

	  babelHelpers.createClass(Text, null, [{
	    key: "encode",

	    /**
	     * Encodes all unsafe entities
	     * @param {string} value
	     * @return {string}
	     */
	    value: function encode(value) {
	      if (Type.isString(value)) {
	        return value.replace(reEscape, function (item) {
	          return escapeEntities[item];
	        });
	      }

	      return value;
	    }
	    /**
	     * Decodes all encoded entities
	     * @param {string} value
	     * @return {string}
	     */

	  }, {
	    key: "decode",
	    value: function decode(value) {
	      if (Type.isString(value)) {
	        return value.replace(reUnescape, function (item) {
	          return unescapeEntities[item];
	        });
	      }

	      return value;
	    }
	  }, {
	    key: "getRandom",
	    value: function getRandom() {
	      var length = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 8;
	      // eslint-disable-next-line
	      return babelHelpers.toConsumableArray(Array(length)).map(function () {
	        return (~~(Math.random() * 36)).toString(36);
	      }).join('');
	    }
	  }, {
	    key: "toNumber",
	    value: function toNumber(value) {
	      var parsedValue = Number.parseFloat(value);

	      if (Type.isNumber(parsedValue)) {
	        return parsedValue;
	      }

	      return 0;
	    }
	  }, {
	    key: "toInteger",
	    value: function toInteger(value) {
	      return Text.toNumber(Number.parseInt(value, 10));
	    }
	  }, {
	    key: "toBoolean",
	    value: function toBoolean(value) {
	      var trueValues = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	      var transformedValue = Type.isString(value) ? value.toLowerCase() : value;
	      return ['true', 'y', '1', 1, true].concat(babelHelpers.toConsumableArray(trueValues)).includes(transformedValue);
	    }
	  }, {
	    key: "toCamelCase",
	    value: function toCamelCase(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }

	      var regex = /[-_\s]+(.)?/g;

	      if (!regex.test(str)) {
	        return str.match(/^[A-Z]+$/) ? str.toLowerCase() : str[0].toLowerCase() + str.slice(1);
	      }

	      str = str.toLowerCase();
	      str = str.replace(regex, function (match, letter) {
	        return letter ? letter.toUpperCase() : '';
	      });
	      return str[0].toLowerCase() + str.substr(1);
	    }
	  }, {
	    key: "toPascalCase",
	    value: function toPascalCase(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }

	      return this.capitalize(this.toCamelCase(str));
	    }
	  }, {
	    key: "toKebabCase",
	    value: function toKebabCase(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }

	      var matches = str.match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g);

	      if (!matches) {
	        return str;
	      }

	      return matches.map(function (x) {
	        return x.toLowerCase();
	      }).join('-');
	    }
	  }, {
	    key: "capitalize",
	    value: function capitalize(str) {
	      if (!Type.isStringFilled(str)) {
	        return str;
	      }

	      return str[0].toUpperCase() + str.substr(1);
	    }
	  }]);
	  return Text;
	}();

	var aliases = {
	  mousewheel: ['DOMMouseScroll'],
	  bxchange: ['change', 'cut', 'paste', 'drop', 'keyup'],
	  animationend: ['animationend', 'oAnimationEnd', 'webkitAnimationEnd', 'MSAnimationEnd'],
	  transitionend: ['webkitTransitionEnd', 'otransitionend', 'oTransitionEnd', 'msTransitionEnd', 'transitionend'],
	  fullscreenchange: ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'],
	  fullscreenerror: ['fullscreenerror', 'webkitfullscreenerror', 'mozfullscreenerror', 'MSFullscreenError']
	};

	var Registry =
	/*#__PURE__*/
	function () {
	  function Registry() {
	    babelHelpers.classCallCheck(this, Registry);
	    babelHelpers.defineProperty(this, "registry", new WeakMap());
	  }

	  babelHelpers.createClass(Registry, [{
	    key: "set",
	    value: function set(target, event, listener) {
	      var events = this.get(target);

	      if (!Type.isSet(events[event])) {
	        events[event] = new Set();
	      }

	      events[event].add(listener);
	      this.registry.set(target, events);
	    }
	  }, {
	    key: "get",
	    value: function get(target) {
	      return this.registry.get(target) || {};
	    }
	  }, {
	    key: "has",
	    value: function has(target, event, listener) {
	      if (event && listener) {
	        return this.registry.has(target) && this.registry.get(target)[event].has(listener);
	      }

	      return this.registry.has(target);
	    }
	  }, {
	    key: "delete",
	    value: function _delete(target, event, listener) {
	      if (!Type.isDomNode(target)) {
	        return;
	      }

	      if (Type.isString(event) && Type.isFunction(listener)) {
	        var events = this.registry.get(target);

	        if (Type.isPlainObject(events) && Type.isSet(events[event])) {
	          events[event].delete(listener);
	        }

	        return;
	      }

	      if (Type.isString(event)) {
	        var _events = this.registry.get(target);

	        if (Type.isPlainObject(_events) && Type.isSet(_events[event])) {
	          _events[event] = new Set();
	        }

	        return;
	      }

	      this.registry.delete(target);
	    }
	  }]);
	  return Registry;
	}();
	var registry = new Registry();

	function isOptionSupported(name) {
	  var isSupported = false;

	  try {
	    var options = Object.defineProperty({}, name, {
	      get: function get() {
	        isSupported = true;
	        return undefined;
	      }
	    });
	    window.addEventListener('test', null, options);
	  } // eslint-disable-next-line
	  catch (err) {}

	  return isSupported;
	}

	function fetchSupportedListenerOptions(options) {
	  if (!Type.isPlainObject(options)) {
	    return options;
	  }

	  return Object.keys(options).reduce(function (acc, name) {
	    if (isOptionSupported(name)) {
	      acc[name] = options[name];
	    }

	    return acc;
	  }, {});
	}

	function bind(target, eventName, handler, options) {
	  if (!Type.isObject(target) || !Type.isFunction(target.addEventListener)) {
	    return;
	  }

	  var listenerOptions = fetchSupportedListenerOptions(options);

	  if (eventName in aliases) {
	    aliases[eventName].forEach(function (key) {
	      target.addEventListener(key, handler, listenerOptions);
	      registry.set(target, eventName, handler);
	    });
	    return;
	  }

	  target.addEventListener(eventName, handler, listenerOptions);
	  registry.set(target, eventName, handler);
	}

	function unbind(target, eventName, handler, options) {
	  if (!Type.isObject(target) || !Type.isFunction(target.removeEventListener)) {
	    return;
	  }

	  var listenerOptions = fetchSupportedListenerOptions(options);

	  if (eventName in aliases) {
	    aliases[eventName].forEach(function (key) {
	      target.removeEventListener(key, handler, listenerOptions);
	      registry.delete(target, key, handler);
	    });
	    return;
	  }

	  target.removeEventListener(eventName, handler, listenerOptions);
	  registry.delete(target, eventName, handler);
	}

	function unbindAll(target, eventName) {
	  var events = registry.get(target);
	  Object.keys(events).forEach(function (currentEvent) {
	    events[currentEvent].forEach(function (handler) {
	      if (!Type.isString(eventName) || eventName === currentEvent) {
	        unbind(target, currentEvent, handler);
	      }
	    });
	  });
	}

	function bindOnce(target, eventName, handler, options) {
	  var once = function once() {
	    unbind(target, eventName, once, options);
	    handler.apply(void 0, arguments);
	  };

	  bind(target, eventName, once, options);
	}

	var debugState = true;
	function enableDebug() {
	  debugState = true;
	}
	function disableDebug() {
	  debugState = false;
	}
	function isDebugEnabled() {
	  return debugState;
	}
	function debug() {
	  if (isDebugEnabled() && Type.isObject(window.console)) {
	    if (Type.isFunction(window.console.log)) {
	      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	        args[_key] = arguments[_key];
	      }

	      window.console.log('BX.debug: ', args.length > 0 ? args : args[0]);

	      if (args[0] instanceof Error && args[0].stack) {
	        window.console.log('BX.debug error stack trace', args[0].stack);
	      }
	    }

	    if (Type.isFunction(window.console.trace)) {
	      // eslint-disable-next-line
	      console.trace();
	    }
	  }
	}

	function fetchExtensionSettings(html) {
	  if (Type.isStringFilled(html)) {
	    var scripts = html.match(/<script type="extension\/settings" \b[^>]*>([\s\S]*?)<\/script>/g);

	    if (Type.isArrayFilled(scripts)) {
	      return scripts.map(function (script) {
	        var _script$match = script.match(/data-extension="(.[a-z0-9_.-]+)"/),
	            _script$match2 = babelHelpers.slicedToArray(_script$match, 2),
	            extension = _script$match2[1];

	        return {
	          extension: extension,
	          script: script
	        };
	      });
	    }
	  }

	  return [];
	}

	var Extension =
	/*#__PURE__*/
	function () {
	  function Extension(options) {
	    babelHelpers.classCallCheck(this, Extension);
	    this.config = options.config || {};
	    this.name = options.extension;
	    this.state = 'scheduled'; // eslint-disable-next-line

	    var result = BX.processHTML(options.html || '');
	    this.inlineScripts = result.SCRIPT.reduce(inlineScripts, []);
	    this.externalScripts = result.SCRIPT.reduce(externalScripts, []);
	    this.externalStyles = result.STYLE.reduce(externalStyles, []);
	    this.settingsScripts = fetchExtensionSettings(result.HTML);
	  }

	  babelHelpers.createClass(Extension, [{
	    key: "load",
	    value: function load() {
	      var _this = this;

	      if (this.state === 'error') {
	        this.loadPromise = this.loadPromise || Promise.resolve(this);
	        console.warn('Extension', this.name, 'not found');
	      }

	      if (!this.loadPromise && this.state) {
	        this.state = 'load';
	        this.settingsScripts.forEach(function (entry) {
	          var isLoaded = !!document.querySelector("script[data-extension=\"".concat(entry.extension, "\"]"));

	          if (!isLoaded) {
	            document.body.insertAdjacentHTML('beforeend', entry.script);
	          }
	        });
	        this.inlineScripts.forEach(BX.evalGlobal);
	        this.loadPromise = Promise.all([loadAll(this.externalScripts), loadAll(this.externalStyles)]).then(function () {
	          _this.state = 'loaded';

	          if (Type.isPlainObject(_this.config) && _this.config.namespace) {
	            return Reflection.getClass(_this.config.namespace);
	          }

	          return window;
	        });
	      }

	      return this.loadPromise;
	    }
	  }]);
	  return Extension;
	}();

	var initialized = {};
	var ajaxController = 'main.bitrix.main.controller.loadext.getextensions';

	function makeIterable(value) {
	  return Type.isArray(value) ? value : [value];
	}
	function isInitialized(extension) {
	  return extension in initialized;
	}
	function getInitialized(extension) {
	  return initialized[extension];
	}
	function isAllInitialized(extensions) {
	  return extensions.every(isInitialized);
	}
	function loadExtensions(extensions) {
	  return Promise.all(extensions.map(function (item) {
	    return item.load();
	  }));
	}
	function mergeExports(exports) {
	  return exports.reduce(function (acc, currentExports) {
	    if (Type.isObject(currentExports)) {
	      return babelHelpers.objectSpread({}, currentExports);
	    }

	    return currentExports;
	  }, {});
	}
	function inlineScripts(acc, item) {
	  if (item.isInternal) {
	    acc.push(item.JS);
	  }

	  return acc;
	}
	function externalScripts(acc, item) {
	  if (!item.isInternal) {
	    acc.push(item.JS);
	  }

	  return acc;
	}
	function externalStyles(acc, item) {
	  if (Type.isString(item) && item !== '') {
	    acc.push(item);
	  }

	  return acc;
	}
	function request(options) {
	  return new Promise(function (resolve) {
	    // eslint-disable-next-line
	    BX.ajax.runAction(ajaxController, {
	      data: options
	    }).then(resolve);
	  });
	}
	function prepareExtensions(response) {
	  if (response.status !== 'success') {
	    response.errors.map(console.warn);
	    return [];
	  }

	  return response.data.map(function (item) {
	    var initializedExtension = getInitialized(item.extension);

	    if (initializedExtension) {
	      return initializedExtension;
	    }

	    initialized[item.extension] = new Extension(item);
	    return initialized[item.extension];
	  });
	}
	function loadAll(items) {
	  var itemsList = makeIterable(items);

	  if (!itemsList.length) {
	    return Promise.resolve();
	  }

	  return new Promise(function (resolve) {
	    // eslint-disable-next-line
	    BX.load(itemsList, resolve);
	  });
	}

	/**
	 * Loads extensions asynchronously
	 * @param {string|Array<string>} extension
	 * @return {Promise<Array<Extension>>}
	 */
	function loadExtension(extension) {
	  var extensions = makeIterable(extension);
	  var isAllInitialized$$1 = isAllInitialized(extensions);

	  if (isAllInitialized$$1) {
	    var initializedExtensions = extensions.map(getInitialized);
	    return loadExtensions(initializedExtensions).then(mergeExports);
	  }

	  return request({
	    extension: extensions
	  }).then(prepareExtensions).then(loadExtensions).then(mergeExports);
	}

	var cloneableTags = ['[object Object]', '[object Array]', '[object RegExp]', '[object Arguments]', '[object Date]', '[object Error]', '[object Map]', '[object Set]', '[object ArrayBuffer]', '[object DataView]', '[object Float32Array]', '[object Float64Array]', '[object Int8Array]', '[object Int16Array]', '[object Int32Array]', '[object Uint8Array]', '[object Uint16Array]', '[object Uint32Array]', '[object Uint8ClampedArray]'];

	function isCloneable(value) {
	  var isCloneableValue = Type.isObjectLike(value) && cloneableTags.includes(getTag(value));
	  return isCloneableValue || Type.isDomNode(value);
	}

	function internalClone(value, map) {
	  if (map.has(value)) {
	    return map.get(value);
	  }

	  if (isCloneable(value)) {
	    if (Type.isArray(value)) {
	      var cloned = Array.from(value);
	      map.set(value, cloned);
	      value.forEach(function (item, index) {
	        cloned[index] = internalClone(item, map);
	      });
	      return map.get(value);
	    }

	    if (Type.isDomNode(value)) {
	      return value.cloneNode(true);
	    }

	    if (Type.isMap(value)) {
	      var _result = new Map();

	      map.set(value, _result);
	      value.forEach(function (item, key) {
	        _result.set(internalClone(key, map), internalClone(item, map));
	      });
	      return _result;
	    }

	    if (Type.isSet(value)) {
	      var _result2 = new Set();

	      map.set(value, _result2);
	      value.forEach(function (item) {
	        _result2.add(internalClone(item, map));
	      });
	      return _result2;
	    }

	    if (Type.isDate(value)) {
	      return new Date(value);
	    }

	    if (Type.isRegExp(value)) {
	      var regExpFlags = /\w*$/;
	      var flags = regExpFlags.exec(value);

	      var _result3 = new RegExp(value.source);

	      if (flags && Type.isArray(flags)) {
	        _result3 = new RegExp(value.source, flags[0]);
	      }

	      _result3.lastIndex = value.lastIndex;
	      return _result3;
	    }

	    var proto = Object.getPrototypeOf(value);
	    var result = Object.assign(Object.create(proto), value);
	    map.set(value, result);
	    Object.keys(value).forEach(function (key) {
	      result[key] = internalClone(value[key], map);
	    });
	    return result;
	  }

	  return value;
	}
	/**
	 * Clones any cloneable object
	 * @param value
	 * @return {*}
	 */

	function clone(value) {
	  return internalClone(value, new WeakMap());
	}

	function merge(current, target) {
	  return Object.entries(target).reduce(function (acc, _ref) {
	    var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	        key = _ref2[0],
	        value = _ref2[1];

	    if (!Type.isDomNode(acc[key]) && Type.isObjectLike(acc[key]) && Type.isObjectLike(value)) {
	      acc[key] = merge(acc[key], value);
	      return acc;
	    }

	    acc[key] = value;
	    return acc;
	  }, current);
	}

	function createComparator(fields) {
	  var orders = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	  return function (a, b) {
	    var field = fields[0];
	    var order = orders[0] || 'asc';

	    if (Type.isUndefined(field)) {
	      return 0;
	    }

	    var valueA = a[field];
	    var valueB = b[field];

	    if (Type.isString(valueA) && Type.isString(valueB)) {
	      valueA = valueA.toLowerCase();
	      valueB = valueB.toLowerCase();
	    }

	    if (valueA < valueB) {
	      return order === 'asc' ? -1 : 1;
	    }

	    if (valueA > valueB) {
	      return order === 'asc' ? 1 : -1;
	    }

	    return createComparator(fields.slice(1), orders.slice(1))(a, b);
	  };
	}

	/**
	 * @memberOf BX
	 */

	var Runtime =
	/*#__PURE__*/
	function () {
	  function Runtime() {
	    babelHelpers.classCallCheck(this, Runtime);
	  }

	  babelHelpers.createClass(Runtime, null, [{
	    key: "debounce",
	    value: function debounce(func) {
	      var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var timeoutId;
	      return function debounced() {
	        var _this = this;

	        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	          args[_key] = arguments[_key];
	        }

	        if (Type.isNumber(timeoutId)) {
	          clearTimeout(timeoutId);
	        }

	        timeoutId = setTimeout(function () {
	          func.apply(context || _this, args);
	        }, wait);
	      };
	    }
	  }, {
	    key: "throttle",
	    value: function throttle(func) {
	      var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var timer = 0;
	      var invoke;
	      return function wrapper() {
	        for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	          args[_key2] = arguments[_key2];
	        }

	        invoke = true;

	        if (!timer) {
	          var q = function q() {
	            if (invoke) {
	              func.apply(context || this, args);
	              invoke = false;
	              timer = setTimeout(q, wait);
	            } else {
	              timer = null;
	            }
	          };

	          q();
	        }
	      };
	    }
	  }, {
	    key: "html",
	    value: function html(node, _html) {
	      var params = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	      if (Type.isNil(_html) && Type.isDomNode(node)) {
	        return node.innerHTML;
	      } // eslint-disable-next-line


	      var parsedHtml = BX.processHTML(_html);
	      var externalCss = parsedHtml.STYLE.reduce(externalStyles, []);
	      var externalJs = parsedHtml.SCRIPT.reduce(externalScripts, []);
	      var inlineJs = parsedHtml.SCRIPT.reduce(inlineScripts, []);

	      if (Type.isDomNode(node)) {
	        if (params.htmlFirst || !externalJs.length && !externalCss.length) {
	          node.innerHTML = parsedHtml.HTML;
	        }
	      }

	      return Promise.all([loadAll(externalJs), loadAll(externalCss)]).then(function () {
	        if (Type.isDomNode(node) && (externalJs.length > 0 || externalCss.length > 0)) {
	          node.innerHTML = parsedHtml.HTML;
	        } // eslint-disable-next-line


	        inlineJs.forEach(function (script) {
	          return BX.evalGlobal(script);
	        });

	        if (Type.isFunction(params.callback)) {
	          params.callback();
	        }
	      });
	    }
	    /**
	     * Merges objects or arrays
	     * @param targets
	     * @return {any}
	     */

	  }, {
	    key: "merge",
	    value: function merge$$1() {
	      for (var _len3 = arguments.length, targets = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	        targets[_key3] = arguments[_key3];
	      }

	      if (Type.isArray(targets[0])) {
	        targets.unshift([]);
	      } else if (Type.isObject(targets[0])) {
	        targets.unshift({});
	      }

	      return targets.reduce(function (acc, item) {
	        return merge(acc, item);
	      }, targets[0]);
	    }
	  }, {
	    key: "orderBy",
	    value: function orderBy(collection) {
	      var fields = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	      var orders = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
	      var comparator = createComparator(fields, orders);
	      return Object.values(collection).sort(comparator);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(target) {
	      var errorMessage = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'Object is destroyed';

	      if (Type.isObject(target)) {
	        var onPropertyAccess = function onPropertyAccess() {
	          throw new Error(errorMessage);
	        };

	        var ownProperties = Object.keys(target);

	        var prototypeProperties = function () {
	          var targetPrototype = Object.getPrototypeOf(target);

	          if (Type.isObject(targetPrototype)) {
	            return Object.getOwnPropertyNames(targetPrototype);
	          }

	          return [];
	        }();

	        var uniquePropertiesList = babelHelpers.toConsumableArray(new Set([].concat(babelHelpers.toConsumableArray(ownProperties), babelHelpers.toConsumableArray(prototypeProperties))));
	        uniquePropertiesList.filter(function (name) {
	          var descriptor = Object.getOwnPropertyDescriptor(target, name);
	          return !/__(.+)__/.test(name) && (!Type.isObject(descriptor) || descriptor.configurable === true);
	        }).forEach(function (name) {
	          Object.defineProperty(target, name, {
	            get: onPropertyAccess,
	            set: onPropertyAccess,
	            configurable: false
	          });
	        });
	        Object.setPrototypeOf(target, null);
	      }
	    }
	  }]);
	  return Runtime;
	}();

	babelHelpers.defineProperty(Runtime, "debug", debug);
	babelHelpers.defineProperty(Runtime, "loadExtension", loadExtension);
	babelHelpers.defineProperty(Runtime, "clone", clone);

	var _isError = Symbol.for('BX.BaseError.isError');
	/**
	 * @memberOf BX
	 */


	var BaseError =
	/*#__PURE__*/
	function () {
	  function BaseError(message, code, customData) {
	    babelHelpers.classCallCheck(this, BaseError);
	    this[_isError] = true;
	    this.message = '';
	    this.code = null;
	    this.customData = null;
	    this.setMessage(message);
	    this.setCode(code);
	    this.setCustomData(customData);
	  }
	  /**
	   * Returns a brief description of the error
	   * @returns {string}
	   */


	  babelHelpers.createClass(BaseError, [{
	    key: "getMessage",
	    value: function getMessage() {
	      return this.message;
	    }
	    /**
	     * Sets a message of the error
	     * @param {string} message
	     * @returns {this}
	     */

	  }, {
	    key: "setMessage",
	    value: function setMessage(message) {
	      if (Type.isString(message)) {
	        this.message = message;
	      }

	      return this;
	    }
	    /**
	     * Returns a code of the error
	     * @returns {?string}
	     */

	  }, {
	    key: "getCode",
	    value: function getCode() {
	      return this.code;
	    }
	    /**
	     * Sets a code of the error
	     * @param {string} code
	     * @returns {this}
	     */

	  }, {
	    key: "setCode",
	    value: function setCode(code) {
	      if (Type.isStringFilled(code) || code === null) {
	        this.code = code;
	      }

	      return this;
	    }
	    /**
	     * Returns custom data of the error
	     * @returns {null|*}
	     */

	  }, {
	    key: "getCustomData",
	    value: function getCustomData() {
	      return this.customData;
	    }
	    /**
	     * Sets custom data of the error
	     * @returns {this}
	     */

	  }, {
	    key: "setCustomData",
	    value: function setCustomData(customData) {
	      if (!Type.isUndefined(customData)) {
	        this.customData = customData;
	      }

	      return this;
	    }
	  }, {
	    key: "toString",
	    value: function toString() {
	      var code = this.getCode();
	      var message = this.getMessage();

	      if (!Type.isStringFilled(code) && !Type.isStringFilled(message)) {
	        return '';
	      } else if (!Type.isStringFilled(code)) {
	        return "Error: ".concat(message);
	      } else if (!Type.isStringFilled(message)) {
	        return code;
	      } else {
	        return "".concat(code, ": ").concat(message);
	      }
	    }
	    /**
	     * Returns true if the object is an instance of BaseError
	     * @param error
	     * @returns {boolean}
	     */

	  }], [{
	    key: "isError",
	    value: function isError(error) {
	      return Type.isObject(error) && error[_isError] === true;
	    }
	  }]);
	  return BaseError;
	}();

	/**
	 * Implements base event object interface
	 */

	var BaseEvent =
	/*#__PURE__*/
	function () {
	  function BaseEvent() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      data: {}
	    };
	    babelHelpers.classCallCheck(this, BaseEvent);
	    this.type = '';
	    this.data = null;
	    this.target = null;
	    this.compatData = null;
	    this.defaultPrevented = false;
	    this.immediatePropagationStopped = false;
	    this.errors = [];
	    this.setData(options.data);
	    this.setCompatData(options.compatData);
	  }

	  babelHelpers.createClass(BaseEvent, [{
	    key: "getType",

	    /**
	     * Returns the name of the event
	     * @returns {string}
	     */
	    value: function getType() {
	      return this.type;
	    }
	    /**
	     *
	     * @param {string} type
	     */

	  }, {
	    key: "setType",
	    value: function setType(type) {
	      if (Type.isStringFilled(type)) {
	        this.type = type;
	      }

	      return this;
	    }
	    /**
	     * Returns an event data
	     */

	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	    /**
	     * Sets an event data
	     * @param data
	     */

	  }, {
	    key: "setData",
	    value: function setData(data) {
	      if (!Type.isUndefined(data)) {
	        this.data = data;
	      }

	      return this;
	    }
	    /**
	     * Returns arguments for BX.addCustomEvent handlers (deprecated).
	     * @returns {array | null}
	     */

	  }, {
	    key: "getCompatData",
	    value: function getCompatData() {
	      return this.compatData;
	    }
	    /**
	     * Sets arguments for BX.addCustomEvent handlers (deprecated)
	     * @param data
	     */

	  }, {
	    key: "setCompatData",
	    value: function setCompatData(data) {
	      if (Type.isArrayLike(data)) {
	        this.compatData = data;
	      }

	      return this;
	    }
	    /**
	     * Sets a event target
	     * @param target
	     */

	  }, {
	    key: "setTarget",
	    value: function setTarget(target) {
	      this.target = target;
	      return this;
	    }
	    /**
	     * Returns a event target
	     */

	  }, {
	    key: "getTarget",
	    value: function getTarget() {
	      return this.target;
	    }
	    /**
	     * Returns an array of event errors
	     * @returns {[]}
	     */

	  }, {
	    key: "getErrors",
	    value: function getErrors() {
	      return this.errors;
	    }
	    /**
	     * Adds an error of the event.
	     * Event listeners can prevent emitter's default action and set the reason of this behavior.
	     * @param error
	     */

	  }, {
	    key: "setError",
	    value: function setError(error) {
	      if (BaseError.isError(error)) {
	        this.errors.push(error);
	      }
	    }
	    /**
	     * Prevents default action
	     */

	  }, {
	    key: "preventDefault",
	    value: function preventDefault() {
	      this.defaultPrevented = true;
	    }
	    /**
	     * Checks that is default action prevented
	     * @return {boolean}
	     */

	  }, {
	    key: "isDefaultPrevented",
	    value: function isDefaultPrevented() {
	      return this.defaultPrevented;
	    }
	    /**
	     * Stops event immediate propagation
	     */

	  }, {
	    key: "stopImmediatePropagation",
	    value: function stopImmediatePropagation() {
	      this.immediatePropagationStopped = true;
	    }
	    /**
	     * Checks that is immediate propagation stopped
	     * @return {boolean}
	     */

	  }, {
	    key: "isImmediatePropagationStopped",
	    value: function isImmediatePropagationStopped() {
	      return this.immediatePropagationStopped;
	    }
	  }], [{
	    key: "create",
	    value: function create(options) {
	      return new this(options);
	    }
	  }]);
	  return BaseEvent;
	}();

	var EventStore =
	/*#__PURE__*/
	function () {
	  function EventStore() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EventStore);
	    this.defaultMaxListeners = Type.isNumber(options.defaultMaxListeners) ? options.defaultMaxListeners : 10;
	    this.eventStore = new WeakMap();
	  }

	  babelHelpers.createClass(EventStore, [{
	    key: "add",
	    value: function add(target) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var record = this.getRecordScheme();

	      if (Type.isNumber(options.maxListeners)) {
	        record.maxListeners = options.maxListeners;
	      }

	      this.eventStore.set(target, record);
	      return record;
	    }
	  }, {
	    key: "get",
	    value: function get(target) {
	      return this.eventStore.get(target);
	    }
	  }, {
	    key: "getOrAdd",
	    value: function getOrAdd(target) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      return this.get(target) || this.add(target, options);
	    }
	  }, {
	    key: "delete",
	    value: function _delete(context) {
	      this.eventStore.delete(context);
	    }
	  }, {
	    key: "getRecordScheme",
	    value: function getRecordScheme() {
	      return {
	        eventsMap: new Map(),
	        onceMap: new Map(),
	        maxListeners: this.getDefaultMaxListeners(),
	        eventsMaxListeners: new Map()
	      };
	    }
	  }, {
	    key: "getDefaultMaxListeners",
	    value: function getDefaultMaxListeners() {
	      return this.defaultMaxListeners;
	    }
	  }]);
	  return EventStore;
	}();

	var WarningStore =
	/*#__PURE__*/
	function () {
	  function WarningStore() {
	    babelHelpers.classCallCheck(this, WarningStore);
	    this.warnings = new Map();
	    this.printDelayed = Runtime.debounce(this.print.bind(this), 500);
	  }

	  babelHelpers.createClass(WarningStore, [{
	    key: "add",
	    value: function add(target, eventName, listeners) {
	      var contextWarnings = this.warnings.get(target);

	      if (!contextWarnings) {
	        contextWarnings = Object.create(null);
	        this.warnings.set(target, contextWarnings);
	      }

	      if (!contextWarnings[eventName]) {
	        contextWarnings[eventName] = {};
	      }

	      contextWarnings[eventName].size = listeners.size;

	      if (!Type.isArray(contextWarnings[eventName].errors)) {
	        contextWarnings[eventName].errors = [];
	      }

	      contextWarnings[eventName].errors.push(new Error());
	    }
	  }, {
	    key: "print",
	    value: function print() {
	      var _iteratorNormalCompletion = true;
	      var _didIteratorError = false;
	      var _iteratorError = undefined;

	      try {
	        for (var _iterator = this.warnings[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	          var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	              target = _step$value[0],
	              warnings = _step$value[1];

	          for (var eventName in warnings) {
	            console.groupCollapsed('Possible BX.Event.EventEmitter memory leak detected. ' + warnings[eventName].size + ' "' + eventName + '" listeners added. ' + 'Use emitter.setMaxListeners() to increase limit.');
	            console.dir(warnings[eventName].errors);
	            console.groupEnd();
	          }
	        }
	      } catch (err) {
	        _didIteratorError = true;
	        _iteratorError = err;
	      } finally {
	        try {
	          if (!_iteratorNormalCompletion && _iterator.return != null) {
	            _iterator.return();
	          }
	        } finally {
	          if (_didIteratorError) {
	            throw _iteratorError;
	          }
	        }
	      }

	      this.clear();
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.warnings.clear();
	    }
	  }, {
	    key: "printDelayed",
	    value: function printDelayed() {}
	  }]);
	  return WarningStore;
	}();

	var eventStore = new EventStore({
	  defaultMaxListeners: 10
	});
	var warningStore = new WarningStore();
	var aliasStore = new Map();
	var globalTarget = {
	  GLOBAL_TARGET: 'GLOBAL_TARGET' // this key only for debugging purposes

	};
	eventStore.add(globalTarget, {
	  maxListeners: 25
	});
	var isEmitterProperty = Symbol.for('BX.Event.EventEmitter.isEmitter');
	var namespaceProperty = Symbol('namespaceProperty');
	var targetProperty = Symbol('targetProperty');

	var EventEmitter =
	/*#__PURE__*/
	function () {
	  /** @private */
	  function EventEmitter() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, EventEmitter);
	    this[targetProperty] = null;
	    this[namespaceProperty] = null;
	    this[isEmitterProperty] = true;
	    var target = this;

	    if (Object.getPrototypeOf(this) === EventEmitter.prototype && arguments.length > 0) //new EventEmitter(obj) case
	      {
	        if (!Type.isObject(arguments.length <= 0 ? undefined : arguments[0])) {
	          throw new TypeError("The \"target\" argument must be an object.");
	        }

	        target = arguments.length <= 0 ? undefined : arguments[0];
	        this.setEventNamespace(arguments.length <= 1 ? undefined : arguments[1]);
	      }

	    this[targetProperty] = target;
	    setTimeout(function () {
	      if (_this.getEventNamespace() === null) {
	        console.warn('The instance of BX.Event.EventEmitter is supposed to have an event namespace. ' + 'Use emitter.setEventNamespace() to make events more unique.');
	      }
	    }, 500);
	  }
	  /**
	   * Makes a target observable
	   * @param {object} target
	   * @param {string} namespace
	   */


	  babelHelpers.createClass(EventEmitter, [{
	    key: "setEventNamespace",
	    value: function setEventNamespace(namespace) {
	      if (Type.isStringFilled(namespace)) {
	        this[namespaceProperty] = namespace;
	      }
	    }
	  }, {
	    key: "getEventNamespace",
	    value: function getEventNamespace() {
	      return this[namespaceProperty];
	    }
	    /**
	     * Subscribes listener on specified global event
	     * @param {object} target
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @param {object} options
	     */

	  }, {
	    key: "subscribe",

	    /**
	     * Subscribes a listener on a specified event
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @return {this}
	     */
	    value: function subscribe(eventName, listener) {
	      EventEmitter.subscribe(this, eventName, listener);
	      return this;
	    }
	    /**
	     *
	     * @param {object} options
	     * @param {object} [aliases]
	     * @param {boolean} [compatMode=false]
	     */

	  }, {
	    key: "subscribeFromOptions",
	    value: function subscribeFromOptions(options, aliases, compatMode) {
	      var _this2 = this;

	      if (!Type.isPlainObject(options)) {
	        return;
	      }

	      aliases = Type.isPlainObject(aliases) ? EventEmitter.normalizeAliases(aliases) : {};
	      Object.keys(options).forEach(function (eventName) {
	        var listener = options[eventName];

	        if (!Type.isFunction(listener)) {
	          throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(babelHelpers.typeof(listener), "."));
	        }

	        eventName = EventEmitter.normalizeEventName(eventName);

	        if (aliases[eventName]) {
	          var actualName = aliases[eventName].eventName;
	          EventEmitter.subscribe(_this2, actualName, listener, {
	            compatMode: compatMode !== false
	          });
	        } else {
	          EventEmitter.subscribe(_this2, eventName, listener, {
	            compatMode: compatMode === true
	          });
	        }
	      });
	    }
	    /**
	     * Subscribes a listener that is called at
	     * most once for a specified event.
	     * @param {object} target
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     */

	  }, {
	    key: "subscribeOnce",

	    /**
	     * Subscribes a listener that is called at most once for a specified event.
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @return {this}
	     */
	    value: function subscribeOnce(eventName, listener) {
	      EventEmitter.subscribeOnce(this, eventName, listener);
	      return this;
	    }
	    /**
	     * Unsubscribes an event listener
	     * @param {object} target
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @param options
	     */

	  }, {
	    key: "unsubscribe",

	    /**
	     * Unsubscribes an event listener
	     * @param {string} eventName
	     * @param {Function<BaseEvent>} listener
	     * @return {this}
	     */
	    value: function unsubscribe(eventName, listener) {
	      EventEmitter.unsubscribe(this, eventName, listener);
	      return this;
	    }
	    /**
	     * Unsubscribes all event listeners
	     * @param {object} target
	     * @param {string} eventName
	     * @param options
	     */

	  }, {
	    key: "unsubscribeAll",

	    /**
	     * Unsubscribes all event listeners
	     * @param {string} [eventName]
	     */
	    value: function unsubscribeAll(eventName) {
	      EventEmitter.unsubscribeAll(this, eventName);
	    }
	    /**
	     *
	     * @param {object} target
	     * @param {string} eventName
	     * @param {BaseEvent | any} event
	     * @param {object} options
	     * @returns {Array}
	     */

	  }, {
	    key: "emit",

	    /**
	     * Emits specified event with specified event object
	     * @param {string} eventName
	     * @param {BaseEvent | any} event
	     * @return {this}
	     */
	    value: function emit(eventName, event) {
	      EventEmitter.emit(this, eventName, event);
	      return this;
	    }
	    /**
	     * Emits global event and returns a promise that is resolved when
	     * all promise returned from event handlers are resolved,
	     * or rejected when at least one of the returned promise is rejected.
	     * Importantly. You can return any value from synchronous handlers, not just promise
	     * @param {object} target
	     * @param {string} eventName
	     * @param {BaseEvent | any} event
	     * @return {Promise<Array>}
	     */

	  }, {
	    key: "emitAsync",

	    /**
	     * Emits event and returns a promise that is resolved when
	     * all promise returned from event handlers are resolved,
	     * or rejected when at least one of the returned promise is rejected.
	     * Importantly. You can return any value from synchronous handlers, not just promise
	     * @param {string} eventName
	     * @param {BaseEvent|any} event
	     * @return {Promise<Array>}
	     */
	    value: function emitAsync(eventName, event) {
	      return EventEmitter.emitAsync(this, eventName, event);
	    }
	    /**
	     * @private
	     * @param {object} target
	     * @param {string} eventName
	     * @param {BaseEvent|any} event
	     * @returns {BaseEvent}
	     */

	  }, {
	    key: "setMaxListeners",

	    /**
	     * Sets max events listeners count
	     * this.setMaxListeners(10) - sets the default value for all events
	     * this.setMaxListeners("onClose", 10) sets the value for onClose event
	     * @return {this}
	     * @param args
	     */
	    value: function setMaxListeners() {
	      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	        args[_key] = arguments[_key];
	      }

	      EventEmitter.setMaxListeners.apply(EventEmitter, [this].concat(args));
	      return this;
	    }
	    /**
	     * Returns max event listeners count
	     * @param {object} target
	     * @param {string} [eventName]
	     * @returns {number}
	     */

	  }, {
	    key: "getMaxListeners",

	    /**
	     * Returns max event listeners count
	     * @param {string} [eventName]
	     * @returns {number}
	     */
	    value: function getMaxListeners(eventName) {
	      return EventEmitter.getMaxListeners(this, eventName);
	    }
	    /**
	     * Adds or subtracts max listeners count
	     * Event.EventEmitter.addMaxListeners() - adds one max listener for all events of global target
	     * Event.EventEmitter.addMaxListeners(3) - adds three max listeners for all events of global target
	     * Event.EventEmitter.addMaxListeners(-1) - subtracts one max listener for all events of global target
	     * Event.EventEmitter.addMaxListeners('onClose') - adds one max listener for onClose event of global target
	     * Event.EventEmitter.addMaxListeners('onClose', 2) - adds two max listeners for onClose event of global target
	     * Event.EventEmitter.addMaxListeners('onClose', -1) - subtracts one max listener for onClose event of global target
	     *
	     * Event.EventEmitter.addMaxListeners(obj) - adds one max listener for all events of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 3) - adds three max listeners for all events of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, -1) - subtracts one max listener for all events of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 'onClose') - adds one max listener for onClose event of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 'onClose', 2) - adds two max listeners for onClose event of 'obj' target
	     * Event.EventEmitter.addMaxListeners(obj, 'onClose', -1) - subtracts one max listener for onClose event of 'obj' target
	     * @param args
	     * @returns {number}
	     */

	  }, {
	    key: "incrementMaxListeners",

	    /**
	     * Increases max listeners count
	     * this.incrementMaxListeners() - adds one max listener for all events
	     * this.incrementMaxListeners(3) - adds three max listeners for all events
	     * this.incrementMaxListeners('onClose') - adds one max listener for onClose event
	     * this.incrementMaxListeners('onClose', 2) - adds two max listeners for onClose event
	     */
	    value: function incrementMaxListeners() {
	      for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	        args[_key2] = arguments[_key2];
	      }

	      return EventEmitter.incrementMaxListeners.apply(EventEmitter, [this].concat(args));
	    }
	    /**
	     * Decreases max listeners count
	     *
	     * Event.EventEmitter.decrementMaxListeners() - subtracts one max listener for all events of global target
	     * Event.EventEmitter.decrementMaxListeners(3) - subtracts three max listeners for all events of global target
	     * Event.EventEmitter.decrementMaxListeners('onClose') - subtracts one max listener for onClose event of global target
	     * Event.EventEmitter.decrementMaxListeners('onClose', 2) - subtracts two max listeners for onClose event of global target
	     *
	     * Event.EventEmitter.decrementMaxListeners(obj) - subtracts one max listener for all events of 'obj' target
	     * Event.EventEmitter.decrementMaxListeners(obj, 3) - subtracts three max listeners for all events of 'obj' target
	     * Event.EventEmitter.decrementMaxListeners(obj, 'onClose') - subtracts one max listener for onClose event of 'obj' target
	     * Event.EventEmitter.decrementMaxListeners(obj, 'onClose', 2) - subtracts two max listeners for onClose event of 'obj' target
	     */

	  }, {
	    key: "decrementMaxListeners",

	    /**
	     * Increases max listeners count
	     * this.decrementMaxListeners() - subtracts one max listener for all events
	     * this.decrementMaxListeners(3) - subtracts three max listeners for all events
	     * this.decrementMaxListeners('onClose') - subtracts one max listener for onClose event
	     * this.decrementMaxListeners('onClose', 2) - subtracts two max listeners for onClose event
	     */
	    value: function decrementMaxListeners() {
	      for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	        args[_key3] = arguments[_key3];
	      }

	      return EventEmitter.decrementMaxListeners.apply(EventEmitter, [this].concat(args));
	    }
	    /**
	     * @private
	     * @param {Array} args
	     * @returns Array
	     */

	  }, {
	    key: "getListeners",

	    /**
	     * Gets listeners list for specified event
	     * @param {string} eventName
	     */
	    value: function getListeners(eventName) {
	      return EventEmitter.getListeners(this, eventName);
	    }
	    /**
	     * Returns a full event name with namespace
	     * @param {string} eventName
	     * @returns {string}
	     */

	  }, {
	    key: "getFullEventName",
	    value: function getFullEventName(eventName) {
	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      return EventEmitter.makeFullEventName(this.getEventNamespace(), eventName);
	    }
	    /**
	     * Registers aliases (old event names for BX.onCustomEvent)
	     * @param aliases
	     */

	  }], [{
	    key: "makeObservable",
	    value: function makeObservable(target, namespace) {
	      if (!Type.isObject(target)) {
	        throw new TypeError('The "target" argument must be an object.');
	      }

	      if (!Type.isStringFilled(namespace)) {
	        throw new TypeError('The "namespace" must be an non-empty string.');
	      }

	      if (EventEmitter.isEventEmitter(target)) {
	        throw new TypeError('The "target" is an event emitter already.');
	      }

	      var targetProto = Object.getPrototypeOf(target);
	      var emitter = new EventEmitter();
	      emitter.setEventNamespace(namespace);
	      Object.setPrototypeOf(emitter, targetProto);
	      Object.setPrototypeOf(target, emitter);
	      Object.getOwnPropertyNames(EventEmitter.prototype).forEach(function (method) {
	        if (['constructor'].includes(method)) {
	          return;
	        }

	        emitter[method] = function () {
	          for (var _len4 = arguments.length, args = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
	            args[_key4] = arguments[_key4];
	          }

	          return EventEmitter.prototype[method].apply(target, args);
	        };
	      });
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe(target, eventName, listener, options) {
	      if (Type.isString(target)) {
	        options = listener;
	        listener = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isFunction(listener)) {
	        throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(babelHelpers.typeof(listener), "."));
	      }

	      options = Type.isPlainObject(options) ? options : {};
	      var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);

	      var _eventStore$getOrAdd = eventStore.getOrAdd(target),
	          eventsMap = _eventStore$getOrAdd.eventsMap,
	          onceMap = _eventStore$getOrAdd.onceMap;

	      var onceListeners = onceMap.get(fullEventName);
	      var listeners = eventsMap.get(fullEventName);

	      if (listeners && listeners.has(listener) || onceListeners && onceListeners.has(listener)) {
	        console.error("You cannot subscribe the same \"".concat(fullEventName, "\" event listener twice."));
	      } else {
	        if (listeners) {
	          listeners.set(listener, {
	            listener: listener,
	            options: options,
	            sort: this.getNextSequenceValue()
	          });
	        } else {
	          listeners = new Map([[listener, {
	            listener: listener,
	            options: options,
	            sort: this.getNextSequenceValue()
	          }]]);
	          eventsMap.set(fullEventName, listeners);
	        }
	      }

	      var maxListeners = this.getMaxListeners(target, eventName);

	      if (listeners.size > maxListeners) {
	        warningStore.add(target, fullEventName, listeners);
	        warningStore.printDelayed();
	      }
	    }
	  }, {
	    key: "subscribeOnce",
	    value: function subscribeOnce(target, eventName, listener) {
	      var _this3 = this;

	      if (Type.isString(target)) {
	        listener = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isFunction(listener)) {
	        throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(babelHelpers.typeof(listener), "."));
	      }

	      var fullEventName = this.resolveEventName(eventName, target);

	      var _eventStore$getOrAdd2 = eventStore.getOrAdd(target),
	          eventsMap = _eventStore$getOrAdd2.eventsMap,
	          onceMap = _eventStore$getOrAdd2.onceMap;

	      var listeners = eventsMap.get(fullEventName);
	      var onceListeners = onceMap.get(fullEventName);

	      if (listeners && listeners.has(listener) || onceListeners && onceListeners.has(listener)) {
	        console.error("You cannot subscribe the same \"".concat(fullEventName, "\" event listener twice."));
	      } else {
	        var once = function once() {
	          _this3.unsubscribe(target, eventName, once);

	          onceListeners.delete(listener);
	          listener.apply(void 0, arguments);
	        };

	        if (onceListeners) {
	          onceListeners.set(listener, once);
	        } else {
	          onceListeners = new Map([[listener, once]]);
	          onceMap.set(fullEventName, onceListeners);
	        }

	        this.subscribe(target, eventName, once);
	      }
	    }
	  }, {
	    key: "unsubscribe",
	    value: function unsubscribe(target, eventName, listener, options) {
	      if (Type.isString(target)) {
	        listener = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isFunction(listener)) {
	        throw new TypeError("The \"listener\" argument must be of type Function. Received type ".concat(typeof event === "undefined" ? "undefined" : babelHelpers.typeof(event), "."));
	      }

	      options = Type.isPlainObject(options) ? options : {};
	      var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      var targetInfo = eventStore.get(target);
	      var listeners = targetInfo && targetInfo.eventsMap.get(fullEventName);
	      var onceListeners = targetInfo && targetInfo.onceMap.get(fullEventName);

	      if (listeners) {
	        listeners.delete(listener);
	      }

	      if (onceListeners) {
	        var once = onceListeners.get(listener);

	        if (once) {
	          onceListeners.delete(listener);
	          listeners.delete(once);
	        }
	      }
	    }
	  }, {
	    key: "unsubscribeAll",
	    value: function unsubscribeAll(target, eventName, options) {
	      if (Type.isString(target)) {
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      if (Type.isStringFilled(eventName)) {
	        var targetInfo = eventStore.get(target);

	        if (targetInfo) {
	          options = Type.isPlainObject(options) ? options : {};
	          var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	          targetInfo.eventsMap.delete(fullEventName);
	          targetInfo.onceMap.delete(fullEventName);
	        }
	      } else if (Type.isNil(eventName)) {
	        if (target === this.GLOBAL_TARGET) {
	          console.error('You cannot unsubscribe all global listeners.');
	        } else {
	          eventStore.delete(target);
	        }
	      }
	    }
	  }, {
	    key: "emit",
	    value: function emit(target, eventName, event, options) {
	      if (Type.isString(target)) {
	        options = event;
	        event = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      options = Type.isPlainObject(options) ? options : {};
	      var fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
	      var globalEvents = eventStore.get(this.GLOBAL_TARGET);
	      var globalListeners = globalEvents && globalEvents.eventsMap.get(fullEventName) || new Map();
	      var targetListeners = new Set();

	      if (target !== this.GLOBAL_TARGET) {
	        var targetEvents = eventStore.get(target);
	        targetListeners = targetEvents && targetEvents.eventsMap.get(fullEventName) || new Map();
	      }

	      var listeners = [].concat(babelHelpers.toConsumableArray(globalListeners.values()), babelHelpers.toConsumableArray(targetListeners.values()));
	      listeners.sort(function (a, b) {
	        return a.sort - b.sort;
	      });
	      var preparedEvent = this.prepareEvent(target, fullEventName, event);
	      var result = [];

	      for (var i = 0; i < listeners.length; i++) {
	        if (preparedEvent.isImmediatePropagationStopped()) {
	          break;
	        }

	        var _listeners$i = listeners[i],
	            listener = _listeners$i.listener,
	            listenerOptions = _listeners$i.options; //A previous listener could remove a current listener.

	        if (globalListeners.has(listener) || targetListeners.has(listener)) {
	          var listenerResult = void 0;

	          if (listenerOptions.compatMode) {
	            var params = [];
	            var compatData = preparedEvent.getCompatData();

	            if (compatData !== null) {
	              params = options.cloneData === true ? Runtime.clone(compatData) : compatData;
	            } else {
	              params = [preparedEvent];
	            }

	            var context = Type.isUndefined(options.thisArg) ? target : options.thisArg;
	            listenerResult = listener.apply(context, params);
	          } else {
	            listenerResult = Type.isUndefined(options.thisArg) ? listener(preparedEvent) : listener.call(options.thisArg, preparedEvent);
	          }

	          result.push(listenerResult);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "emitAsync",
	    value: function emitAsync(target, eventName, event) {
	      if (Type.isString(target)) {
	        event = eventName;
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      return Promise.all(this.emit(target, eventName, event));
	    }
	  }, {
	    key: "prepareEvent",
	    value: function prepareEvent(target, eventName, event) {
	      var preparedEvent = event;

	      if (!(event instanceof BaseEvent)) {
	        preparedEvent = new BaseEvent();
	        preparedEvent.setData(event);
	      }

	      preparedEvent.setTarget(this.isEventEmitter(target) ? target[targetProperty] : target);
	      preparedEvent.setType(eventName);
	      return preparedEvent;
	    }
	    /**
	     * @private
	     * @returns {number}
	     */

	  }, {
	    key: "getNextSequenceValue",
	    value: function getNextSequenceValue() {
	      return this.sequenceValue++;
	    }
	    /**
	     * Sets max global events listeners count
	     * Event.EventEmitter.setMaxListeners(10) - sets the default value for all events (global target)
	     * Event.EventEmitter.setMaxListeners("onClose", 10) - sets the value for onClose event (global target)
	     * Event.EventEmitter.setMaxListeners(obj, 10) - sets the default value for all events (obj target)
	     * Event.EventEmitter.setMaxListeners(obj, "onClose", 10); - sets the value for onClose event (obj target)
	     * @return {void}
	     * @param args
	     */

	  }, {
	    key: "setMaxListeners",
	    value: function setMaxListeners() {
	      var target = this.GLOBAL_TARGET;
	      var eventName = null;
	      var count = undefined;

	      for (var _len5 = arguments.length, args = new Array(_len5), _key5 = 0; _key5 < _len5; _key5++) {
	        args[_key5] = arguments[_key5];
	      }

	      if (args.length === 1) {
	        count = args[0];
	      } else if (args.length === 2) {
	        if (Type.isString(args[0])) {
	          eventName = args[0];
	          count = args[1];
	        } else {
	          target = args[0];
	          count = args[1];
	        }
	      } else if (args.length >= 3) {
	        target = args[0];
	        eventName = args[1];
	        count = args[2];
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      if (eventName !== null && !Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isNumber(count) || count < 0) {
	        throw new TypeError("The value of \"count\" is out of range. It must be a non-negative number. Received ".concat(count, "."));
	      }

	      var targetInfo = eventStore.getOrAdd(target);

	      if (Type.isStringFilled(eventName)) {
	        var fullEventName = this.resolveEventName(eventName, target);
	        targetInfo.eventsMaxListeners.set(fullEventName, count);
	      } else {
	        targetInfo.maxListeners = count;
	      }
	    }
	  }, {
	    key: "getMaxListeners",
	    value: function getMaxListeners(target, eventName) {
	      if (Type.isString(target)) {
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      } else if (Type.isNil(target)) {
	        target = this.GLOBAL_TARGET;
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      var targetInfo = eventStore.get(target);

	      if (targetInfo) {
	        var maxListeners = targetInfo.maxListeners;

	        if (Type.isStringFilled(eventName)) {
	          var fullEventName = this.resolveEventName(eventName, target);
	          maxListeners = targetInfo.eventsMaxListeners.get(fullEventName) || maxListeners;
	        }

	        return maxListeners;
	      }

	      return this.DEFAULT_MAX_LISTENERS;
	    }
	  }, {
	    key: "addMaxListeners",
	    value: function addMaxListeners() {
	      var _this$destructMaxList = this.destructMaxListenersArgs.apply(this, arguments),
	          _this$destructMaxList2 = babelHelpers.slicedToArray(_this$destructMaxList, 3),
	          target = _this$destructMaxList2[0],
	          eventName = _this$destructMaxList2[1],
	          increment = _this$destructMaxList2[2];

	      var maxListeners = Math.max(this.getMaxListeners(target, eventName) + increment, 0);

	      if (Type.isStringFilled(eventName)) {
	        EventEmitter.setMaxListeners(target, eventName, maxListeners);
	      } else {
	        EventEmitter.setMaxListeners(target, maxListeners);
	      }

	      return maxListeners;
	    }
	    /**
	     * Increases max listeners count
	     *
	     * Event.EventEmitter.incrementMaxListeners() - adds one max listener for all events of global target
	     * Event.EventEmitter.incrementMaxListeners(3) - adds three max listeners for all events of global target
	     * Event.EventEmitter.incrementMaxListeners('onClose') - adds one max listener for onClose event of global target
	     * Event.EventEmitter.incrementMaxListeners('onClose', 2) - adds two max listeners for onClose event of global target
	     *
	     * Event.EventEmitter.incrementMaxListeners(obj) - adds one max listener for all events of 'obj' target
	     * Event.EventEmitter.incrementMaxListeners(obj, 3) - adds three max listeners for all events of 'obj' target
	     * Event.EventEmitter.incrementMaxListeners(obj, 'onClose') - adds one max listener for onClose event of 'obj' target
	     * Event.EventEmitter.incrementMaxListeners(obj, 'onClose', 2) - adds two max listeners for onClose event of 'obj' target
	     */

	  }, {
	    key: "incrementMaxListeners",
	    value: function incrementMaxListeners() {
	      var _this$destructMaxList3 = this.destructMaxListenersArgs.apply(this, arguments),
	          _this$destructMaxList4 = babelHelpers.slicedToArray(_this$destructMaxList3, 3),
	          target = _this$destructMaxList4[0],
	          eventName = _this$destructMaxList4[1],
	          increment = _this$destructMaxList4[2];

	      return this.addMaxListeners(target, eventName, Math.abs(increment));
	    }
	  }, {
	    key: "decrementMaxListeners",
	    value: function decrementMaxListeners() {
	      var _this$destructMaxList5 = this.destructMaxListenersArgs.apply(this, arguments),
	          _this$destructMaxList6 = babelHelpers.slicedToArray(_this$destructMaxList5, 3),
	          target = _this$destructMaxList6[0],
	          eventName = _this$destructMaxList6[1],
	          increment = _this$destructMaxList6[2];

	      return this.addMaxListeners(target, eventName, -Math.abs(increment));
	    }
	  }, {
	    key: "destructMaxListenersArgs",
	    value: function destructMaxListenersArgs() {
	      var eventName = null;
	      var increment = 1;
	      var target = this.GLOBAL_TARGET;

	      for (var _len6 = arguments.length, args = new Array(_len6), _key6 = 0; _key6 < _len6; _key6++) {
	        args[_key6] = arguments[_key6];
	      }

	      if (args.length === 1) {
	        if (Type.isNumber(args[0])) {
	          increment = args[0];
	        } else if (Type.isString(args[0])) {
	          eventName = args[0];
	        } else {
	          target = args[0];
	        }
	      } else if (args.length === 2) {
	        if (Type.isString(args[0])) {
	          eventName = args[0];
	          increment = args[1];
	        } else if (Type.isString(args[1])) {
	          target = args[0];
	          eventName = args[1];
	        } else {
	          target = args[0];
	          increment = args[1];
	        }
	      } else if (args.length >= 3) {
	        target = args[0];
	        eventName = args[1];
	        increment = args[2];
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      if (eventName !== null && !Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      if (!Type.isNumber(increment)) {
	        throw new TypeError("The value of \"increment\" must be a number.");
	      }

	      return [target, eventName, increment];
	    }
	    /**
	     * Gets listeners list for a specified event
	     * @param {object} target
	     * @param {string} eventName
	     */

	  }, {
	    key: "getListeners",
	    value: function getListeners(target, eventName) {
	      if (Type.isString(target)) {
	        eventName = target;
	        target = this.GLOBAL_TARGET;
	      }

	      if (!Type.isObject(target)) {
	        throw new TypeError("The \"target\" argument must be an object.");
	      }

	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        throw new TypeError("The \"eventName\" argument must be a string.");
	      }

	      var targetInfo = eventStore.get(target);

	      if (!targetInfo) {
	        return new Map();
	      }

	      var fullEventName = this.resolveEventName(eventName, target);
	      return targetInfo.eventsMap.get(fullEventName) || new Map();
	    }
	  }, {
	    key: "registerAliases",
	    value: function registerAliases(aliases) {
	      aliases = this.normalizeAliases(aliases);
	      Object.keys(aliases).forEach(function (alias) {
	        aliasStore.set(alias, {
	          eventName: aliases[alias].eventName,
	          namespace: aliases[alias].namespace
	        });
	      });
	      EventEmitter.mergeEventAliases(aliases);
	    }
	    /**
	     * @private
	     * @param aliases
	     */

	  }, {
	    key: "normalizeAliases",
	    value: function normalizeAliases(aliases) {
	      if (!Type.isPlainObject(aliases)) {
	        throw new TypeError("The \"aliases\" argument must be an object.");
	      }

	      var result = Object.create(null);

	      for (var _alias in aliases) {
	        if (!Type.isStringFilled(_alias)) {
	          throw new TypeError("The alias must be an non-empty string.");
	        }

	        var options = aliases[_alias];

	        if (!options || !Type.isStringFilled(options.eventName) || !Type.isStringFilled(options.namespace)) {
	          throw new TypeError("The alias options must set the \"eventName\" and the \"namespace\".");
	        }

	        _alias = this.normalizeEventName(_alias);
	        result[_alias] = {
	          eventName: options.eventName,
	          namespace: options.namespace
	        };
	      }

	      return result;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "mergeEventAliases",
	    value: function mergeEventAliases(aliases) {
	      var _this4 = this;

	      var globalEvents = eventStore.get(this.GLOBAL_TARGET);

	      if (!globalEvents) {
	        return;
	      }

	      Object.keys(aliases).forEach(function (alias) {
	        var options = aliases[alias];
	        alias = _this4.normalizeEventName(alias);

	        var fullEventName = _this4.makeFullEventName(options.namespace, options.eventName);

	        var aliasListeners = globalEvents.eventsMap.get(alias);

	        if (aliasListeners) {
	          var listeners = globalEvents.eventsMap.get(fullEventName) || new Map();
	          globalEvents.eventsMap.set(fullEventName, new Map([].concat(babelHelpers.toConsumableArray(listeners), babelHelpers.toConsumableArray(aliasListeners))));
	          globalEvents.eventsMap.delete(alias);
	        }

	        var aliasOnceListeners = globalEvents.onceMap.get(alias);

	        if (aliasOnceListeners) {
	          var onceListeners = globalEvents.onceMap.get(fullEventName) || new Map();
	          globalEvents.onceMap.set(fullEventName, new Map([].concat(babelHelpers.toConsumableArray(onceListeners), babelHelpers.toConsumableArray(aliasOnceListeners))));
	          globalEvents.onceMap.delete(alias);
	        }

	        var aliasMaxListeners = globalEvents.eventsMaxListeners.get(alias);

	        if (aliasMaxListeners) {
	          var eventMaxListeners = globalEvents.eventsMaxListeners.get(fullEventName) || 0;
	          globalEvents.eventsMaxListeners.set(fullEventName, Math.max(eventMaxListeners, aliasMaxListeners));
	          globalEvents.eventsMaxListeners.delete(alias);
	        }
	      });
	    }
	    /**
	     * Returns true if the target is an instance of Event.EventEmitter
	     * @param {object} target
	     * @returns {boolean}
	     */

	  }, {
	    key: "isEventEmitter",
	    value: function isEventEmitter(target) {
	      return Type.isObject(target) && target[isEmitterProperty] === true;
	    }
	    /**
	     * @private
	     * @param {string} eventName
	     * @returns {string}
	     */

	  }, {
	    key: "normalizeEventName",
	    value: function normalizeEventName(eventName) {
	      if (!Type.isStringFilled(eventName)) {
	        return '';
	      }

	      return eventName.toLowerCase();
	    }
	    /**
	     * @private
	     * @param eventName
	     * @param target
	     * @param useGlobalNaming
	     * @returns {string}
	     */

	  }, {
	    key: "resolveEventName",
	    value: function resolveEventName(eventName, target) {
	      var useGlobalNaming = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      eventName = this.normalizeEventName(eventName);

	      if (!Type.isStringFilled(eventName)) {
	        return '';
	      }

	      if (this.isEventEmitter(target) && useGlobalNaming !== true) {
	        if (target.getEventNamespace() !== null && eventName.includes('.')) {
	          console.warn("Possible the wrong event name \"".concat(eventName, "\"."));
	        }

	        eventName = target.getFullEventName(eventName);
	      } else if (aliasStore.has(eventName)) {
	        var _aliasStore$get = aliasStore.get(eventName),
	            namespace = _aliasStore$get.namespace,
	            actualEventName = _aliasStore$get.eventName;

	        eventName = this.makeFullEventName(namespace, actualEventName);
	      }

	      return eventName;
	    }
	    /**
	     * @private
	     * @param {string} namespace
	     * @param {string} eventName
	     * @returns {string}
	     */

	  }, {
	    key: "makeFullEventName",
	    value: function makeFullEventName(namespace, eventName) {
	      var fullName = Type.isStringFilled(namespace) ? "".concat(namespace, ":").concat(eventName) : eventName;
	      return Type.isStringFilled(fullName) ? fullName.toLowerCase() : '';
	    }
	  }]);
	  return EventEmitter;
	}();

	babelHelpers.defineProperty(EventEmitter, "GLOBAL_TARGET", globalTarget);
	babelHelpers.defineProperty(EventEmitter, "DEFAULT_MAX_LISTENERS", eventStore.getDefaultMaxListeners());
	babelHelpers.defineProperty(EventEmitter, "sequenceValue", 1);

	var stack = [];
	/**
	 * For compatibility only
	 * @type {boolean}
	 */
	// eslint-disable-next-line

	exports.isReady = false;
	function ready(handler) {
	  switch (document.readyState) {
	    case 'loading':
	      stack.push(handler);
	      break;

	    case 'interactive':
	    case 'complete':
	      if (Type.isFunction(handler)) {
	        handler();
	      }

	      exports.isReady = true;
	      break;

	    default:
	      break;
	  }
	}
	document.addEventListener('readystatechange', function () {
	  if (!exports.isReady) {
	    stack.forEach(ready);
	    stack = [];
	  }
	});

	/**
	 * @memberOf BX
	 */

	var Event = function Event() {
	  babelHelpers.classCallCheck(this, Event);
	};

	babelHelpers.defineProperty(Event, "bind", bind);
	babelHelpers.defineProperty(Event, "bindOnce", bindOnce);
	babelHelpers.defineProperty(Event, "unbind", unbind);
	babelHelpers.defineProperty(Event, "unbindAll", unbindAll);
	babelHelpers.defineProperty(Event, "ready", ready);
	babelHelpers.defineProperty(Event, "EventEmitter", EventEmitter);
	babelHelpers.defineProperty(Event, "BaseEvent", BaseEvent);

	function encodeAttributeValue(value) {
	  if (Type.isPlainObject(value) || Type.isArray(value)) {
	    return JSON.stringify(value);
	  }

	  return Text.encode(Text.decode(value));
	}

	function decodeAttributeValue(value) {
	  if (Type.isString(value)) {
	    var decodedValue = Text.decode(value);
	    var result;

	    try {
	      result = JSON.parse(decodedValue);
	    } catch (e) {
	      result = decodedValue;
	    }

	    if (result === decodedValue) {
	      if (/^[\d.]+[.]?\d+$/.test(result)) {
	        return Number(result);
	      }
	    }

	    if (result === 'true' || result === 'false') {
	      return Boolean(result);
	    }

	    return result;
	  }

	  return value;
	}

	function getPageScroll() {
	  var _document = document,
	      documentElement = _document.documentElement,
	      body = _document.body;
	  var scrollTop = Math.max(window.pageYOffset || 0, documentElement ? documentElement.scrollTop : 0, body ? body.scrollTop : 0);
	  var scrollLeft = Math.max(window.pageXOffset || 0, documentElement ? documentElement.scrollLeft : 0, body ? body.scrollLeft : 0);
	  return {
	    scrollTop: scrollTop,
	    scrollLeft: scrollLeft
	  };
	}

	/**
	 * @memberOf BX
	 */

	var Dom =
	/*#__PURE__*/
	function () {
	  function Dom() {
	    babelHelpers.classCallCheck(this, Dom);
	  }

	  babelHelpers.createClass(Dom, null, [{
	    key: "replace",

	    /**
	     * Replaces old html element to new html element
	     * @param oldElement
	     * @param newElement
	     */
	    value: function replace(oldElement, newElement) {
	      if (Type.isDomNode(oldElement) && Type.isDomNode(newElement)) {
	        if (Type.isDomNode(oldElement.parentNode)) {
	          oldElement.parentNode.replaceChild(newElement, oldElement);
	        }
	      }
	    }
	    /**
	     * Removes element
	     * @param element
	     */

	  }, {
	    key: "remove",
	    value: function remove(element) {
	      if (Type.isDomNode(element) && Type.isDomNode(element.parentNode)) {
	        element.parentNode.removeChild(element);
	      }
	    }
	    /**
	     * Cleans element
	     * @param element
	     */

	  }, {
	    key: "clean",
	    value: function clean(element) {
	      if (Type.isDomNode(element)) {
	        while (element.childNodes.length > 0) {
	          element.removeChild(element.firstChild);
	        }

	        return;
	      }

	      if (Type.isString(element)) {
	        Dom.clean(document.getElementById(element));
	      }
	    }
	    /**
	     * Inserts element before target element
	     * @param current
	     * @param target
	     */

	  }, {
	    key: "insertBefore",
	    value: function insertBefore(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        if (Type.isDomNode(target.parentNode)) {
	          target.parentNode.insertBefore(current, target);
	        }
	      }
	    }
	    /**
	     * Inserts element after target element
	     * @param current
	     * @param target
	     */

	  }, {
	    key: "insertAfter",
	    value: function insertAfter(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        if (Type.isDomNode(target.parentNode)) {
	          var parent = target.parentNode;

	          if (Type.isDomNode(target.nextSibling)) {
	            parent.insertBefore(current, target.nextSibling);
	            return;
	          }

	          parent.appendChild(current);
	        }
	      }
	    }
	    /**
	     * Appends element to target element
	     * @param current
	     * @param target
	     */

	  }, {
	    key: "append",
	    value: function append(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        target.appendChild(current);
	      }
	    }
	    /**
	     * Prepends element to target element
	     * @param current
	     * @param target
	     */

	  }, {
	    key: "prepend",
	    value: function prepend(current, target) {
	      if (Type.isDomNode(current) && Type.isDomNode(target)) {
	        if (Type.isDomNode(target.firstChild)) {
	          target.insertBefore(current, target.firstChild);
	          return;
	        }

	        Dom.append(current, target);
	      }
	    }
	    /**
	     * Checks that element contains class name or class names
	     * @param element
	     * @param className
	     * @return {Boolean}
	     */

	  }, {
	    key: "hasClass",
	    value: function hasClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          var preparedClassName = className.trim();

	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              return preparedClassName.split(' ').every(function (name) {
	                return Dom.hasClass(element, name);
	              });
	            }

	            if ('classList' in element) {
	              return element.classList.contains(preparedClassName);
	            }

	            if (Type.isObject(element.className) && Type.isString(element.className.baseVal)) {
	              return element.getAttribute('class').split(' ').some(function (name) {
	                return name === preparedClassName;
	              });
	            }
	          }
	        }

	        if (Type.isArray(className) && className.length > 0) {
	          return className.every(function (name) {
	            return Dom.hasClass(element, name);
	          });
	        }
	      }

	      return false;
	    }
	    /**
	     * Adds class name
	     * @param element
	     * @param className
	     */

	  }, {
	    key: "addClass",
	    value: function addClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          var preparedClassName = className.trim();

	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              Dom.addClass(element, preparedClassName.split(' '));
	              return;
	            }

	            if ('classList' in element) {
	              element.classList.add(preparedClassName);
	              return;
	            }

	            if (Type.isObject(element.className) && Type.isString(element.className.baseVal)) {
	              if (element.className.baseVal === '') {
	                element.className.baseVal = preparedClassName;
	                return;
	              }

	              var names = element.className.baseVal.split(' ');

	              if (!names.includes(preparedClassName)) {
	                names.push(preparedClassName);
	                element.className.baseVal = names.join(' ').trim();
	                return;
	              }
	            }

	            return;
	          }
	        }

	        if (Type.isArray(className)) {
	          className.forEach(function (name) {
	            return Dom.addClass(element, name);
	          });
	        }
	      }
	    }
	    /**
	     * Removes class name
	     * @param element
	     * @param className
	     */

	  }, {
	    key: "removeClass",
	    value: function removeClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          var preparedClassName = className.trim();

	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              Dom.removeClass(element, preparedClassName.split(' '));
	              return;
	            }

	            if ('classList' in element) {
	              element.classList.remove(preparedClassName);
	              return;
	            }

	            if (Type.isObject(element.className) && Type.isString(element.className.baseVal)) {
	              var names = element.className.baseVal.split(' ').filter(function (name) {
	                return name !== preparedClassName;
	              });
	              element.className.baseVal = names.join(' ');
	              return;
	            }
	          }
	        }

	        if (Type.isArray(className)) {
	          className.forEach(function (name) {
	            return Dom.removeClass(element, name);
	          });
	        }
	      }
	    }
	    /**
	     * Toggles class name
	     * @param element
	     * @param className
	     */

	  }, {
	    key: "toggleClass",
	    value: function toggleClass(element, className) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(className)) {
	          var preparedClassName = className.trim();

	          if (preparedClassName.length > 0) {
	            if (preparedClassName.includes(' ')) {
	              Dom.toggleClass(element, preparedClassName.split(' '));
	              return;
	            }

	            element.classList.toggle(preparedClassName);
	            return;
	          }
	        }

	        if (Type.isArray(className)) {
	          className.forEach(function (name) {
	            return Dom.toggleClass(element, name);
	          });
	        }
	      }
	    }
	    /**
	     * Styles element
	     */

	  }, {
	    key: "style",
	    value: function style(element, prop, value) {
	      if (Type.isElementNode(element)) {
	        if (Type.isNull(prop)) {
	          element.removeAttribute('style');
	          return element;
	        }

	        if (Type.isPlainObject(prop)) {
	          Object.entries(prop).forEach(function (item) {
	            var _item = babelHelpers.slicedToArray(item, 2),
	                currentKey = _item[0],
	                currentValue = _item[1];

	            Dom.style(element, currentKey, currentValue);
	          });
	          return element;
	        }

	        if (Type.isString(prop)) {
	          if (Type.isUndefined(value) && element.nodeType !== Node.DOCUMENT_NODE) {
	            var computedStyle = getComputedStyle(element);

	            if (prop in computedStyle) {
	              return computedStyle[prop];
	            }

	            return computedStyle.getPropertyValue(prop);
	          }

	          if (Type.isNull(value) || value === '' || value === 'null') {
	            // eslint-disable-next-line
	            element.style[prop] = '';
	            return element;
	          }

	          if (Type.isString(value) || Type.isNumber(value)) {
	            // eslint-disable-next-line
	            element.style[prop] = value;
	            return element;
	          }
	        }
	      }

	      return null;
	    }
	    /**
	     * Adjusts element
	     * @param target
	     * @param data
	     * @return {*}
	     */

	  }, {
	    key: "adjust",
	    value: function adjust(target) {
	      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (!target.nodeType) {
	        return null;
	      }

	      var element = target;

	      if (target.nodeType === Node.DOCUMENT_NODE) {
	        element = target.body;
	      }

	      if (Type.isPlainObject(data)) {
	        if (Type.isPlainObject(data.attrs)) {
	          Object.keys(data.attrs).forEach(function (key) {
	            if (key === 'class' || key.toLowerCase() === 'classname') {
	              element.className = data.attrs[key];
	              return;
	            } // eslint-disable-next-line


	            if (data.attrs[key] == '') {
	              element.removeAttribute(key);
	              return;
	            }

	            element.setAttribute(key, data.attrs[key]);
	          });
	        }

	        if (Type.isPlainObject(data.style)) {
	          Dom.style(element, data.style);
	        }

	        if (Type.isPlainObject(data.props)) {
	          Object.keys(data.props).forEach(function (key) {
	            element[key] = data.props[key];
	          });
	        }

	        if (Type.isPlainObject(data.events)) {
	          Object.keys(data.events).forEach(function (key) {
	            Event.bind(element, key, data.events[key]);
	          });
	        }

	        if (Type.isPlainObject(data.dataset)) {
	          Object.keys(data.dataset).forEach(function (key) {
	            element.dataset[key] = data.dataset[key];
	          });
	        }

	        if (Type.isString(data.children)) {
	          data.children = [data.children];
	        }

	        if (Type.isArray(data.children) && data.children.length > 0) {
	          data.children.forEach(function (item) {
	            if (Type.isDomNode(item)) {
	              Dom.append(item, element);
	            }

	            if (Type.isString(item)) {
	              element.innerHTML += item;
	            }
	          });
	          return element;
	        }

	        if ('text' in data && !Type.isNil(data.text)) {
	          element.innerText = data.text;
	          return element;
	        }

	        if ('html' in data && !Type.isNil(data.html)) {
	          element.innerHTML = data.html;
	        }
	      }

	      return element;
	    }
	    /**
	     * Creates element
	     * @param tag
	     * @param data
	     * @param context
	     * @return {HTMLElement|HTMLBodyElement}
	     */

	  }, {
	    key: "create",
	    value: function create(tag) {
	      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : document;
	      var tagName = tag;
	      var options = data;

	      if (Type.isObjectLike(tag)) {
	        options = tag;
	        tagName = tag.tag;
	      }

	      return Dom.adjust(context.createElement(tagName), options);
	    }
	    /**
	     * Shows element
	     * @param element
	     */

	  }, {
	    key: "show",
	    value: function show(element) {
	      if (Type.isDomNode(element)) {
	        // eslint-disable-next-line
	        element.hidden = false;
	      }
	    }
	    /**
	     * Hides element
	     * @param element
	     */

	  }, {
	    key: "hide",
	    value: function hide(element) {
	      if (Type.isDomNode(element)) {
	        // eslint-disable-next-line
	        element.hidden = true;
	      }
	    }
	    /**
	     * Checks that element is shown
	     * @param element
	     * @return {*|boolean}
	     */

	  }, {
	    key: "isShown",
	    value: function isShown(element) {
	      return Type.isDomNode(element) && !element.hidden && element.style.getPropertyValue('display') !== 'none';
	    }
	    /**
	     * Toggles element visibility
	     * @param element
	     */

	  }, {
	    key: "toggle",
	    value: function toggle(element) {
	      if (Type.isDomNode(element)) {
	        if (Dom.isShown(element)) {
	          Dom.hide(element);
	        } else {
	          Dom.show(element);
	        }
	      }
	    }
	    /**
	     * Gets element position relative page
	     * @param {HTMLElement} element
	     * @return {DOMRect}
	     */

	  }, {
	    key: "getPosition",
	    value: function getPosition(element) {
	      if (Type.isDomNode(element)) {
	        var elementRect = element.getBoundingClientRect();

	        var _getPageScroll = getPageScroll(),
	            scrollLeft = _getPageScroll.scrollLeft,
	            scrollTop = _getPageScroll.scrollTop;

	        return new DOMRect(elementRect.left + scrollLeft, elementRect.top + scrollTop, elementRect.width, elementRect.height);
	      }

	      return new DOMRect();
	    }
	    /**
	     * Gets element position relative specified element position
	     * @param {HTMLElement} element
	     * @param {HTMLElement} relationElement
	     * @return {DOMRect}
	     */

	  }, {
	    key: "getRelativePosition",
	    value: function getRelativePosition(element, relationElement) {
	      if (Type.isDomNode(element) && Type.isDomNode(relationElement)) {
	        var elementPosition = Dom.getPosition(element);
	        var relationElementPosition = Dom.getPosition(relationElement);
	        return new DOMRect(elementPosition.left - relationElementPosition.left, elementPosition.top - relationElementPosition.top, elementPosition.width, elementPosition.height);
	      }

	      return new DOMRect();
	    }
	  }, {
	    key: "attr",
	    value: function attr(element, _attr, value) {
	      if (Type.isElementNode(element)) {
	        if (Type.isString(_attr)) {
	          if (!Type.isNil(value)) {
	            return element.setAttribute(_attr, encodeAttributeValue(value));
	          }

	          if (Type.isNull(value)) {
	            return element.removeAttribute(_attr);
	          }

	          return decodeAttributeValue(element.getAttribute(_attr));
	        }

	        if (Type.isPlainObject(_attr)) {
	          return Object.entries(_attr).forEach(function (_ref) {
	            var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	                attrKey = _ref2[0],
	                attrValue = _ref2[1];

	            Dom.attr(element, attrKey, attrValue);
	          });
	        }
	      }

	      return null;
	    }
	  }]);
	  return Dom;
	}();

	var UA = navigator.userAgent.toLowerCase();
	/**
	 * @memberOf BX
	 */

	var Browser =
	/*#__PURE__*/
	function () {
	  function Browser() {
	    babelHelpers.classCallCheck(this, Browser);
	  }

	  babelHelpers.createClass(Browser, null, [{
	    key: "isOpera",
	    value: function isOpera() {
	      return UA.includes('opera');
	    }
	  }, {
	    key: "isIE",
	    value: function isIE() {
	      return 'attachEvent' in document && !Browser.isOpera();
	    }
	  }, {
	    key: "isIE6",
	    value: function isIE6() {
	      return UA.includes('msie 6');
	    }
	  }, {
	    key: "isIE7",
	    value: function isIE7() {
	      return UA.includes('msie 7');
	    }
	  }, {
	    key: "isIE8",
	    value: function isIE8() {
	      return UA.includes('msie 8');
	    }
	  }, {
	    key: "isIE9",
	    value: function isIE9() {
	      return 'documentMode' in document && document.documentMode >= 9;
	    }
	  }, {
	    key: "isIE10",
	    value: function isIE10() {
	      return 'documentMode' in document && document.documentMode >= 10;
	    }
	  }, {
	    key: "isSafari",
	    value: function isSafari() {
	      return UA.includes('webkit');
	    }
	  }, {
	    key: "isFirefox",
	    value: function isFirefox() {
	      return UA.includes('firefox');
	    }
	  }, {
	    key: "isChrome",
	    value: function isChrome() {
	      return UA.includes('chrome');
	    }
	  }, {
	    key: "detectIEVersion",
	    value: function detectIEVersion() {
	      if (Browser.isOpera() || Browser.isSafari() || Browser.isFirefox() || Browser.isChrome()) {
	        return -1;
	      }

	      var rv = -1;

	      if (!!window.MSStream && !window.ActiveXObject && 'ActiveXObject' in window) {
	        rv = 11;
	      } else if (Browser.isIE10()) {
	        rv = 10;
	      } else if (Browser.isIE9()) {
	        rv = 9;
	      } else if (Browser.isIE()) {
	        rv = 8;
	      }

	      if (rv === -1 || rv === 8) {
	        if (navigator.appName === 'Microsoft Internet Explorer') {
	          var re = new RegExp('MSIE ([0-9]+[.0-9]*)');
	          var res = navigator.userAgent.match(re);

	          if (Type.isArrayLike(res) && res.length > 0) {
	            rv = parseFloat(res[1]);
	          }
	        }

	        if (navigator.appName === 'Netscape') {
	          // Alternative check for IE 11
	          rv = 11;

	          var _re = new RegExp('Trident/.*rv:([0-9]+[.0-9]*)');

	          if (_re.exec(navigator.userAgent) != null) {
	            var _res = navigator.userAgent.match(_re);

	            if (Type.isArrayLike(_res) && _res.length > 0) {
	              rv = parseFloat(_res[1]);
	            }
	          }
	        }
	      }

	      return rv;
	    }
	  }, {
	    key: "isIE11",
	    value: function isIE11() {
	      return Browser.detectIEVersion() >= 11;
	    }
	  }, {
	    key: "isMac",
	    value: function isMac() {
	      return UA.includes('macintosh');
	    }
	  }, {
	    key: "isAndroid",
	    value: function isAndroid() {
	      return UA.includes('android');
	    }
	  }, {
	    key: "isIPad",
	    value: function isIPad() {
	      return UA.includes('ipad;');
	    }
	  }, {
	    key: "isIPhone",
	    value: function isIPhone() {
	      return UA.includes('iphone;');
	    }
	  }, {
	    key: "isIOS",
	    value: function isIOS() {
	      return Browser.isIPad() || Browser.isIPhone();
	    }
	  }, {
	    key: "isMobile",
	    value: function isMobile() {
	      return Browser.isIPhone() || Browser.isIPad() || Browser.isAndroid() || UA.includes('mobile') || UA.includes('touch');
	    }
	  }, {
	    key: "isRetina",
	    value: function isRetina() {
	      return window.devicePixelRatio && window.devicePixelRatio >= 2;
	    }
	  }, {
	    key: "isDoctype",
	    value: function isDoctype(target) {
	      var doc = target || document;

	      if (doc.compatMode) {
	        return doc.compatMode === 'CSS1Compat';
	      }

	      return doc.documentElement && doc.documentElement.clientHeight;
	    }
	  }, {
	    key: "isLocalStorageSupported",
	    value: function isLocalStorageSupported() {
	      try {
	        localStorage.setItem('test', 'test');
	        localStorage.removeItem('test');
	        return true;
	      } catch (e) {
	        return false;
	      }
	    }
	  }, {
	    key: "addGlobalClass",
	    value: function addGlobalClass() {
	      var globalClass = 'bx-core';

	      if (Dom.hasClass(document.documentElement, globalClass)) {
	        return;
	      }

	      if (Browser.isIOS()) {
	        globalClass += ' bx-ios';
	      } else if (Browser.isMac()) {
	        globalClass += ' bx-mac';
	      } else if (Browser.isAndroid()) {
	        globalClass += ' bx-android';
	      }

	      globalClass += Browser.isMobile() ? ' bx-touch' : ' bx-no-touch';
	      globalClass += Browser.isRetina() ? ' bx-retina' : ' bx-no-retina';
	      var ieVersion = -1;

	      if (/AppleWebKit/.test(navigator.userAgent)) {
	        globalClass += ' bx-chrome';
	      } else if (Browser.detectIEVersion() > 0) {
	        ieVersion = Browser.detectIEVersion();
	        globalClass += " bx-ie bx-ie".concat(ieVersion);

	        if (ieVersion > 7 && ieVersion < 10 && !Browser.isDoctype()) {
	          globalClass += ' bx-quirks';
	        }
	      } else if (/Opera/.test(navigator.userAgent)) {
	        globalClass += ' bx-opera';
	      } else if (/Gecko/.test(navigator.userAgent)) {
	        globalClass += ' bx-firefox';
	      }

	      Dom.addClass(document.documentElement, globalClass);
	    }
	  }, {
	    key: "detectAndroidVersion",
	    value: function detectAndroidVersion() {
	      var re = new RegExp('Android ([0-9]+[.0-9]*)');

	      if (re.exec(navigator.userAgent) != null) {
	        var res = navigator.userAgent.match(re);

	        if (Type.isArrayLike(res) && res.length > 0) {
	          return parseFloat(res[1]);
	        }
	      }

	      return 0;
	    }
	  }, {
	    key: "isPropertySupported",
	    value: function isPropertySupported(jsProperty, returnCSSName) {
	      if (jsProperty === '') {
	        return false;
	      }

	      function getCssName(propertyName) {
	        return propertyName.replace(/([A-Z])/g, function () {
	          for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	            args[_key] = arguments[_key];
	          }

	          return "-".concat(args[1].toLowerCase());
	        });
	      }

	      function getJsName(cssName) {
	        var reg = /(\\-([a-z]))/g;

	        if (reg.test(cssName)) {
	          return cssName.replace(reg, function () {
	            for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	              args[_key2] = arguments[_key2];
	            }

	            return args[2].toUpperCase();
	          });
	        }

	        return cssName;
	      }

	      var property = jsProperty.includes('-') ? getJsName(jsProperty) : jsProperty;
	      var bReturnCSSName = !!returnCSSName;
	      var ucProperty = property.charAt(0).toUpperCase() + property.slice(1);
	      var props = ['Webkit', 'Moz', 'O', 'ms'].join("".concat(ucProperty, " "));
	      var properties = "".concat(property, " ").concat(props, " ").concat(ucProperty).split(' ');
	      var obj = document.body || document.documentElement;

	      for (var i = 0; i < properties.length; i += 1) {
	        var prop = properties[i];

	        if (obj && 'style' in obj && prop in obj.style) {
	          var lowerProp = prop.substr(0, prop.length - property.length).toLowerCase();
	          var prefix = prop === property ? '' : "-".concat(lowerProp, "-");
	          return bReturnCSSName ? prefix + getCssName(property) : prop;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "addGlobalFeatures",
	    value: function addGlobalFeatures(features) {
	      if (!Type.isArray(features)) {
	        return;
	      }

	      var classNames = [];

	      for (var i = 0; i < features.length; i += 1) {
	        var support = !!Browser.isPropertySupported(features[i]);
	        classNames.push("bx-".concat(support ? '' : 'no-').concat(features[i].toLowerCase()));
	      }

	      Dom.addClass(document.documentElement, classNames.join(' '));
	    }
	  }]);
	  return Browser;
	}();

	var Cookie =
	/*#__PURE__*/
	function () {
	  function Cookie() {
	    babelHelpers.classCallCheck(this, Cookie);
	  }

	  babelHelpers.createClass(Cookie, null, [{
	    key: "getList",

	    /**
	     * Gets cookies list for current domain
	     * @return {object}
	     */
	    value: function getList() {
	      return document.cookie.split(';').map(function (item) {
	        return item.split('=');
	      }).map(function (item) {
	        return item.map(function (subItem) {
	          return subItem.trim();
	        });
	      }).reduce(function (acc, item) {
	        var _item = babelHelpers.slicedToArray(item, 2),
	            key = _item[0],
	            value = _item[1];

	        acc[decodeURIComponent(key)] = decodeURIComponent(value);
	        return acc;
	      }, {});
	    }
	    /**
	     * Gets cookie value
	     * @param {string} name
	     * @return {*}
	     */

	  }, {
	    key: "get",
	    value: function get(name) {
	      var cookiesList = Cookie.getList();

	      if (name in cookiesList) {
	        return cookiesList[name];
	      }

	      return undefined;
	    }
	    /**
	     * Sets cookie
	     * @param {string} name
	     * @param {*} value
	     * @param {object} [options]
	     */

	  }, {
	    key: "set",
	    value: function set(name, value) {
	      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      var attributes = babelHelpers.objectSpread({
	        expires: ''
	      }, options);

	      if (Type.isNumber(attributes.expires)) {
	        var now = +new Date();
	        var days = attributes.expires;
	        var dayInMs = 864e+5;
	        attributes.expires = new Date(now + days * dayInMs);
	      }

	      if (Type.isDate(attributes.expires)) {
	        attributes.expires = attributes.expires.toUTCString();
	      }

	      var safeName = decodeURIComponent(String(name)).replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent).replace(/[()]/g, escape);
	      var safeValue = encodeURIComponent(String(value)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
	      var stringifiedAttributes = Object.keys(attributes).reduce(function (acc, key) {
	        var attributeValue = attributes[key];

	        if (!attributeValue) {
	          return acc;
	        }

	        if (attributeValue === true) {
	          return "".concat(acc, "; ").concat(key);
	        }
	        /**
	         * Considers RFC 6265 section 5.2:
	         * ...
	         * 3. If the remaining unparsed-attributes contains a %x3B (';')
	         * character:
	         * Consume the characters of the unparsed-attributes up to,
	         * not including, the first %x3B (';') character.
	         */


	        return "".concat(acc, "; ").concat(key, "=").concat(attributeValue.split(';')[0]);
	      }, '');
	      document.cookie = "".concat(safeName, "=").concat(safeValue).concat(stringifiedAttributes);
	    }
	    /**
	     * Removes cookie
	     * @param {string} name
	     * @param {object} [options]
	     */

	  }, {
	    key: "remove",
	    value: function remove(name) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      Cookie.set(name, '', babelHelpers.objectSpread({}, options, {
	        expires: -1
	      }));
	    }
	  }]);
	  return Cookie;
	}();

	function objectToFormData(source) {
	  var formData = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : new FormData();
	  var pre = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

	  if (Type.isUndefined(source)) {
	    return formData;
	  }

	  if (Type.isNull(source)) {
	    formData.append(pre, '');
	  } else if (Type.isArray(source)) {
	    if (!source.length) {
	      var _key = "".concat(pre, "[]");

	      formData.append(_key, '');
	    } else {
	      source.forEach(function (value, index) {
	        var key = "".concat(pre, "[").concat(index, "]");
	        objectToFormData(value, formData, key);
	      });
	    }
	  } else if (Type.isDate(source)) {
	    formData.append(pre, source.toISOString());
	  } else if (Type.isObject(source) && !Type.isFile(source) && !Type.isBlob(source)) {
	    Object.keys(source).forEach(function (property) {
	      var value = source[property];
	      var preparedProperty = property;

	      if (Type.isArray(value)) {
	        while (property.length > 2 && property.lastIndexOf('[]') === property.length - 2) {
	          preparedProperty = property.substring(0, property.length - 2);
	        }
	      }

	      var key = pre ? "".concat(pre, "[").concat(preparedProperty, "]") : preparedProperty;
	      objectToFormData(value, formData, key);
	    });
	  } else {
	    formData.append(pre, source);
	  }

	  return formData;
	}

	var Data =
	/*#__PURE__*/
	function () {
	  function Data() {
	    babelHelpers.classCallCheck(this, Data);
	  }

	  babelHelpers.createClass(Data, null, [{
	    key: "convertObjectToFormData",

	    /**
	     * Converts object to FormData
	     * @param source
	     * @return {FormData}
	     */
	    value: function convertObjectToFormData(source) {
	      return objectToFormData(source);
	    }
	  }]);
	  return Data;
	}();

	/**
	 * @memberOf BX
	 */

	var Http = function Http() {
	  babelHelpers.classCallCheck(this, Http);
	};

	babelHelpers.defineProperty(Http, "Cookie", Cookie);
	babelHelpers.defineProperty(Http, "Data", Data);

	function message(value) {
	  if (Type.isString(value)) {
	    if (Type.isNil(message[value])) {
	      // eslint-disable-next-line
	      EventEmitter.emit('onBXMessageNotFound', new BaseEvent({
	        compatData: [value]
	      }));

	      if (Type.isNil(message[value])) {
	        Runtime.debug("message undefined: ".concat(value));
	        message[value] = '';
	      }
	    }
	  }

	  if (Type.isPlainObject(value)) {
	    Object.keys(value).forEach(function (key) {
	      message[key] = value[key];
	    });
	  }

	  return message[value];
	}

	if (!Type.isNil(window.BX) && Type.isFunction(window.BX.message)) {
	  Object.keys(window.BX.message).forEach(function (key) {
	    message(babelHelpers.defineProperty({}, key, window.BX.message[key]));
	  });
	}

	/**
	 * Implements interface for works with language messages
	 * @memberOf BX
	 */

	var Loc =
	/*#__PURE__*/
	function () {
	  function Loc() {
	    babelHelpers.classCallCheck(this, Loc);
	  }

	  babelHelpers.createClass(Loc, null, [{
	    key: "getMessage",

	    /**
	     * Gets message by id
	     * @param {string} messageId
	     * @return {?string}
	     */
	    value: function getMessage(messageId) {
	      return message(messageId);
	    }
	    /**
	     * Sets message or messages
	     * @param {string | Object<string, string>} id
	     * @param {string} [value]
	     */

	  }, {
	    key: "setMessage",
	    value: function setMessage(id, value) {
	      if (Type.isString(id) && Type.isString(value)) {
	        message(babelHelpers.defineProperty({}, id, value));
	      }

	      if (Type.isObject(id)) {
	        message(id);
	      }
	    }
	  }]);
	  return Loc;
	}();

	var handlers = new Map();
	var children = new Map();

	var getUid = function () {
	  var incremental = 0;
	  return function () {
	    incremental += 1;
	    return incremental;
	  };
	}();

	function bindAll(element, handlersMap) {
	  handlersMap.forEach(function (handler, key) {
	    var currentElement = element.querySelector("[".concat(key, "]"));

	    if (currentElement) {
	      currentElement.removeAttribute(key);
	      var event = key.replace(/-(.*)/, '');
	      Event.bind(currentElement, event, handler);
	      handlers.delete(key);
	    }
	  });
	}

	function replaceChild(element, childrenMap) {
	  childrenMap.forEach(function (item, id) {
	    var currentElement = element.getElementById(id);

	    if (currentElement) {
	      Dom.replace(currentElement, item);
	      children.delete(id);
	    }
	  });
	}

	function render(sections) {
	  var eventAttrRe = /[ |\t]on(\w+)="$/;
	  var uselessSymbolsRe = /[\r\n\t]/g;

	  for (var _len = arguments.length, substitutions = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	    substitutions[_key - 1] = arguments[_key];
	  }

	  var html = substitutions.reduce(function (acc, item, index) {
	    var preparedAcc = acc; // Process event handlers

	    var matches = acc.match(eventAttrRe);

	    if (matches && Type.isFunction(item)) {
	      var eventName = matches[1].replace(/=['|"]/, '');
	      var attrName = "".concat(eventName, "-").concat(getUid());
	      var attribute = "".concat(attrName, "=\"");
	      preparedAcc = preparedAcc.replace(eventAttrRe, " ".concat(attribute));
	      handlers.set(attrName, item);
	      preparedAcc += sections[index + 1].replace(uselessSymbolsRe, ' ').replace(/  +/g, ' ');
	      return preparedAcc;
	    } // Process element


	    if (Type.isDomNode(item)) {
	      var childKey = "tmp___".concat(getUid());
	      children.set(childKey, item);
	      preparedAcc += "<span id=\"".concat(childKey, "\"> </span>");
	      preparedAcc += sections[index + 1];
	      return preparedAcc;
	    } // Process array


	    if (Type.isArray(item)) {
	      babelHelpers.toConsumableArray(item).forEach(function (currentElement) {
	        if (Type.isDomNode(currentElement)) {
	          var _childKey = "tmp___".concat(getUid());

	          children.set(_childKey, currentElement);
	          preparedAcc += "<span id=\"".concat(_childKey, "\"> </span>");
	        }
	      });
	      preparedAcc += sections[index + 1];
	      return preparedAcc;
	    }

	    return preparedAcc + item + sections[index + 1];
	  }, sections[0]);
	  var lowercaseHtml = html.trim().toLowerCase();

	  if (lowercaseHtml.startsWith('<!doctype') || lowercaseHtml.startsWith('<html')) {
	    var doc = document.implementation.createHTMLDocument('');
	    doc.documentElement.innerHTML = html;
	    replaceChild(doc, children);
	    bindAll(doc, handlers);
	    handlers.clear();
	    return doc;
	  }

	  var parser = new DOMParser();
	  var parsedDocument = parser.parseFromString(html, 'text/html');
	  replaceChild(parsedDocument, children);
	  bindAll(parsedDocument, handlers);

	  if (parsedDocument.head.children.length && parsedDocument.body.children.length) {
	    return parsedDocument;
	  }

	  if (parsedDocument.body.children.length === 1) {
	    var _parsedDocument$body$ = babelHelpers.slicedToArray(parsedDocument.body.children, 1),
	        el = _parsedDocument$body$[0];

	    Dom.remove(el);
	    return el;
	  }

	  if (parsedDocument.body.children.length > 1) {
	    return babelHelpers.toConsumableArray(parsedDocument.body.children).map(function (item) {
	      Dom.remove(item);
	      return item;
	    });
	  }

	  if (parsedDocument.body.children.length === 0) {
	    if (parsedDocument.head.children.length === 1) {
	      var _parsedDocument$head$ = babelHelpers.slicedToArray(parsedDocument.head.children, 1),
	          _el = _parsedDocument$head$[0];

	      Dom.remove(_el);
	      return _el;
	    }

	    if (parsedDocument.head.children.length > 1) {
	      return babelHelpers.toConsumableArray(parsedDocument.head.children).map(function (item) {
	        Dom.remove(item);
	        return item;
	      });
	    }
	  }

	  return false;
	}

	function parseProps(sections) {
	  for (var _len = arguments.length, substitutions = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	    substitutions[_key - 1] = arguments[_key];
	  }

	  return substitutions.reduce(function (acc, item, index) {
	    var nextSectionIndex = index + 1;

	    if (!Type.isPlainObject(item) && !Type.isArray(item)) {
	      return acc + item + sections[nextSectionIndex];
	    }

	    return "".concat(acc, "__s").concat(index).concat(sections[nextSectionIndex]);
	  }, sections[0]).replace(/[\r\t]/gm, '').split(';\n').map(function (item) {
	    return item.replace(/\n/, '');
	  }).reduce(function (acc, item) {
	    if (item !== '') {
	      var matches = item.match(/^[\w-. ]+:/);
	      var splitted = item.split(/^[\w-. ]+:/);

	      var _key2 = matches[0].replace(':', '').trim();

	      var value = splitted[1].trim();
	      var substitutionPlaceholderExp = /^__s\d+/;

	      if (substitutionPlaceholderExp.test(value)) {
	        acc[_key2] = substitutions[value.replace('__s', '')];
	        return acc;
	      }

	      acc[_key2] = value;
	    }

	    return acc;
	  }, {});
	}
	/**
	 * @memberOf BX
	 */


	var Tag =
	/*#__PURE__*/
	function () {
	  function Tag() {
	    babelHelpers.classCallCheck(this, Tag);
	  }

	  babelHelpers.createClass(Tag, null, [{
	    key: "safe",

	    /**
	     * Encodes all substitutions
	     * @param sections
	     * @param substitutions
	     * @return {string}
	     */
	    value: function safe(sections) {
	      for (var _len2 = arguments.length, substitutions = new Array(_len2 > 1 ? _len2 - 1 : 0), _key3 = 1; _key3 < _len2; _key3++) {
	        substitutions[_key3 - 1] = arguments[_key3];
	      }

	      return substitutions.reduce(function (acc, item, index) {
	        return acc + Text.encode(item) + sections[index + 1];
	      }, sections[0]);
	    }
	    /**
	     * Decodes all substitutions
	     * @param sections
	     * @param substitutions
	     * @return {string}
	     */

	  }, {
	    key: "unsafe",
	    value: function unsafe(sections) {
	      for (var _len3 = arguments.length, substitutions = new Array(_len3 > 1 ? _len3 - 1 : 0), _key4 = 1; _key4 < _len3; _key4++) {
	        substitutions[_key4 - 1] = arguments[_key4];
	      }

	      return substitutions.reduce(function (acc, item, index) {
	        return acc + Text.decode(item) + sections[index + 1];
	      }, sections[0]);
	    }
	    /**
	     * Adds styles to specified element
	     * @param {HTMLElement} element
	     * @return {Function}
	     */

	  }, {
	    key: "style",
	    value: function style(element) {
	      if (!Type.isDomNode(element)) {
	        throw new Error('element is not HTMLElement');
	      }

	      return function styleTagHandler() {
	        Dom.style(element, parseProps.apply(void 0, arguments));
	      };
	    }
	    /**
	     * Replace all messages identifiers to real messages
	     * @param sections
	     * @param substitutions
	     * @return {string}
	     */

	  }, {
	    key: "message",
	    value: function message(sections) {
	      for (var _len4 = arguments.length, substitutions = new Array(_len4 > 1 ? _len4 - 1 : 0), _key5 = 1; _key5 < _len4; _key5++) {
	        substitutions[_key5 - 1] = arguments[_key5];
	      }

	      return substitutions.reduce(function (acc, item, index) {
	        return acc + Loc.getMessage(item) + sections[index + 1];
	      }, sections[0]);
	    }
	  }, {
	    key: "attrs",

	    /**
	     * Adds attributes to specified element
	     * @param element
	     * @return {Function}
	     */
	    value: function attrs(element) {
	      if (!Type.isDomNode(element)) {
	        throw new Error('element is not HTMLElement');
	      }

	      return function attrsTagHandler() {
	        Dom.attr(element, parseProps.apply(void 0, arguments));
	      };
	    }
	  }]);
	  return Tag;
	}();

	babelHelpers.defineProperty(Tag, "render", render);
	babelHelpers.defineProperty(Tag, "attr", Tag.attrs);

	function getParser(format) {
	  switch (format) {
	    case 'index':
	      return function (sourceKey, value, accumulator) {
	        var result = /\[(\w*)\]$/.exec(sourceKey);
	        var key = sourceKey.replace(/\[\w*\]$/, '');

	        if (Type.isNil(result)) {
	          accumulator[key] = value;
	          return;
	        }

	        if (Type.isUndefined(accumulator[key])) {
	          accumulator[key] = {};
	        }

	        accumulator[key][result[1]] = value;
	      };

	    case 'bracket':
	      return function (sourceKey, value, accumulator) {
	        var result = /(\[\])$/.exec(sourceKey);
	        var key = sourceKey.replace(/\[\]$/, '');

	        if (Type.isNil(result)) {
	          accumulator[key] = value;
	          return;
	        }

	        if (Type.isUndefined(accumulator[key])) {
	          accumulator[key] = [value];
	          return;
	        }

	        accumulator[key] = [].concat(accumulator[key], value);
	      };

	    default:
	      return function (sourceKey, value, accumulator) {
	        var key = sourceKey.replace(/\[\]$/, '');
	        accumulator[key] = value;
	      };
	  }
	}

	function getKeyFormat(key) {
	  if (/^\w+\[([\w]+)\]$/.test(key)) {
	    return 'index';
	  }

	  if (/^\w+\[\]$/.test(key)) {
	    return 'bracket';
	  }

	  return 'default';
	}

	function parseQuery(input) {
	  if (!Type.isString(input)) {
	    return {};
	  }

	  var url = input.trim().replace(/^[?#&]/, '');

	  if (!url) {
	    return {};
	  }

	  return url.split('&').reduce(function (acc, param) {
	    var _param$replace$split = param.replace(/\+/g, ' ').split('='),
	        _param$replace$split2 = babelHelpers.slicedToArray(_param$replace$split, 2),
	        key = _param$replace$split2[0],
	        value = _param$replace$split2[1];

	    var keyFormat = getKeyFormat(key);
	    var formatter = getParser(keyFormat);
	    formatter(key, value, acc);
	    return acc;
	  }, {});
	}

	var urlExp = /^((\w+):)?(\/\/((\w+)?(:(\w+))?@)?([^\/\?:]+)(:(\d+))?)?(\/?([^\/\?#][^\?#]*)?)?(\?([^#]+))?(#(\w*))?/;
	function parseUrl(url) {
	  var result = url.match(urlExp);

	  if (Type.isArray(result)) {
	    var queryParams = parseQuery(result[14]);
	    return {
	      useShort: /^\/\//.test(url),
	      href: result[0] || '',
	      schema: result[2] || '',
	      host: result[8] || '',
	      port: result[10] || '',
	      path: result[11] || '',
	      query: result[14] || '',
	      queryParams: queryParams,
	      hash: result[16] || '',
	      username: result[5] || '',
	      password: result[7] || '',
	      origin: result[8] || ''
	    };
	  }

	  return {};
	}

	function buildQueryString() {
	  var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	  var queryString = Object.keys(params).reduce(function (acc, key) {
	    if (Type.isArray(params[key])) {
	      params[key].forEach(function (paramValue) {
	        acc.push("".concat(key, "[]=").concat(paramValue));
	      }, '');
	    }

	    if (Type.isPlainObject(params[key])) {
	      Object.keys(params[key]).forEach(function (paramIndex) {
	        acc.push("".concat(key, "[").concat(paramIndex, "]=").concat(params[key][paramIndex]));
	      }, '');
	    }

	    if (!Type.isObject(params[key]) && !Type.isArray(params[key])) {
	      acc.push("".concat(key, "=").concat(params[key]));
	    }

	    return acc;
	  }, []).join('&');

	  if (queryString.length > 0) {
	    return "?".concat(queryString);
	  }

	  return queryString;
	}

	function prepareParamValue(value) {
	  if (Type.isArray(value)) {
	    return value.map(function (item) {
	      return String(item);
	    });
	  }

	  if (Type.isPlainObject(value)) {
	    return babelHelpers.objectSpread({}, value);
	  }

	  return String(value);
	}

	var map = new WeakMap();
	/**
	 * Implements interface for works with URI
	 * @memberOf BX
	 */

	var Uri =
	/*#__PURE__*/
	function () {
	  babelHelpers.createClass(Uri, null, [{
	    key: "addParam",
	    value: function addParam(url) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      return new Uri(url).setQueryParams(params).toString();
	    }
	  }, {
	    key: "removeParam",
	    value: function removeParam(url, params) {
	      var _ref;

	      var removableParams = Type.isArray(params) ? params : [params];
	      return (_ref = new Uri(url)).removeQueryParam.apply(_ref, babelHelpers.toConsumableArray(removableParams)).toString();
	    }
	  }]);

	  function Uri() {
	    var url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	    babelHelpers.classCallCheck(this, Uri);
	    map.set(this, parseUrl(url));
	  }
	  /**
	   * Gets schema
	   * @return {?string}
	   */


	  babelHelpers.createClass(Uri, [{
	    key: "getSchema",
	    value: function getSchema() {
	      return map.get(this).schema;
	    }
	    /**
	     * Sets schema
	     * @param {string} schema
	     * @return {Uri}
	     */

	  }, {
	    key: "setSchema",
	    value: function setSchema(schema) {
	      map.get(this).schema = String(schema);
	      return this;
	    }
	    /**
	     * Gets host
	     * @return {?string}
	     */

	  }, {
	    key: "getHost",
	    value: function getHost() {
	      return map.get(this).host;
	    }
	    /**
	     * Sets host
	     * @param {string} host
	     * @return {Uri}
	     */

	  }, {
	    key: "setHost",
	    value: function setHost(host) {
	      map.get(this).host = String(host);
	      return this;
	    }
	    /**
	     * Gets port
	     * @return {?string}
	     */

	  }, {
	    key: "getPort",
	    value: function getPort() {
	      return map.get(this).port;
	    }
	    /**
	     * Sets port
	     * @param {String | Number} port
	     * @return {Uri}
	     */

	  }, {
	    key: "setPort",
	    value: function setPort(port) {
	      map.get(this).port = String(port);
	      return this;
	    }
	    /**
	     * Gets path
	     * @return {?string}
	     */

	  }, {
	    key: "getPath",
	    value: function getPath() {
	      return map.get(this).path;
	    }
	    /**
	     * Sets path
	     * @param {string} path
	     * @return {Uri}
	     */

	  }, {
	    key: "setPath",
	    value: function setPath(path) {
	      if (!/^\//.test(path)) {
	        map.get(this).path = "/".concat(String(path));
	        return this;
	      }

	      map.get(this).path = String(path);
	      return this;
	    }
	    /**
	     * Gets query
	     * @return {?string}
	     */

	  }, {
	    key: "getQuery",
	    value: function getQuery() {
	      return buildQueryString(map.get(this).queryParams);
	    }
	    /**
	     * Gets query param value by name
	     * @param {string} key
	     * @return {?string}
	     */

	  }, {
	    key: "getQueryParam",
	    value: function getQueryParam(key) {
	      var params = this.getQueryParams();

	      if (key in params) {
	        return params[key];
	      }

	      return null;
	    }
	    /**
	     * Sets query param
	     * @param {string} key
	     * @param [value]
	     * @return {Uri}
	     */

	  }, {
	    key: "setQueryParam",
	    value: function setQueryParam(key) {
	      var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	      map.get(this).queryParams[key] = prepareParamValue(value);
	      return this;
	    }
	    /**
	     * Gets query params
	     * @return {Object<string, any>}
	     */

	  }, {
	    key: "getQueryParams",
	    value: function getQueryParams() {
	      return babelHelpers.objectSpread({}, map.get(this).queryParams);
	    }
	    /**
	     * Sets query params
	     * @param {Object<string, any>} params
	     * @return {Uri}
	     */

	  }, {
	    key: "setQueryParams",
	    value: function setQueryParams() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var currentParams = this.getQueryParams();
	      var newParams = babelHelpers.objectSpread({}, currentParams, params);
	      Object.keys(newParams).forEach(function (key) {
	        newParams[key] = prepareParamValue(newParams[key]);
	      });
	      map.get(this).queryParams = newParams;
	      return this;
	    }
	    /**
	     * Removes query params by name
	     * @param keys
	     * @return {Uri}
	     */

	  }, {
	    key: "removeQueryParam",
	    value: function removeQueryParam() {
	      var currentParams = babelHelpers.objectSpread({}, map.get(this).queryParams);

	      for (var _len = arguments.length, keys = new Array(_len), _key = 0; _key < _len; _key++) {
	        keys[_key] = arguments[_key];
	      }

	      keys.forEach(function (key) {
	        if (key in currentParams) {
	          delete currentParams[key];
	        }
	      });
	      map.get(this).queryParams = currentParams;
	      return this;
	    }
	    /**
	     * Gets fragment
	     * @return {?string}
	     */

	  }, {
	    key: "getFragment",
	    value: function getFragment() {
	      return map.get(this).hash;
	    }
	    /**
	     * Sets fragment
	     * @param {string} hash
	     * @return {Uri}
	     */

	  }, {
	    key: "setFragment",
	    value: function setFragment(hash) {
	      map.get(this).hash = String(hash);
	      return this;
	    }
	    /**
	     * Serializes URI
	     * @return {Object}
	     */

	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var serialized = babelHelpers.objectSpread({}, map.get(this));
	      serialized.href = this.toString();
	      return serialized;
	    }
	    /**
	     * Gets URI string
	     * @return {string}
	     */

	  }, {
	    key: "toString",
	    value: function toString() {
	      var data = babelHelpers.objectSpread({}, map.get(this));
	      var protocol = data.schema ? "".concat(data.schema, "://") : '';

	      if (data.useShort) {
	        protocol = '//';
	      }

	      var port = function () {
	        if (Type.isString(data.port) && !['', '80'].includes(data.port)) {
	          return ":".concat(data.port);
	        }

	        return '';
	      }();

	      var host = this.getHost();
	      var path = this.getPath();
	      var query = buildQueryString(data.queryParams);
	      var hash = data.hash ? "#".concat(data.hash) : '';
	      return "".concat(host ? protocol : '').concat(host).concat(host ? port : '').concat(path).concat(query).concat(hash);
	    }
	  }]);
	  return Uri;
	}();

	/**
	 * @memberOf BX
	 */
	var Validation =
	/*#__PURE__*/
	function () {
	  function Validation() {
	    babelHelpers.classCallCheck(this, Validation);
	  }

	  babelHelpers.createClass(Validation, null, [{
	    key: "isEmail",

	    /**
	     * Checks that value is valid email
	     * @param value
	     * @return {boolean}
	     */
	    value: function isEmail(value) {
	      var exp = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
	      return exp.test(String(value).toLowerCase());
	    }
	  }]);
	  return Validation;
	}();

	var BaseCache =
	/*#__PURE__*/
	function () {
	  function BaseCache() {
	    babelHelpers.classCallCheck(this, BaseCache);
	    babelHelpers.defineProperty(this, "storage", new Map());
	  }

	  babelHelpers.createClass(BaseCache, [{
	    key: "get",

	    /**
	     * Gets cached value or default value
	     */
	    value: function get(key, defaultValue) {
	      if (!this.storage.has(key)) {
	        if (Type.isFunction(defaultValue)) {
	          return defaultValue();
	        }

	        if (!Type.isUndefined(defaultValue)) {
	          return defaultValue;
	        }
	      }

	      return this.storage.get(key);
	    }
	    /**
	     * Sets cache entry
	     */

	  }, {
	    key: "set",
	    value: function set(key, value) {
	      this.storage.set(key, value);
	    }
	    /**
	     * Deletes cache entry
	     */

	  }, {
	    key: "delete",
	    value: function _delete(key) {
	      this.storage.delete(key);
	    }
	    /**
	     * Checks that storage contains entry with specified key
	     */

	  }, {
	    key: "has",
	    value: function has(key) {
	      return this.storage.has(key);
	    }
	    /**
	     * Gets cached value if exists,
	     */

	  }, {
	    key: "remember",
	    value: function remember(key, defaultValue) {
	      if (!this.storage.has(key)) {
	        if (Type.isFunction(defaultValue)) {
	          this.storage.set(key, defaultValue());
	        } else if (!Type.isUndefined(defaultValue)) {
	          this.storage.set(key, defaultValue);
	        }
	      }

	      return this.storage.get(key);
	    }
	    /**
	     * Gets storage size
	     */

	  }, {
	    key: "size",
	    value: function size() {
	      return this.storage.size;
	    }
	    /**
	     * Gets storage keys
	     */

	  }, {
	    key: "keys",
	    value: function keys() {
	      return babelHelpers.toConsumableArray(this.storage.keys());
	    }
	    /**
	     * Gets storage values
	     */

	  }, {
	    key: "values",
	    value: function values() {
	      return babelHelpers.toConsumableArray(this.storage.values());
	    }
	  }]);
	  return BaseCache;
	}();

	var MemoryCache =
	/*#__PURE__*/
	function (_BaseCache) {
	  babelHelpers.inherits(MemoryCache, _BaseCache);

	  function MemoryCache() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, MemoryCache);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(MemoryCache)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "storage", new Map());
	    return _this;
	  }

	  return MemoryCache;
	}(BaseCache);

	var LsStorage =
	/*#__PURE__*/
	function () {
	  function LsStorage() {
	    babelHelpers.classCallCheck(this, LsStorage);
	    babelHelpers.defineProperty(this, "stackKey", 'BX.Cache.Storage.LsStorage.stack');
	    babelHelpers.defineProperty(this, "stack", null);
	  }

	  babelHelpers.createClass(LsStorage, [{
	    key: "getStack",

	    /**
	     * @private
	     */
	    value: function getStack() {
	      if (Type.isPlainObject(this.stack)) {
	        return this.stack;
	      }

	      var stack = localStorage.getItem(this.stackKey);

	      if (Type.isString(stack) && stack !== '') {
	        var parsedStack = JSON.parse(stack);

	        if (Type.isPlainObject(parsedStack)) {
	          this.stack = parsedStack;
	          return this.stack;
	        }
	      }

	      this.stack = {};
	      return this.stack;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "saveStack",
	    value: function saveStack() {
	      if (Type.isPlainObject(this.stack)) {
	        var preparedStack = JSON.stringify(this.stack);
	        localStorage.setItem(this.stackKey, preparedStack);
	      }
	    }
	  }, {
	    key: "get",
	    value: function get(key) {
	      var stack = this.getStack();
	      return stack[key];
	    }
	  }, {
	    key: "set",
	    value: function set(key, value) {
	      var stack = this.getStack();
	      stack[key] = value;
	      this.saveStack();
	    }
	  }, {
	    key: "delete",
	    value: function _delete(key) {
	      var stack = this.getStack();

	      if (key in stack) {
	        delete stack[key];
	      }
	    }
	  }, {
	    key: "has",
	    value: function has(key) {
	      var stack = this.getStack();
	      return key in stack;
	    }
	  }, {
	    key: "keys",
	    value: function keys() {
	      var stack = this.getStack();
	      return Object.keys(stack);
	    }
	  }, {
	    key: "values",
	    value: function values() {
	      var stack = this.getStack();
	      return Object.values(stack);
	    }
	  }, {
	    key: "size",
	    get: function get() {
	      var stack = this.getStack();
	      return Object.keys(stack).length;
	    }
	  }]);
	  return LsStorage;
	}();

	var LocalStorageCache =
	/*#__PURE__*/
	function (_BaseCache) {
	  babelHelpers.inherits(LocalStorageCache, _BaseCache);

	  function LocalStorageCache() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, LocalStorageCache);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(LocalStorageCache)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "storage", new LsStorage());
	    return _this;
	  }

	  return LocalStorageCache;
	}(BaseCache);

	/**
	 * @memberOf BX
	 */

	var Cache = function Cache() {
	  babelHelpers.classCallCheck(this, Cache);
	};

	babelHelpers.defineProperty(Cache, "MemoryCache", MemoryCache);
	babelHelpers.defineProperty(Cache, "LocalStorageCache", LocalStorageCache);

	function getElement(element) {
	  if (Type.isString(element)) {
	    return document.getElementById(element);
	  }

	  return element;
	}

	function getWindow(element) {
	  if (Type.isElementNode(element)) {
	    return element.ownerDocument.parentWindow || element.ownerDocument.defaultView || window;
	  }

	  if (Type.isDomNode(element)) {
	    return element.parentWindow || element.defaultView || window;
	  }

	  return window;
	}

	/* eslint-disable prefer-rest-params */

	var getClass = Reflection.getClass,
	    namespace = Reflection.namespace;
	var message$1 = message;
	/**
	 * @memberOf BX
	 */

	var replace = Dom.replace,
	    remove = Dom.remove,
	    clean = Dom.clean,
	    insertBefore = Dom.insertBefore,
	    insertAfter = Dom.insertAfter,
	    append = Dom.append,
	    prepend = Dom.prepend,
	    style = Dom.style,
	    adjust = Dom.adjust,
	    create = Dom.create,
	    isShown = Dom.isShown;
	var addClass = function addClass() {
	  Dom.addClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge([], Array.from(arguments), [getElement(arguments[0])])));
	};
	var removeClass = function removeClass() {
	  Dom.removeClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge(Array.from(arguments), [getElement(arguments[0])])));
	};
	var hasClass = function hasClass() {
	  return Dom.hasClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge(Array.from(arguments), [getElement(arguments[0])])));
	};
	var toggleClass = function toggleClass() {
	  Dom.toggleClass.apply(Dom, babelHelpers.toConsumableArray(Runtime.merge(Array.from(arguments), [getElement(arguments[0])])));
	};
	var cleanNode = function cleanNode(element) {
	  var removeElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	  var currentElement = getElement(element);

	  if (Type.isDomNode(currentElement)) {
	    Dom.clean(currentElement);

	    if (removeElement) {
	      Dom.remove(currentElement);
	      return currentElement;
	    }
	  }

	  return currentElement;
	};
	var getCookie = Http.Cookie.get;
	var setCookie = function setCookie(name, value) {
	  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	  var attributes = babelHelpers.objectSpread({}, options);

	  if (Type.isNumber(attributes.expires)) {
	    attributes.expires /= 3600 * 24;
	  }

	  Http.Cookie.set(name, value, attributes);
	};
	var bind$1 = Event.bind,
	    unbind$1 = Event.unbind,
	    unbindAll$1 = Event.unbindAll,
	    bindOnce$1 = Event.bindOnce,
	    ready$1 = Event.ready;
	var debugEnableFlag = debugState,
	    debugStatus = isDebugEnabled,
	    debug$1 = debug;
	var debugEnable = function debugEnable(value) {
	  if (value) {
	    enableDebug();
	  } else {
	    disableDebug();
	  }
	};
	var clone$1 = Runtime.clone,
	    loadExt = Runtime.loadExtension,
	    debounce = Runtime.debounce,
	    throttle = Runtime.throttle,
	    html = Runtime.html; // BX.type
	var type = babelHelpers.objectSpread({}, Object.getOwnPropertyNames(Type).filter(function (key) {
	  return !['name', 'length', 'prototype', 'caller', 'arguments'].includes(key);
	}).reduce(function (acc, key) {
	  acc[key] = Type[key];
	  return acc;
	}, {}), {
	  isNotEmptyString: function isNotEmptyString(value) {
	    return Type.isString(value) && value !== '';
	  },
	  isNotEmptyObject: function isNotEmptyObject(value) {
	    return Type.isObjectLike(value) && Object.keys(value).length > 0;
	  },
	  isMapKey: Type.isObject,
	  stringToInt: function stringToInt(value) {
	    var parsed = parseInt(value);
	    return !Number.isNaN(parsed) ? parsed : 0;
	  }
	}); // BX.browser

	var browser = {
	  IsOpera: Browser.isOpera,
	  IsIE: Browser.isIE,
	  IsIE6: Browser.isIE6,
	  IsIE7: Browser.isIE7,
	  IsIE8: Browser.isIE8,
	  IsIE9: Browser.isIE9,
	  IsIE10: Browser.isIE10,
	  IsIE11: Browser.isIE11,
	  IsSafari: Browser.isSafari,
	  IsFirefox: Browser.isFirefox,
	  IsChrome: Browser.isChrome,
	  DetectIeVersion: Browser.detectIEVersion,
	  IsMac: Browser.isMac,
	  IsAndroid: Browser.isAndroid,
	  isIPad: Browser.isIPad,
	  isIPhone: Browser.isIPhone,
	  IsIOS: Browser.isIOS,
	  IsMobile: Browser.isMobile,
	  isRetina: Browser.isRetina,
	  IsDoctype: Browser.isDoctype,
	  SupportLocalStorage: Browser.isLocalStorageSupported,
	  addGlobalClass: Browser.addGlobalClass,
	  DetectAndroidVersion: Browser.detectAndroidVersion,
	  isPropertySupported: Browser.isPropertySupported,
	  addGlobalFeatures: Browser.addGlobalFeatures
	}; // eslint-disable-next-line

	var ajax = window.BX ? window.BX.ajax : function () {};
	function GetWindowScrollSize() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  return {
	    scrollWidth: doc.documentElement.scrollWidth,
	    scrollHeight: doc.documentElement.scrollHeight
	  };
	}
	function GetWindowScrollPos() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  var win = getWindow(doc);
	  return {
	    scrollLeft: win.pageXOffset,
	    scrollTop: win.pageYOffset
	  };
	}
	function GetWindowInnerSize() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  var win = getWindow(doc);
	  return {
	    innerWidth: win.innerWidth,
	    innerHeight: win.innerHeight
	  };
	}
	function GetWindowSize() {
	  var doc = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : document;
	  return babelHelpers.objectSpread({}, GetWindowInnerSize(doc), GetWindowScrollPos(doc), GetWindowScrollSize(doc));
	}
	function GetContext(node) {
	  return getWindow(node);
	}
	function pos(element) {
	  var relative = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	  if (!element) {
	    return new DOMRect().toJSON();
	  }

	  if (element.ownerDocument === document && !relative) {
	    var clientRect = element.getBoundingClientRect();
	    var root = document.documentElement;
	    var _document = document,
	        body = _document.body;
	    return {
	      top: Math.round(clientRect.top + (root.scrollTop || body.scrollTop)),
	      left: Math.round(clientRect.left + (root.scrollLeft || body.scrollLeft)),
	      width: Math.round(clientRect.right - clientRect.left),
	      height: Math.round(clientRect.bottom - clientRect.top),
	      right: Math.round(clientRect.right + (root.scrollLeft || body.scrollLeft)),
	      bottom: Math.round(clientRect.bottom + (root.scrollTop || body.scrollTop))
	    };
	  }

	  var x = 0;
	  var y = 0;
	  var w = element.offsetWidth;
	  var h = element.offsetHeight;
	  var first = true; // eslint-disable-next-line no-param-reassign

	  for (; element != null; element = element.offsetParent) {
	    if (!first && relative && BX.is_relative(element)) {
	      break;
	    }

	    x += element.offsetLeft;
	    y += element.offsetTop;

	    if (first) {
	      first = false; // eslint-disable-next-line no-continue

	      continue;
	    }

	    x += Text.toNumber(Dom.style(element, 'border-left-width'));
	    y += Text.toNumber(Dom.style(element, 'border-top-width'));
	  }

	  return new DOMRect(x, y, w, h).toJSON();
	}
	function addCustomEvent(eventObject, eventName, eventHandler) {
	  if (Type.isString(eventObject)) {
	    eventHandler = eventName;
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  if (eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  if (!Type.isObject(eventObject)) {
	    console.error('The "eventObject" argument must be an object. Received type ' + babelHelpers.typeof(eventObject) + '.');
	    return;
	  }

	  if (!Type.isStringFilled(eventName)) {
	    console.error('The "eventName" argument must be a string.');
	    return;
	  }

	  if (!Type.isFunction(eventHandler)) {
	    console.error('The "eventHandler" argument must be a function. Received type ' + babelHelpers.typeof(eventHandler) + '.');
	    return;
	  }

	  eventName = eventName.toLowerCase();
	  EventEmitter.subscribe(eventObject, eventName, eventHandler, {
	    compatMode: true,
	    useGlobalNaming: true
	  });
	}
	function onCustomEvent(eventObject, eventName, eventParams, secureParams) {
	  if (Type.isString(eventObject)) {
	    secureParams = eventParams;
	    eventParams = eventName;
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  if (!Type.isObject(eventObject) || eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  if (!eventParams) {
	    eventParams = [];
	  }

	  eventName = eventName.toLowerCase();
	  var event = new BaseEvent();
	  event.setData(eventParams);
	  event.setCompatData(eventParams);
	  EventEmitter.emit(eventObject, eventName, event, {
	    cloneData: secureParams === true,
	    useGlobalNaming: true
	  });
	}
	function removeCustomEvent(eventObject, eventName, eventHandler) {
	  if (Type.isString(eventObject)) {
	    eventHandler = eventName;
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  if (!Type.isFunction(eventHandler)) {
	    console.error('The "eventHandler" argument must be a function. Received type ' + babelHelpers.typeof(eventHandler) + '.');
	    return;
	  }

	  if (eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  eventName = eventName.toLowerCase();
	  EventEmitter.unsubscribe(eventObject, eventName, eventHandler, {
	    useGlobalNaming: true
	  });
	}
	function removeAllCustomEvents(eventObject, eventName) {
	  if (Type.isString(eventObject)) {
	    eventName = eventObject;
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  if (eventObject === window) {
	    eventObject = EventEmitter.GLOBAL_TARGET;
	  }

	  eventName = eventName.toLowerCase();
	  EventEmitter.unsubscribeAll(eventObject, eventName, {
	    useGlobalNaming: true
	  });
	}

	//import './internal/bx';

	exports.Type = Type;
	exports.Reflection = Reflection;
	exports.Text = Text;
	exports.Dom = Dom;
	exports.Browser = Browser;
	exports.Event = Event;
	exports.Http = Http;
	exports.Runtime = Runtime;
	exports.Loc = Loc;
	exports.Tag = Tag;
	exports.Uri = Uri;
	exports.Validation = Validation;
	exports.Cache = Cache;
	exports.BaseError = BaseError;
	exports.getClass = getClass;
	exports.namespace = namespace;
	exports.message = message$1;
	exports.replace = replace;
	exports.remove = remove;
	exports.clean = clean;
	exports.insertBefore = insertBefore;
	exports.insertAfter = insertAfter;
	exports.append = append;
	exports.prepend = prepend;
	exports.style = style;
	exports.adjust = adjust;
	exports.create = create;
	exports.isShown = isShown;
	exports.addClass = addClass;
	exports.removeClass = removeClass;
	exports.hasClass = hasClass;
	exports.toggleClass = toggleClass;
	exports.cleanNode = cleanNode;
	exports.getCookie = getCookie;
	exports.setCookie = setCookie;
	exports.bind = bind$1;
	exports.unbind = unbind$1;
	exports.unbindAll = unbindAll$1;
	exports.bindOnce = bindOnce$1;
	exports.ready = ready$1;
	exports.debugEnableFlag = debugEnableFlag;
	exports.debugStatus = debugStatus;
	exports.debug = debug$1;
	exports.debugEnable = debugEnable;
	exports.clone = clone$1;
	exports.loadExt = loadExt;
	exports.debounce = debounce;
	exports.throttle = throttle;
	exports.html = html;
	exports.type = type;
	exports.browser = browser;
	exports.ajax = ajax;
	exports.GetWindowScrollSize = GetWindowScrollSize;
	exports.GetWindowScrollPos = GetWindowScrollPos;
	exports.GetWindowInnerSize = GetWindowInnerSize;
	exports.GetWindowSize = GetWindowSize;
	exports.GetContext = GetContext;
	exports.pos = pos;
	exports.addCustomEvent = addCustomEvent;
	exports.onCustomEvent = onCustomEvent;
	exports.removeCustomEvent = removeCustomEvent;
	exports.removeAllCustomEvents = removeAllCustomEvents;

}((this.BX = this.BX || {})));
//# sourceMappingURL=main.core.minimal.bundle.js.map
