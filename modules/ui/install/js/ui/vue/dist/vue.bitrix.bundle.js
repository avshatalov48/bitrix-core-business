(function (exports,main_polyfill_core,ui_vue_vendor_v2) {
	'use strict';

	/**
	 * Bitrix Vue wrapper
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */

	var BitrixVue =
	/*#__PURE__*/
	function () {
	  function BitrixVue() {
	    babelHelpers.classCallCheck(this, BitrixVue);
	    this._components = {};
	    this._mutations = {};
	    this._clones = {};
	    this.event = new ui_vue_vendor_v2.VueVendorV2({});
	  }
	  /**
	   * Create new Vue instance
	   *
	   * @param {Object} params - definition
	   *
	   * @see https://vuejs.org/v2/guide/
	   */


	  babelHelpers.createClass(BitrixVue, [{
	    key: "create",
	    value: function create(params) {
	      return new ui_vue_vendor_v2.VueVendorV2(params);
	    }
	    /**
	     * Register Vue component
	     *
	     * @param {String} id
	     * @param {Object} params
	     *
	     * @see https://vuejs.org/v2/guide/components.html
	     */

	  }, {
	    key: "component",
	    value: function component(id, params) {
	      this._components[id] = Object.assign({}, params);

	      if (typeof this._clones[id] !== 'undefined') {
	        this._registerCloneComponent(id);
	      }

	      return ui_vue_vendor_v2.VueVendorV2.component(id, this._getComponentParamsWithMutation(id, this._mutations[id]));
	    }
	    /**
	     * Modify Vue component
	     *
	     * @param {String} id
	     * @param {Object} mutations
	     *
	     * @returns {Function} - function for remove this modification
	     */

	  }, {
	    key: "mutateComponent",
	    value: function mutateComponent(id, mutations) {
	      var _this = this;

	      if (typeof this._mutations[id] === 'undefined') {
	        this._mutations[id] = [];
	      }

	      this._mutations[id].push(mutations);

	      if (typeof this._components[id] !== 'undefined') {
	        this.component(id, this._components[id]);
	      }

	      return function () {
	        _this._mutations[id] = _this._mutations[id].filter(function (element) {
	          return element !== mutations;
	        });
	      };
	    }
	    /**
	     * Clone Vue component
	     *
	     * @param {string} id
	     * @param {string} sourceId
	     * @param {object} mutations
	     * @returns {boolean}
	     */

	  }, {
	    key: "cloneComponent",
	    value: function cloneComponent(id, sourceId, mutations) {
	      if (typeof this._clones[sourceId] === 'undefined') {
	        this._clones[sourceId] = {};
	      }

	      this._clones[sourceId][id] = {
	        id: id,
	        sourceId: sourceId,
	        mutations: mutations
	      };

	      if (typeof this._components[sourceId] !== 'undefined') {
	        this._registerCloneComponent(sourceId, id);
	      }

	      return true;
	    }
	  }, {
	    key: "isComponent",
	    value: function isComponent(id) {
	      return typeof this._components[id] !== 'undefined';
	    }
	    /**
	     * Create a "subclass" of the base Vue constructor.
	     *
	     * @param options
	     * @returns {*}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-extend
	     */

	  }, {
	    key: "extend",
	    value: function extend(options) {
	      return ui_vue_vendor_v2.VueVendorV2.extend(options);
	    }
	    /**
	     *	Defer the callback to be executed after the next DOM update cycle. Use it immediately after you’ve changed some data to wait for the DOM update.
	     *
	     * @param {Function} callback
	     * @param {Object} context
	     * @returns {Promise|void}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-nextTick
	     */

	  }, {
	    key: "nextTick",
	    value: function nextTick(callback, context) {
	      return ui_vue_vendor_v2.VueVendorV2.nextTick(callback, context);
	    }
	    /**
	     * Adds a property to a reactive object, ensuring the new property is also reactive, so triggers view updates.
	     *
	     * @param {Object|Array} target
	     * @param {String|Number} key
	     * @param {*} value
	     * @returns {*}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-set
	     */

	  }, {
	    key: "set",
	    value: function set(target, key, value) {
	      return ui_vue_vendor_v2.VueVendorV2.set(target, key, value);
	    }
	    /**
	     * Delete a property on an object. If the object is reactive, ensure the deletion triggers view updates.
	     *
	     * @param {Object|Array} target
	     * @param {String|Number} key
	     * @returns {*}
	     */

	  }, {
	    key: "delete",
	    value: function _delete(target, key) {
	      return ui_vue_vendor_v2.VueVendorV2.delete(target, key);
	    }
	    /**
	     * Register or retrieve a global directive.
	     *
	     * @param {String} id
	     * @param {Object|Function} definition
	     * @returns {*}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-directive
	     */

	  }, {
	    key: "directive",
	    value: function directive(id, definition) {
	      return ui_vue_vendor_v2.VueVendorV2.directive(id, definition);
	    }
	    /**
	     * Register or retrieve a global filter.
	     *
	     * @param id
	     * @param definition
	     * @returns {*}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-filter
	     */

	  }, {
	    key: "filter",
	    value: function filter(id, definition) {
	      return ui_vue_vendor_v2.VueVendorV2.filter(id, definition);
	    }
	    /**
	     * Install a Vue.js plugin.
	     *
	     * @param {Object|Function} plugin
	     * @returns {*}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-use
	     */

	  }, {
	    key: "use",
	    value: function use(plugin) {
	      return ui_vue_vendor_v2.VueVendorV2.use(plugin);
	    }
	    /**
	     * Apply a mixin globally, which affects every Vue instance created afterwards.
	     *
	     * @param {Object} mixin
	     * @returns {*|Function|Object}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-mixin
	     */

	  }, {
	    key: "mixin",
	    value: function mixin(_mixin) {
	      return ui_vue_vendor_v2.VueVendorV2.mixin(_mixin);
	    }
	    /**
	     * Make an object reactive. Internally, Vue uses this on the object returned by the data function.
	     *
	     * @param object
	     * @returns {*}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-observable
	     */

	  }, {
	    key: "observable",
	    value: function observable(object) {
	      return ui_vue_vendor_v2.VueVendorV2.observable(object);
	    }
	    /**
	     * Compiles a template string into a render function.
	     *
	     * @param template
	     * @returns {*}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-compile
	     */

	  }, {
	    key: "compile",
	    value: function compile(template) {
	      return ui_vue_vendor_v2.VueVendorV2.compile(template);
	    }
	    /**
	     * Provides the installed version of Vue as a string.
	     *
	     * @returns {String}
	     *
	     * @see https://vuejs.org/v2/api/#Vue-version
	     */

	  }, {
	    key: "version",
	    value: function version() {
	      return ui_vue_vendor_v2.VueVendorV2.version;
	    }
	    /**
	     *
	     * @param {String} phrasePrefix
	     * @param {Object|null} phrases
	     * @returns {ReadonlyArray<any>}
	     */

	  }, {
	    key: "getFilteredPhrases",
	    value: function getFilteredPhrases(phrasePrefix) {
	      var phrases = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var result = {};

	      if (!phrases && typeof BX.message !== 'undefined') {
	        phrases = BX.message;
	      }

	      if (Array.isArray(phrasePrefix)) {
	        var _loop = function _loop(message) {
	          if (!phrases.hasOwnProperty(message)) {
	            return "continue";
	          }

	          if (!phrasePrefix.find(function (element) {
	            return message.toString().startsWith(element);
	          })) {
	            return "continue";
	          }

	          result[message] = phrases[message];
	        };

	        for (var message in phrases) {
	          var _ret = _loop(message);

	          if (_ret === "continue") continue;
	        }
	      } else {
	        for (var _message in phrases) {
	          if (!phrases.hasOwnProperty(_message)) {
	            continue;
	          }

	          if (!_message.startsWith(phrasePrefix)) {
	            continue;
	          }

	          result[_message] = phrases[_message];
	        }
	      }

	      return Object.freeze(result);
	    }
	    /**
	     * Return component params with mutation
	     *
	     * @param {String} componentId
	     * @param {Object} mutations
	     * @returns {null|Object}
	     *
	     * @private
	     */

	  }, {
	    key: "_getComponentParamsWithMutation",
	    value: function _getComponentParamsWithMutation(componentId, mutations) {
	      var _this2 = this;

	      if (typeof this._components[componentId] === 'undefined') {
	        return null;
	      }

	      var componentParams = Object.assign({}, this._components[componentId]);

	      if (typeof mutations === 'undefined') {
	        return componentParams;
	      }

	      mutations.forEach(function (mutation) {
	        componentParams = _this2._applyMutation(_this2._cloneObjectWithoutDuplicateFunction(componentParams, mutation), mutation);
	      });
	      return componentParams;
	    }
	    /**
	     * Register clone of components
	     *
	     * @param {String} sourceId
	     * @param {String|null} [id]
	     *
	     * @private
	     */

	  }, {
	    key: "_registerCloneComponent",
	    value: function _registerCloneComponent(sourceId) {
	      var _this3 = this;

	      var id = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var components = [];

	      if (id) {
	        if (typeof this._clones[sourceId][id] !== 'undefined') {
	          components.push(this._clones[sourceId][id]);
	        }
	      } else {
	        for (var cloneId in this._clones[sourceId]) {
	          if (!this._clones[sourceId].hasOwnProperty(cloneId)) {
	            continue;
	          }

	          components.push(this._clones[sourceId][cloneId]);
	        }
	      }

	      components.forEach(function (element) {
	        var mutations = [];

	        if (typeof _this3._mutations[element.sourceId] !== 'undefined') {
	          mutations = mutations.concat(_this3._mutations[element.sourceId]);
	        }

	        mutations.push(element.mutations);

	        var componentParams = _this3._getComponentParamsWithMutation(element.sourceId, mutations);

	        if (!componentParams) {
	          return false;
	        }

	        _this3.component(element.id, componentParams);
	      });
	    }
	    /**
	     * Clone object without duplicate function for apply mutation
	     *
	     * @param objectParams
	     * @param mutation
	     * @param level
	     * @private
	     */

	  }, {
	    key: "_cloneObjectWithoutDuplicateFunction",
	    value: function _cloneObjectWithoutDuplicateFunction() {
	      var objectParams = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var mutation = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var level = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
	      var object = {};

	      for (var param in objectParams) {
	        if (!objectParams.hasOwnProperty(param)) {
	          continue;
	        }

	        if (typeof objectParams[param] === 'string') {
	          object[param] = objectParams[param];
	        } else if (Object.prototype.toString.call(objectParams[param]) === '[object Array]') {
	          object[param] = [].concat(objectParams[param]);
	        } else if (babelHelpers.typeof(objectParams[param]) === 'object') {
	          if (objectParams[param] === null) {
	            object[param] = null;
	          } else if (babelHelpers.typeof(mutation[param]) === 'object') {
	            object[param] = this._cloneObjectWithoutDuplicateFunction(objectParams[param], mutation[param], level + 1);
	          } else {
	            object[param] = Object.assign({}, objectParams[param]);
	          }
	        } else if (typeof objectParams[param] === 'function') {
	          if (typeof mutation[param] !== 'function') {
	            object[param] = objectParams[param];
	          } else if (level > 1) {
	            object['parent' + param[0].toUpperCase() + param.substr(1)] = objectParams[param];
	          } else {
	            if (typeof object['methods'] === 'undefined') {
	              object['methods'] = {};
	            }

	            object['methods']['parent' + param[0].toUpperCase() + param.substr(1)] = objectParams[param];

	            if (typeof objectParams['methods'] === 'undefined') {
	              objectParams['methods'] = {};
	            }

	            objectParams['methods']['parent' + param[0].toUpperCase() + param.substr(1)] = objectParams[param];
	          }
	        } else if (typeof objectParams[param] !== 'undefined') {
	          object[param] = objectParams[param];
	        }
	      }

	      return object;
	    }
	    /**
	     * Apply mutation
	     *
	     * @param clonedObject
	     * @param mutation
	     * @private
	     */

	  }, {
	    key: "_applyMutation",
	    value: function _applyMutation() {
	      var clonedObject = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var mutation = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var object = Object.assign({}, clonedObject);

	      for (var param in mutation) {
	        if (!mutation.hasOwnProperty(param)) {
	          continue;
	        }

	        if (typeof mutation[param] === 'string') {
	          if (typeof object[param] === 'string') {
	            object[param] = mutation[param].replace("#PARENT_".concat(param.toUpperCase(), "#"), object[param]);
	          } else {
	            object[param] = mutation[param].replace("#PARENT_".concat(param.toUpperCase(), "#"), '');
	          }
	        } else if (Object.prototype.toString.call(mutation[param]) === '[object Array]') {
	          object[param] = [].concat(mutation[param]);
	        } else if (babelHelpers.typeof(mutation[param]) === 'object') {
	          if (babelHelpers.typeof(object[param]) === 'object') {
	            object[param] = this._applyMutation(object[param], mutation[param]);
	          } else {
	            object[param] = mutation[param];
	          }
	        } else {
	          object[param] = mutation[param];
	        }
	      }

	      return object;
	    }
	    /**
	     * Test node for compliance with parameters
	     *
	     * @param obj
	     * @param params
	     * @returns {boolean}
	     */

	  }, {
	    key: "testNode",
	    value: function testNode(obj, params) {
	      if (!params || babelHelpers.typeof(params) !== 'object') {
	        return true;
	      }

	      var i, j, len;

	      for (i in params) {
	        if (!params.hasOwnProperty(i)) {
	          continue;
	        }

	        switch (i) {
	          case 'tag':
	          case 'tagName':
	            if (typeof params[i] === "string") {
	              if (obj.tagName.toUpperCase() !== params[i].toUpperCase()) {
	                return false;
	              }
	            } else if (params[i] instanceof RegExp) {
	              if (!params[i].test(obj.tagName)) {
	                return false;
	              }
	            }

	            break;

	          case 'class':
	          case 'className':
	            if (typeof params[i] === "string") {
	              if (!obj.classList.contains(params[i].trim())) {
	                return false;
	              }
	            } else if (params[i] instanceof RegExp) {
	              if (typeof obj.className !== "string" || !params[i].test(obj.className)) {
	                return false;
	              }
	            }

	            break;

	          case 'attr':
	          case 'attrs':
	          case 'attribute':
	            if (typeof params[i] === "string") {
	              if (!obj.getAttribute(params[i])) {
	                return false;
	              }
	            } else if (params[i] && Object.prototype.toString.call(params[i]) === "[object Array]") {
	              for (j = 0, len = params[i].length; j < len; j++) {
	                if (params[i][j] && !obj.getAttribute(params[i][j])) {
	                  return false;
	                }
	              }
	            } else {
	              for (j in params[i]) {
	                if (!params[i].hasOwnProperty(j)) {
	                  continue;
	                }

	                var value = obj.getAttribute(j);

	                if (typeof value !== "string") {
	                  return false;
	                }

	                if (params[i][j] instanceof RegExp) {
	                  if (!params[i][j].test(value)) {
	                    return false;
	                  }
	                } else if (value !== '' + params[i][j]) {
	                  return false;
	                }
	              }
	            }

	            break;

	          case 'property':
	          case 'props':
	            if (typeof params[i] === "string") {
	              if (!obj[params[i]]) {
	                return false;
	              }
	            } else if (params[i] && Object.prototype.toString.call(params[i]) == "[object Array]") {
	              for (j = 0, len = params[i].length; j < len; j++) {
	                if (params[i][j] && !obj[params[i][j]]) {
	                  return false;
	                }
	              }
	            } else {
	              for (j in params[i]) {
	                if (!params[i].hasOwnProperty(j)) {
	                  continue;
	                }

	                if (typeof params[i][j] === "string") {
	                  if (obj[j] != params[i][j]) {
	                    return false;
	                  }
	                } else if (params[i][j] instanceof RegExp) {
	                  if (typeof obj[j] !== "string" || !params[i][j].test(obj[j])) {
	                    return false;
	                  }
	                }
	              }
	            }

	            break;
	        }
	      }

	      return true;
	    }
	  }]);
	  return BitrixVue;
	}();

	var Vue = new BitrixVue();

	exports.VueVendor = ui_vue_vendor_v2.VueVendorV2;
	exports.Vue = Vue;

}((this.BX = this.BX || {}),window,BX));
//# sourceMappingURL=vue.bitrix.bundle.js.map
