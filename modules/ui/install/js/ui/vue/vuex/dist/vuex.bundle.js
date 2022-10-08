;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vuex !== 'undefined'
	)
	{
		var currentVersion = '3.6.2';

		if (this.BX.Vuex.version !== currentVersion)
		{
			console.warn('BX.Vuex already loaded. Loaded: ' + this.BX.Vuex.version + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}

(function (exports,ui_vue,ui_dexie,main_md5) {
	'use strict';

	/**
	 * Bitrix Vuex wrapper
	 * IndexedDB driver for Vuex Builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var VuexBuilderDatabaseIndexedDB = /*#__PURE__*/function () {
	  function VuexBuilderDatabaseIndexedDB() {
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, VuexBuilderDatabaseIndexedDB);
	    this.siteId = config.siteId || 'default';
	    this.userId = config.userId || 0;
	    this.storage = config.storage || 'default';
	    this.name = config.name || '';
	    this.code = (window.md5 || main_md5.md5)(this.siteId + '/' + this.userId + '/' + this.storage + '/' + this.name);
	    this.db = new ui_dexie.Dexie('bx-vuex-model');
	    this.db.version(1).stores({
	      data: "code, value"
	    });
	  }

	  babelHelpers.createClass(VuexBuilderDatabaseIndexedDB, [{
	    key: "get",
	    value: function get() {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        _this.db.data.where('code').equals(_this.code).first().then(function (data) {
	          resolve(data ? data.value : null);
	        }, function (error) {
	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "set",
	    value: function set(value) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        _this2.db.data.put({
	          code: _this2.code,
	          value: value
	        }).then(function (data) {
	          resolve(true);
	        }, function (error) {
	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      var _this3 = this;

	      return new Promise(function (resolve, reject) {
	        _this3.db.data["delete"](_this3.code).then(function (data) {
	          resolve(true);
	        }, function (error) {
	          reject(error);
	        });
	      });
	    }
	  }]);
	  return VuexBuilderDatabaseIndexedDB;
	}();

	/**
	 * Bitrix Vuex wrapper
	 * LocalStorage driver for Vuex Builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var VuexBuilderDatabaseLocalStorage = /*#__PURE__*/function () {
	  function VuexBuilderDatabaseLocalStorage() {
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, VuexBuilderDatabaseLocalStorage);
	    this.siteId = config.siteId || 'default';
	    this.userId = config.userId || 0;
	    this.storage = config.storage || 'default';
	    this.name = config.name || '';
	    this.enabled = false;

	    if (typeof window.localStorage !== 'undefined') {
	      try {
	        window.localStorage.setItem('__bx_test_ls_feature__', 'ok');

	        if (window.localStorage.getItem('__bx_test_ls_feature__') === 'ok') {
	          window.localStorage.removeItem('__bx_test_ls_feature__');
	          this.enabled = true;
	        }
	      } catch (e) {}
	    }

	    this.code = 'bx-vuex-' + (window.md5 || main_md5.md5)(this.siteId + '/' + this.userId + '/' + this.storage + '/' + this.name);
	  }

	  babelHelpers.createClass(VuexBuilderDatabaseLocalStorage, [{
	    key: "get",
	    value: function get() {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        if (!_this.enabled) {
	          resolve(null);
	          return true;
	        }

	        var result = window.localStorage.getItem(_this.code);

	        if (typeof result !== "string") {
	          resolve(null);
	          return true;
	        }

	        try {
	          resolve(_this.prepareValueAfterGet(JSON.parse(result)));
	        } catch (error) {
	          reject(error);
	        }
	      });
	    }
	  }, {
	    key: "set",
	    value: function set(value) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this2.enabled) {
	          window.localStorage.setItem(_this2.code, JSON.stringify(_this2.prepareValueBeforeSet(value)));
	        }

	        resolve(true);
	      });
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      var _this3 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this3.enabled) {
	          window.localStorage.removeItem(_this3.code);
	        }

	        resolve(true);
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "prepareValueAfterGet",
	    value: function prepareValueAfterGet(value) {
	      var _this4 = this;

	      if (value instanceof Array) {
	        value = value.map(function (element) {
	          return _this4.prepareValueAfterGet(element);
	        });
	      } else if (value instanceof Date) ; else if (value && babelHelpers["typeof"](value) === 'object') {
	        for (var index in value) {
	          if (value.hasOwnProperty(index)) {
	            value[index] = this.prepareValueAfterGet(value[index]);
	          }
	        }
	      } else if (typeof value === 'string') {
	        if (value.startsWith('#DT#')) {
	          value = new Date(value.substring(4));
	        }
	      }

	      return value;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "prepareValueBeforeSet",
	    value: function prepareValueBeforeSet(value) {
	      var _this5 = this;

	      if (value instanceof Array) {
	        value = value.map(function (element) {
	          return _this5.prepareValueBeforeSet(element);
	        });
	      } else if (value instanceof Date) {
	        value = '#DT#' + value.toISOString();
	      } else if (value && babelHelpers["typeof"](value) === 'object') {
	        for (var index in value) {
	          if (value.hasOwnProperty(index)) {
	            value[index] = this.prepareValueBeforeSet(value[index]);
	          }
	        }
	      }

	      return value;
	    }
	  }]);
	  return VuexBuilderDatabaseLocalStorage;
	}();

	/**
	 * Bitrix Vuex wrapper
	 * BitrixMobile ApplicationStorage driver for Vuex Builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var VuexBuilderDatabaseJnSharedStorage = /*#__PURE__*/function () {
	  function VuexBuilderDatabaseJnSharedStorage() {
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, VuexBuilderDatabaseJnSharedStorage);
	    this.siteId = config.siteId || 'default';
	    this.userId = config.userId || 0;
	    this.storage = config.storage || 'default';
	    this.name = config.name || '';
	    this.code = (window.md5 || main_md5.md5)(this.siteId + '/' + this.userId + '/' + this.storage + '/' + this.name);

	    if (!this.isJnContext() && typeof ApplicationStorage === 'undefined') {
	      console.error('ApplicationStorage is not defined, load "webcomponent/storage" extension.');
	    }
	  }

	  babelHelpers.createClass(VuexBuilderDatabaseJnSharedStorage, [{
	    key: "get",
	    value: function get() {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        if (_this.isJnContext()) {
	          var result = Application.sharedStorage.get(_this.code);
	          resolve(result ? result : null);
	        } else if (typeof ApplicationStorage !== 'undefined') {
	          ApplicationStorage.get(_this.code, null).then(function (data) {
	            return resolve(_this.prepareValueAfterGet(JSON.parse(data)));
	          });
	        } else {
	          resolve(null);
	        }
	      });
	    }
	  }, {
	    key: "set",
	    value: function set(value) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this2.isJnContext()) {
	          Application.sharedStorage().set(_this2.code, JSON.stringify(_this2.prepareValueBeforeSet(value)));
	          resolve();
	        } else if (typeof ApplicationStorage !== 'undefined') {
	          ApplicationStorage.set(_this2.code, JSON.stringify(_this2.prepareValueBeforeSet(value))).then(function (data) {
	            return resolve();
	          });
	        } else {
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      return this.set(null);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "isJnContext",
	    value: function isJnContext() {
	      return typeof env !== 'undefined';
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "prepareValueAfterGet",
	    value: function prepareValueAfterGet(value) {
	      var _this3 = this;

	      if (value instanceof Array) {
	        value = value.map(function (element) {
	          return _this3.prepareValueAfterGet(element);
	        });
	      } else if (value instanceof Date) ; else if (value && babelHelpers["typeof"](value) === 'object') {
	        for (var index in value) {
	          if (value.hasOwnProperty(index)) {
	            value[index] = this.prepareValueAfterGet(value[index]);
	          }
	        }
	      } else if (typeof value === 'string') {
	        if (value.startsWith('#DT#')) {
	          value = new Date(value.substring(4));
	        }
	      }

	      return value;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "prepareValueBeforeSet",
	    value: function prepareValueBeforeSet(value) {
	      var _this4 = this;

	      if (value instanceof Array) {
	        value = value.map(function (element) {
	          return _this4.prepareValueBeforeSet(element);
	        });
	      } else if (value instanceof Date) {
	        value = '#DT#' + value.toISOString();
	      } else if (value && babelHelpers["typeof"](value) === 'object') {
	        for (var index in value) {
	          if (value.hasOwnProperty(index)) {
	            value[index] = this.prepareValueBeforeSet(value[index]);
	          }
	        }
	      }

	      return value;
	    }
	  }]);
	  return VuexBuilderDatabaseJnSharedStorage;
	}();

	/**
	 * Bitrix Vuex wrapper
	 * Interface Vuex model (Vuex builder model)
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var VuexBuilderModel$$1 = /*#__PURE__*/function () {
	  babelHelpers.createClass(VuexBuilderModel$$1, [{
	    key: "getName",

	    /**
	     * Get name of model
	     *
	     * @override
	     *
	     * @returns {String}
	     */
	    value: function getName() {
	      return '';
	    }
	    /**
	     * Get default state
	     *
	    	 * @override
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getState",
	    value: function getState() {
	      return {};
	    }
	    /**
	     * Get default element state for models with collection.
	     *
	    	 * @override
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getElementState",
	    value: function getElementState() {
	      return {};
	    }
	    /**
	     * Get object containing fields to exclude during the save to database.
	     *
	    	 * @override
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getStateSaveException",
	    value: function getStateSaveException() {
	      return undefined;
	    }
	    /**
	     * Get getters
	     *
	    	 * @override
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      return {};
	    }
	    /**
	     * Get actions
	     *
	    	 * @override
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getActions",
	    value: function getActions() {
	      return {};
	    }
	    /**
	     * Get mutations
	     *
	    	 * @override
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      return {};
	    }
	    /**
	     * Method for validation and sanitizing input fields before save in model
	     *
	     * @override
	     *
	     * @param fields {Object}
	     * @param options {Object}
	     *
	     * @returns {Object} - Sanitizing fields
	     */

	  }, {
	    key: "validate",
	    value: function validate(fields) {
	      return {};
	    }
	    /**
	     * Set external variable.
	     *
	     * @param variables {Object}
	     * @returns {VuexBuilderModel}
	     */

	  }, {
	    key: "setVariables",
	    value: function setVariables() {
	      var variables = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (!(babelHelpers["typeof"](variables) === 'object' && variables)) {
	        this.logger('error', 'VuexBuilderModel.setVars: passed variables is not a Object', store);
	        return this;
	      }

	      this.variables = variables;
	      return this;
	    }
	  }, {
	    key: "getVariable",
	    value: function getVariable(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;

	      if (!name) {
	        return defaultValue;
	      }

	      var nameParts = name.toString().split('.');

	      if (nameParts.length === 1) {
	        return this.variables[nameParts[0]];
	      }

	      var result;
	      var variables = Object.assign({}, this.variables);

	      for (var i = 0; i < nameParts.length; i++) {
	        if (typeof variables[nameParts[i]] !== 'undefined') {
	          variables = result = variables[nameParts[i]];
	        } else {
	          result = defaultValue;
	          break;
	        }
	      }

	      return result;
	    }
	    /**
	     * Get namespace
	     *
	     * @returns {String}
	     */

	  }, {
	    key: "getNamespace",
	    value: function getNamespace() {
	      return this.namespace ? this.namespace : this.getName();
	    }
	    /**
	     * Set namespace
	     *
	     * @param name {String}
	     *
	     * @returns {VuexBuilderModel}
	     */

	  }, {
	    key: "setNamespace",
	    value: function setNamespace(name) {
	      this.namespace = name.toString();
	      this.databaseConfig.name = this.namespace;
	      return this;
	    }
	    /**
	     * Set database config for model or disable this feature.
	     *
	     * @param active {boolean}
	     * @param config {{name: String, siteId: String, userId: Number, type: VuexBuilder.DatabaseType}}
	     *
	     * @returns {VuexBuilderModel}
	     */

	  }, {
	    key: "useDatabase",
	    value: function useDatabase(active) {
	      var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      this.databaseConfig.active = !!active;
	      var updateDriver = this.db === null;

	      if (config.type) {
	        this.databaseConfig.type = config.type.toString();
	        updateDriver = true;
	      }

	      if (config.storage) {
	        this.databaseConfig.storage = config.storage.toString();
	      }

	      if (config.siteId) {
	        this.databaseConfig.siteId = config.siteId.toString();
	      }

	      if (config.userId) {
	        this.databaseConfig.userId = config.userId;
	      }

	      if (typeof config.timeout === 'number') {
	        this.databaseConfig.timeout = config.timeout;
	      }

	      if (!this.databaseConfig.active && this.db !== null) {
	        this.databaseConfig.type = null;
	        updateDriver = true;
	      }

	      if (updateDriver) {
	        if (this.databaseConfig.type === VuexBuilder$$1.DatabaseType.indexedDb) {
	          this.db = new VuexBuilderDatabaseIndexedDB(this.databaseConfig);
	        } else if (this.databaseConfig.type === VuexBuilder$$1.DatabaseType.localStorage) {
	          this.db = new VuexBuilderDatabaseLocalStorage(this.databaseConfig);
	        } else if (this.databaseConfig.type === VuexBuilder$$1.DatabaseType.jnSharedStorage) {
	          this.db = new VuexBuilderDatabaseJnSharedStorage(this.databaseConfig);
	        } else {
	          this.db = null;
	        }
	      }

	      return this;
	    }
	    /**
	     * @returns {VuexBuilderModel}
	     * @deprecated
	     */

	  }, {
	    key: "useNamespace",
	    value: function useNamespace(active) {
	      if (ui_vue.BitrixVue.developerMode) {
	        if (active) {
	          console.warn('VuexBuilderModel: Method `useNamespace` is deprecated, please remove this call.');
	        } else {
	          console.error('VuexBuilderModel: Method `useNamespace` is deprecated, using VuexBuilder without namespaces is no longer supported.');
	        }
	      }

	      return this;
	    }
	    /**
	     * @returns {Promise}
	     * @deprecated use getModule instead.
	     */

	  }, {
	    key: "getStore",
	    value: function getStore() {
	      return this.getModule();
	    }
	    /**
	     * Get Vuex module.
	     *
	     * @returns {Promise}
	     */

	  }, {
	    key: "getModule",
	    value: function getModule() {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        var namespace = _this.namespace ? _this.namespace : _this.getName();

	        if (!namespace) {
	          _this.logger('error', 'VuexBuilderModel.getStore: current model can not be run in Vuex modules mode', _this.getState());

	          reject();
	        }

	        if (_this.db) {
	          _this._getStoreFromDatabase().then(function (state) {
	            return resolve({
	              namespace: namespace,
	              module: _this._createStore(state)
	            });
	          });
	        } else {
	          resolve({
	            namespace: namespace,
	            module: _this._createStore(_this.getState())
	          });
	        }
	      });
	    }
	    /**
	     * Get default state of Vuex module.
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getModuleWithDefaultState",
	    value: function getModuleWithDefaultState() {
	      var namespace = this.namespace ? this.namespace : this.getName();

	      if (!namespace) {
	        this.logger('error', 'VuexBuilderModel.getStore: current model can not be run in Vuex modules mode', this.getState());
	        return null;
	      }

	      return {
	        namespace: namespace,
	        module: this._createStore(this.getState())
	      };
	    }
	    /**
	     * Get timeout for save to database
	     *
	    	 * @override
	     *
	     * @returns {number}
	     */

	  }, {
	    key: "getSaveTimeout",
	    value: function getSaveTimeout() {
	      return 150;
	    }
	    /**
	     * Get timeout for load from database
	     *
	     * @override
	     *
	     * @returns {number|boolean}
	     */

	  }, {
	    key: "getLoadTimeout",
	    value: function getLoadTimeout() {
	      return 1000;
	    }
	    /**
	     * Get state after load from database
	     *
	    	 * @param state {Object}
	     *
	     * @override
	     *
	     * @returns {Object}
	     */

	  }, {
	    key: "getLoadedState",
	    value: function getLoadedState() {
	      var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return state;
	    }
	    /**
	     * Save current state after change state to database
	     *
	    	 * @param state {Object|function}
	     *
	     * @returns {Promise}
	     */

	  }, {
	    key: "saveState",
	    value: function saveState() {
	      var _this2 = this;

	      var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (!this.isSaveAvailable()) {
	        return true;
	      }

	      this.lastSaveState = state;

	      if (this.saveStateTimeout) {
	        this.logger('log', 'VuexModel.saveState: wait save...', this.getName());
	        return true;
	      }

	      this.logger('log', 'VuexModel.saveState: start saving', this.getName());
	      var timeout = this.getSaveTimeout();

	      if (typeof this.databaseConfig.timeout === 'number') {
	        timeout = this.databaseConfig.timeout;
	      }

	      this.saveStateTimeout = setTimeout(function () {
	        _this2.logger('log', 'VuexModel.saveState: saved!', _this2.getName());

	        var lastState = _this2.lastSaveState;

	        if (typeof lastState === 'function') {
	          lastState = lastState();

	          if (babelHelpers["typeof"](lastState) !== 'object' || !lastState) {
	            return false;
	          }
	        }

	        _this2.db.set(_this2.cloneState(lastState, _this2.getStateSaveException()));

	        _this2.lastState = null;
	        _this2.saveStateTimeout = null;
	      }, timeout);
	      return true;
	    }
	    /**
	     * Reset current store to default state
	     **
	     * @returns {Promise}
	     */

	  }, {
	    key: "clearState",
	    value: function clearState() {
	      if (this.store) {
	        this.store.commit(this.getNamespace() + '/' + 'vuexBuilderModelClearState');
	        return true;
	      }

	      return this.saveState(this.getState());
	    }
	    /**
	     * Clear database only, store state does not change
	     **
	     * @returns {Promise}
	     */

	  }, {
	    key: "clearDatabase",
	    value: function clearDatabase() {
	      if (!this.isSaveAvailable()) {
	        return true;
	      }

	      this.db.clear();
	      return true;
	    }
	  }, {
	    key: "isSaveAvailable",
	    value: function isSaveAvailable() {
	      return this.db && this.databaseConfig.active;
	    }
	  }, {
	    key: "isSaveNeeded",
	    value: function isSaveNeeded(payload) {
	      if (!this.isSaveAvailable()) {
	        return false;
	      }

	      var checkFunction = function checkFunction(payload) {
	        var filter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	        if (!filter) {
	          return true;
	        }

	        for (var field in payload) {
	          if (!payload.hasOwnProperty(field)) {
	            continue;
	          }

	          if (typeof filter[field] === 'undefined') {
	            return true;
	          } else if (babelHelpers["typeof"](filter[field]) === 'object' && filter[field]) {
	            var result = checkFunction(payload[field], filter[field]);

	            if (result) {
	              return true;
	            }
	          }
	        }

	        return false;
	      };

	      return checkFunction(payload, this.getStateSaveException());
	    }
	    /**
	     * Create new instance of model.
	     */

	  }], [{
	    key: "create",

	    /**
	     * Create new instance of model.
	     *
	     * @returns {VuexBuilderModel}
	     */
	    value: function create() {
	      return new this();
	    }
	  }]);

	  function VuexBuilderModel$$1() {
	    babelHelpers.classCallCheck(this, VuexBuilderModel$$1);
	    this.databaseConfig = {
	      type: VuexBuilder$$1.DatabaseType.indexedDb,
	      active: null,
	      storage: 'default',
	      name: this.getName(),
	      siteId: 'default',
	      userId: 0,
	      timeout: null
	    };
	    this.db = null;
	    this.store = null;
	    this.namespace = null;
	    this.variables = {};
	  }

	  babelHelpers.createClass(VuexBuilderModel$$1, [{
	    key: "setStore",
	    value: function setStore(store) {
	      if (!(store instanceof index.Store)) {
	        this.logger('error', 'VuexBuilderModel.setStore: passed store is not a Vuex.Store', store);
	        return this;
	      }

	      this.store = store;
	      return this;
	    }
	  }, {
	    key: "_getStoreFromDatabase",
	    value: function _getStoreFromDatabase() {
	      var _this3 = this;

	      clearTimeout(this.cacheTimeout);
	      return new Promise(function (resolve) {
	        var loadTimeout = _this3.getLoadTimeout();

	        if (loadTimeout !== false && typeof loadTimeout === 'number') {
	          _this3.cacheTimeout = setTimeout(function () {
	            _this3.logger('warn', 'VuexModel.getStoreFromDatabase: Cache loading timeout', _this3.getName());

	            resolve(_this3.getState());
	          }, loadTimeout);
	        } else {
	          _this3.cacheTimeout = null;
	        }

	        _this3.db.get().then(function (cache) {
	          clearTimeout(_this3.cacheTimeout);
	          cache = _this3.getLoadedState(cache ? cache : {});

	          var state = _this3.getState();

	          if (cache) {
	            state = _this3._mergeState(state, cache);
	          }

	          resolve(state);
	        }, function (error) {
	          clearTimeout(_this3.cacheTimeout);
	          resolve(_this3.getState());
	        });
	      });
	    }
	  }, {
	    key: "_mergeState",
	    value: function _mergeState(currentState, newState) {
	      for (var key in currentState) {
	        if (!currentState.hasOwnProperty(key)) {
	          continue;
	        }

	        if (typeof newState[key] === 'undefined') {
	          newState[key] = currentState[key];
	        } else if (!(newState[key] instanceof Array) && babelHelpers["typeof"](newState[key]) === 'object' && newState[key] && babelHelpers["typeof"](currentState[key]) === 'object' && currentState[key]) {
	          newState[key] = Object.assign({}, currentState[key], newState[key]);
	        }
	      }

	      return newState;
	    }
	  }, {
	    key: "_createStore",
	    value: function _createStore(state) {
	      var _this4 = this;

	      var result = {
	        namespaced: true,
	        state: state,
	        getters: this.getGetters(),
	        actions: this.getActions(),
	        mutations: this.getMutations()
	      };

	      result.mutations.vuexBuilderModelClearState = function (state) {
	        state = Object.assign(state, _this4.getState());

	        _this4.saveState(state);
	      };

	      return result;
	    }
	    /**
	     * Utils. Convert Object to Array
	     * @param object
	     * @returns {Array}
	     */

	  }, {
	    key: "cloneState",

	    /**
	     * Clone state without observers
	     * @param element {object}
	     * @param exceptions {object}
	     */
	    value: function cloneState(element) {
	      var _this5 = this;

	      var exceptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;
	      var result;

	      if (element instanceof Array) {
	        result = [].concat(element.map(function (element) {
	          return _this5.cloneState(element);
	        }));
	      } else if (element instanceof Date) {
	        result = new Date(element.toISOString());
	      } else if (babelHelpers["typeof"](element) === 'object' && element) {
	        result = {};

	        for (var param in element) {
	          if (!element.hasOwnProperty(param)) {
	            continue;
	          }

	          if (typeof exceptions === 'undefined' || typeof exceptions[param] === 'undefined') {
	            result[param] = this.cloneState(element[param]);
	          } else if (babelHelpers["typeof"](exceptions[param]) === 'object' && exceptions[param]) {
	            result[param] = this.cloneState(element[param], exceptions[param]);
	          }
	        }
	      } else {
	        result = element;
	      }

	      return result;
	    }
	  }, {
	    key: "logger",
	    value: function logger(type) {
	      for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        args[_key - 1] = arguments[_key];
	      }

	      if (type === 'error') {
	        var _console;

	        (_console = console).error.apply(_console, args);

	        return undefined;
	      } else if (typeof BX.VueDevTools === 'undefined') {
	        return undefined;
	      }

	      if (type === 'log') {
	        var _console2;

	        (_console2 = console).log.apply(_console2, args);
	      } else if (type === 'info') {
	        var _console3;

	        (_console3 = console).info.apply(_console3, args);
	      } else if (type === 'warn') {
	        var _console4;

	        (_console4 = console).warn.apply(_console4, args);
	      }
	    }
	  }], [{
	    key: "convertToArray",
	    value: function convertToArray(object) {
	      var result = [];

	      for (var i in object) {
	        if (object.hasOwnProperty(i)) {
	          result.push(object[i]);
	        }
	      }

	      return result;
	    }
	  }]);
	  return VuexBuilderModel$$1;
	}();

	/**
	 * Bitrix Vuex wrapper
	 * Vuex builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */
	var DatabaseType = Object.freeze({
	  indexedDb: 'indexedDb',
	  localStorage: 'localStorage',
	  jnSharedStorage: 'jnSharedStorage'
	});
	var VuexBuilder$$1 = /*#__PURE__*/function () {
	  babelHelpers.createClass(VuexBuilder$$1, null, [{
	    key: "create",

	    /**
	     * @deprecated use init() method.
	     * @returns {VuexBuilder}
	     */
	    value: function create() {
	      if (ui_vue.BitrixVue.developerMode) {
	        console.warn('VuexBuilder: Method VuexBuilder.create is deprecated, use VuexBuilder.init instead.');
	      }

	      return new this();
	    }
	    /**
	     * Create new instance of builder and initialize Vuex store
	     *
	     * @param store {Vuex}
	     *
	     * @returns {VuexBuilder}
	     */

	  }, {
	    key: "init",
	    value: function init(store) {
	      if (store) {
	        if (!(store instanceof index.Store)) {
	          console.warn('VuexBuilder.init: passed store is not a Vuex.Store', store);
	          return new this();
	        }
	      }

	      return new this(store);
	    }
	  }]);

	  function VuexBuilder$$1(store) {
	    babelHelpers.classCallCheck(this, VuexBuilder$$1);
	    this.models = [];
	    this.databaseConfig = {
	      name: null,
	      type: null,
	      siteId: null,
	      userId: null,
	      timeout: null
	    };
	    this.store = store;
	    this.builded = false;
	  }
	  /**
	   * Add Vuex module.
	   *
	   * @param model {VuexBuilderModel}
	   *
	   * @returns {VuexBuilder}
	   */


	  babelHelpers.createClass(VuexBuilder$$1, [{
	    key: "addModel",
	    value: function addModel(model) {
	      if (this.builded) {
	        return this;
	      }

	      if (!(model instanceof VuexBuilderModel$$1)) {
	        console.error('BX.VuexBuilder.addModel: passed model is not a BX.VuexBuilderModel', model, name);
	        return this;
	      }

	      this.models.push(model);
	      return this;
	    }
	    /**
	     * Add dynamic Vuex module.
	     *
	     * @param model {VuexBuilderModel}
	     *
	     * @returns {Promise}
	     */

	  }, {
	    key: "addDynamicModel",
	    value: function addDynamicModel(model) {
	      var _this = this;

	      if (!(model instanceof VuexBuilderModel$$1)) {
	        return new Promise(function (resolve, reject) {
	          console.error('BX.VuexBuilder.addDynamicModel: passed model is not a BX.VuexBuilderModel', model);
	          reject('MODEL_ERROR');
	        });
	      }

	      if (this.store.hasModule(model.getNamespace()) || this.models.find(function (stored) {
	        return stored.getNamespace() === model.getNamespace();
	      })) {
	        return new Promise(function (resolve, reject) {
	          console.error('BX.VuexBuilder.addDynamicModel: model `' + model.getNamespace() + '` was not added because it is already registered.');
	          reject('DUPLICATE_MODEL');
	        });
	      }

	      this.models.push(model);

	      if (this.databaseConfig.active && model.databaseConfig.active !== false) {
	        model.useDatabase(true, this.databaseConfig);
	      } else {
	        model.useDatabase(false);
	      }

	      model.setStore(this.store);
	      var promise = model.getModule();
	      return new Promise(function (resolve, reject) {
	        promise.then(function (result) {
	          _this.store.registerModule(result.namespace, result.module);

	          resolve();
	        }, function (error) {
	          console.error('BX.VuexBuilder.addDynamicModel: storage was not created due to runtime errors.', error ? error : '');
	          reject('ERROR_IN_MODEL');
	        });
	      });
	    }
	    /**
	     * Remove dynamic Vuex module.
	     *
	     * @param namespace {string}
	     *
	     * @returns {VuexBuilder}
	     */

	  }, {
	    key: "removeDynamicModel",
	    value: function removeDynamicModel(namespace) {
	      if (!this.builded) {
	        console.error('BX.VuexBuilder.removeDynamicModel: you cannot use the method until builder is built.');
	        return this;
	      }

	      if (!this.store.hasModule(namespace)) {
	        console.error('BX.VuexBuilder.removeDynamicModel: module `' + namespace + '` not registered.');
	        return this;
	      }

	      this.models = this.models.filter(function (stored) {
	        return stored.getNamespace() !== namespace;
	      });
	      this.store.unregisterModule(namespace);
	      return this;
	    }
	    /**
	     * @returns {VuexBuilder}
	     * @deprecated
	     */

	  }, {
	    key: "useNamespace",
	    value: function useNamespace(active) {
	      if (ui_vue.BitrixVue.developerMode) {
	        if (active) {
	          console.warn('VuexBuilder: Method `useNamespace` is deprecated, please remove this call.');
	        } else {
	          console.error('VuexBuilder: Method `useNamespace` is deprecated, using VuexBuilder without namespaces is no longer supported.');
	        }
	      }

	      return this;
	    }
	    /**
	     * Set database config for all models (except models with "no database" option).
	     *
	     * @param config {{name: String, siteId: String, userId: Number, type: DatabaseType}}
	     * @returns {VuexBuilder}
	     */

	  }, {
	    key: "setDatabaseConfig",
	    value: function setDatabaseConfig() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (!(babelHelpers["typeof"](config) === 'object' && config)) {
	        return this;
	      }

	      this.databaseConfig.active = true;
	      this.databaseConfig.storage = config.name;
	      this.databaseConfig.type = config.type || this.databaseConfig.type;
	      this.databaseConfig.siteId = config.siteId || this.databaseConfig.siteId;
	      this.databaseConfig.userId = config.userId || this.databaseConfig.userId;
	      this.databaseConfig.timeout = typeof config.timeout !== 'undefined' ? config.timeout : this.databaseConfig.timeout;
	      return this;
	    }
	  }, {
	    key: "clearModelState",
	    value: function clearModelState() {
	      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (!this.builded) {
	        return new Promise(function (resolve, reject) {
	          console.error('BX.VuexBuilder.clearModelState: you cannot use the method until builder is built.');

	          if (typeof callback !== 'function') {
	            reject('BUILDER_NOT_BUILD');
	          }
	        });
	      }

	      var results = [];
	      this.models.forEach(function (model) {
	        results.push(model.clearState());
	      });
	      return new Promise(function (resolve, reject) {
	        Promise.all(results).then(function (stores) {
	          resolve(true);

	          if (typeof callback === 'function') {
	            callback(true);
	          }
	        }, function (error) {
	          console.error('BX.VuexBuilder.clearModelState: storage was not clear due to runtime errors.', error ? error : '');

	          if (typeof callback !== 'function') {
	            reject('ERROR_WHILE_CLEARING');
	          }
	        });
	      });
	    }
	  }, {
	    key: "clearDatabase",
	    value: function clearDatabase() {
	      if (!this.builded) {
	        return new Promise(function (resolve, reject) {
	          console.error('BX.VuexBuilder.clearModelState: you cannot use the method until builder is built.');
	          reject('BUILDER_NOT_BUILD');
	        });
	      }

	      this.models.forEach(function (model) {
	        return model.clearDatabase();
	      });
	      return new Promise(function (resolve, reject) {
	        return resolve(true);
	      });
	    }
	    /**
	     * Build Vuex Store asynchronously
	     *
	     * @param callback {Function|null}
	     * @returns {Promise<any>}
	     */

	  }, {
	    key: "build",
	    value: function build() {
	      var _this2 = this;

	      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (this.builded) {
	        return this;
	      }

	      var promises = [];

	      if (!this.store) {
	        this.store = index.createStore();
	      }

	      this.models.forEach(function (model) {
	        if (_this2.databaseConfig.active && model.databaseConfig.active !== false) {
	          model.useDatabase(true, _this2.databaseConfig);
	        }

	        model.setStore(_this2.store);
	        promises.push(model.getModule());
	      });
	      return new Promise(function (resolve, reject) {
	        Promise.all(promises).then(function (modules) {
	          modules.forEach(function (result) {
	            _this2.store.registerModule(result.namespace, result.module);
	          });
	          var result = {
	            store: _this2.store,
	            models: _this2.models,
	            builder: _this2
	          };
	          _this2.builded = true;

	          if (typeof callback === 'function') {
	            callback(result);
	          }

	          resolve(result);
	        }, function (error) {
	          console.error('BX.VuexBuilder.create: storage was not created due to runtime errors.', error ? error : '');

	          if (typeof callback !== 'function') {
	            reject('ERROR_IN_MODEL');
	          }
	        });
	      });
	    }
	    /**
	     * Build Vuex Store synchronously
	     *
	     * @returns {Object<any>}
	     */

	  }, {
	    key: "syncBuild",
	    value: function syncBuild() {
	      var _this3 = this;

	      if (this.builded) {
	        return {
	          store: this.store,
	          models: this.models,
	          builder: this
	        };
	      }

	      if (!this.store) {
	        this.store = index.createStore();
	      }

	      if (this.databaseConfig.active) {
	        if (ui_vue.BitrixVue.developerMode) {
	          console.error('VuexBuilder: Method `syncBuild` creates storage in synchronous mode, the database does not work in this mode.');
	        }

	        this.databaseConfig.active = false;
	      }

	      this.models.forEach(function (model) {
	        model.useDatabase(false);
	        model.setStore(_this3.store);

	        var _model$getModuleWithD = model.getModuleWithDefaultState(),
	            namespace = _model$getModuleWithD.namespace,
	            module = _model$getModuleWithD.module;

	        _this3.store.registerModule(namespace, module);
	      });
	      this.builded = true;
	      return {
	        store: this.store,
	        models: this.models,
	        builder: this
	      };
	    }
	  }]);
	  return VuexBuilder$$1;
	}();
	VuexBuilder$$1.DatabaseType = DatabaseType;

	/*!
	 * vuex v3.6.2
	 * (c) 2021 Evan You
	 * @license MIT
	 *
	 * @source: https://cdn.jsdelivr.net/npm/vuex@3.6.2/dist/vuex.esm.browser.js
	 */

	function applyMixin(Vue) {
	  var version = Number(Vue.version.split('.')[0]);

	  if (version >= 2) {
	    Vue.mixin({
	      beforeCreate: vuexInit
	    });
	  } else {
	    // override init and inject vuex init procedure
	    // for 1.x backwards compatibility.
	    var _init = Vue.prototype._init;

	    Vue.prototype._init = function () {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      options.init = options.init ? [vuexInit].concat(options.init) : vuexInit;

	      _init.call(this, options);
	    };
	  }
	  /**
	   * Vuex init hook, injected into each instances init hooks list.
	   */


	  function vuexInit() {
	    var options = this.$options; // store injection

	    if (options.store) {
	      this.$store = typeof options.store === 'function' ? options.store() : options.store;
	    } else if (options.parent && options.parent.$store) {
	      this.$store = options.parent.$store;
	    }
	  }
	}

	var target = typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : {};
	var devtoolHook = target.__VUE_DEVTOOLS_GLOBAL_HOOK__;

	function devtoolPlugin(store) {
	  if (!devtoolHook) return;
	  store._devtoolHook = devtoolHook;
	  devtoolHook.emit('vuex:init', store);
	  devtoolHook.on('vuex:travel-to-state', function (targetState) {
	    store.replaceState(targetState);
	  });
	  store.subscribe(function (mutation, state) {
	    devtoolHook.emit('vuex:mutation', mutation, state);
	  }, {
	    prepend: true
	  });
	  store.subscribeAction(function (action, state) {
	    devtoolHook.emit('vuex:action', action, state);
	  }, {
	    prepend: true
	  });
	}
	/**
	 * Get the first item that pass the test
	 * by second argument function
	 *
	 * @param {Array} list
	 * @param {Function} f
	 * @return {*}
	 */


	function find(list, f) {
	  return list.filter(f)[0];
	}
	/**
	 * Deep copy the given object considering circular structure.
	 * This function caches all nested objects and its copies.
	 * If it detects circular structure, use cached copy to avoid infinite loop.
	 *
	 * @param {*} obj
	 * @param {Array<Object>} cache
	 * @return {*}
	 */


	function deepCopy(obj) {
	  var cache = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];

	  // just return if obj is immutable value
	  if (obj === null || babelHelpers["typeof"](obj) !== 'object') {
	    return obj;
	  } // if obj is hit, it is in circular structure


	  var hit = find(cache, function (c) {
	    return c.original === obj;
	  });

	  if (hit) {
	    return hit.copy;
	  }

	  var copy = Array.isArray(obj) ? [] : {}; // put the copy into cache at first
	  // because we want to refer it in recursive deepCopy

	  cache.push({
	    original: obj,
	    copy: copy
	  });
	  Object.keys(obj).forEach(function (key) {
	    copy[key] = deepCopy(obj[key], cache);
	  });
	  return copy;
	}
	/**
	 * forEach for object
	 */


	function forEachValue(obj, fn) {
	  Object.keys(obj).forEach(function (key) {
	    return fn(obj[key], key);
	  });
	}

	function isObject(obj) {
	  return obj !== null && babelHelpers["typeof"](obj) === 'object';
	}

	function isPromise(val) {
	  return val && typeof val.then === 'function';
	}

	function assert(condition, msg) {
	  if (!condition) throw new Error("[vuex] ".concat(msg));
	}

	function partial(fn, arg) {
	  return function () {
	    return fn(arg);
	  };
	} // Base data struct for store's module, package with some attribute and method


	var Module = /*#__PURE__*/function () {
	  function Module(rawModule, runtime) {
	    babelHelpers.classCallCheck(this, Module);
	    this.runtime = runtime; // Store some children item

	    this._children = Object.create(null); // Store the origin module object which passed by programmer

	    this._rawModule = rawModule;
	    var rawState = rawModule.state; // Store the origin module's state

	    this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
	  }

	  babelHelpers.createClass(Module, [{
	    key: "addChild",
	    value: function addChild(key, module) {
	      this._children[key] = module;
	    }
	  }, {
	    key: "removeChild",
	    value: function removeChild(key) {
	      delete this._children[key];
	    }
	  }, {
	    key: "getChild",
	    value: function getChild(key) {
	      return this._children[key];
	    }
	  }, {
	    key: "hasChild",
	    value: function hasChild(key) {
	      return key in this._children;
	    }
	  }, {
	    key: "update",
	    value: function update(rawModule) {
	      this._rawModule.namespaced = rawModule.namespaced;

	      if (rawModule.actions) {
	        this._rawModule.actions = rawModule.actions;
	      }

	      if (rawModule.mutations) {
	        this._rawModule.mutations = rawModule.mutations;
	      }

	      if (rawModule.getters) {
	        this._rawModule.getters = rawModule.getters;
	      }
	    }
	  }, {
	    key: "forEachChild",
	    value: function forEachChild(fn) {
	      forEachValue(this._children, fn);
	    }
	  }, {
	    key: "forEachGetter",
	    value: function forEachGetter(fn) {
	      if (this._rawModule.getters) {
	        forEachValue(this._rawModule.getters, fn);
	      }
	    }
	  }, {
	    key: "forEachAction",
	    value: function forEachAction(fn) {
	      if (this._rawModule.actions) {
	        forEachValue(this._rawModule.actions, fn);
	      }
	    }
	  }, {
	    key: "forEachMutation",
	    value: function forEachMutation(fn) {
	      if (this._rawModule.mutations) {
	        forEachValue(this._rawModule.mutations, fn);
	      }
	    }
	  }, {
	    key: "namespaced",
	    get: function get() {
	      return !!this._rawModule.namespaced;
	    }
	  }]);
	  return Module;
	}();

	var ModuleCollection = /*#__PURE__*/function () {
	  function ModuleCollection(rawRootModule) {
	    babelHelpers.classCallCheck(this, ModuleCollection);
	    // register root module (Vuex.Store options)
	    this.register([], rawRootModule, false);
	  }

	  babelHelpers.createClass(ModuleCollection, [{
	    key: "get",
	    value: function get(path) {
	      return path.reduce(function (module, key) {
	        return module.getChild(key);
	      }, this.root);
	    }
	  }, {
	    key: "getNamespace",
	    value: function getNamespace(path) {
	      var module = this.root;
	      return path.reduce(function (namespace, key) {
	        module = module.getChild(key);
	        return namespace + (module.namespaced ? key + '/' : '');
	      }, '');
	    }
	  }, {
	    key: "update",
	    value: function update(rawRootModule) {
	      _update([], this.root, rawRootModule);
	    }
	  }, {
	    key: "register",
	    value: function register(path, rawModule) {
	      var _this = this;

	      var runtime = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      {
	        assertRawModule(path, rawModule);
	      }
	      var newModule = new Module(rawModule, runtime);

	      if (path.length === 0) {
	        this.root = newModule;
	      } else {
	        var parent = this.get(path.slice(0, -1));
	        parent.addChild(path[path.length - 1], newModule);
	      } // register nested modules


	      if (rawModule.modules) {
	        forEachValue(rawModule.modules, function (rawChildModule, key) {
	          _this.register(path.concat(key), rawChildModule, runtime);
	        });
	      }
	    }
	  }, {
	    key: "unregister",
	    value: function unregister(path) {
	      var parent = this.get(path.slice(0, -1));
	      var key = path[path.length - 1];
	      var child = parent.getChild(key);

	      if (!child) {
	        {
	          console.warn("[vuex] trying to unregister module '".concat(key, "', which is ") + "not registered");
	        }
	        return;
	      }

	      if (!child.runtime) {
	        return;
	      }

	      parent.removeChild(key);
	    }
	  }, {
	    key: "isRegistered",
	    value: function isRegistered(path) {
	      var parent = this.get(path.slice(0, -1));
	      var key = path[path.length - 1];

	      if (parent) {
	        return parent.hasChild(key);
	      }

	      return false;
	    }
	  }]);
	  return ModuleCollection;
	}();

	function _update(path, targetModule, newModule) {
	  {
	    assertRawModule(path, newModule);
	  } // update target module

	  targetModule.update(newModule); // update nested modules

	  if (newModule.modules) {
	    for (var key in newModule.modules) {
	      if (!targetModule.getChild(key)) {
	        {
	          console.warn("[vuex] trying to add a new module '".concat(key, "' on hot reloading, ") + 'manual reload is needed');
	        }
	        return;
	      }

	      _update(path.concat(key), targetModule.getChild(key), newModule.modules[key]);
	    }
	  }
	}

	var functionAssert = {
	  assert: function assert(value) {
	    return typeof value === 'function';
	  },
	  expected: 'function'
	};
	var objectAssert = {
	  assert: function assert(value) {
	    return typeof value === 'function' || babelHelpers["typeof"](value) === 'object' && typeof value.handler === 'function';
	  },
	  expected: 'function or object with "handler" function'
	};
	var assertTypes = {
	  getters: functionAssert,
	  mutations: functionAssert,
	  actions: objectAssert
	};

	function assertRawModule(path, rawModule) {
	  Object.keys(assertTypes).forEach(function (key) {
	    if (!rawModule[key]) return;
	    var assertOptions = assertTypes[key];
	    forEachValue(rawModule[key], function (value, type) {
	      assert(assertOptions.assert(value), makeAssertionMessage(path, key, type, value, assertOptions.expected));
	    });
	  });
	}

	function makeAssertionMessage(path, key, type, value, expected) {
	  var buf = "".concat(key, " should be ").concat(expected, " but \"").concat(key, ".").concat(type, "\"");

	  if (path.length > 0) {
	    buf += " in module \"".concat(path.join('.'), "\"");
	  }

	  buf += " is ".concat(JSON.stringify(value), ".");
	  return buf;
	}

	var Store = /*#__PURE__*/function () {
	  function Store() {
	    var _this2 = this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Store);

	    // Auto install if it is not done yet and `window` has `Vue`.
	    // To allow users to avoid auto-installation in some cases,
	    // this code should be placed here. See #731
	    if (!ui_vue.VueVendor && typeof window !== 'undefined' && window.Vue) {
	      install(window.Vue);
	    }

	    {
	      assert(ui_vue.VueVendor, "must call Vue.use(Vuex) before creating a store instance.");
	      assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
	      assert(this instanceof Store, "store must be called with the new operator.");
	    }
	    var _options$plugins = options.plugins,
	        plugins = _options$plugins === void 0 ? [] : _options$plugins,
	        _options$strict = options.strict,
	        strict = _options$strict === void 0 ? false : _options$strict; // store internal state

	    this._committing = false;
	    this._actions = Object.create(null);
	    this._actionSubscribers = [];
	    this._mutations = Object.create(null);
	    this._wrappedGetters = Object.create(null);
	    this._modules = new ModuleCollection(options);
	    this._modulesNamespaceMap = Object.create(null);
	    this._subscribers = [];
	    this._watcherVM = new ui_vue.VueVendor();
	    this._makeLocalGettersCache = Object.create(null); // bind commit and dispatch to self

	    var store = this;
	    var dispatch = this.dispatch,
	        commit = this.commit;

	    this.dispatch = function boundDispatch(type, payload) {
	      return dispatch.call(store, type, payload);
	    };

	    this.commit = function boundCommit(type, payload, options) {
	      return commit.call(store, type, payload, options);
	    }; // strict mode


	    this.strict = strict;
	    var state = this._modules.root.state; // init root module.
	    // this also recursively registers all sub-modules
	    // and collects all module getters inside this._wrappedGetters

	    installModule(this, state, [], this._modules.root); // initialize the store vm, which is responsible for the reactivity
	    // (also registers _wrappedGetters as computed properties)

	    resetStoreVM(this, state); // apply plugins

	    plugins.forEach(function (plugin) {
	      return plugin(_this2);
	    });
	    var useDevtools = options.devtools !== undefined ? options.devtools : ui_vue.VueVendor.config.devtools;

	    if (useDevtools) {
	      devtoolPlugin(this);
	    }
	  }

	  babelHelpers.createClass(Store, [{
	    key: "commit",
	    value: function commit(_type, _payload, _options) {
	      var _this3 = this;

	      // check object-style commit
	      var _unifyObjectStyle = unifyObjectStyle(_type, _payload, _options),
	          type = _unifyObjectStyle.type,
	          payload = _unifyObjectStyle.payload,
	          options = _unifyObjectStyle.options;

	      var mutation = {
	        type: type,
	        payload: payload
	      };
	      var entry = this._mutations[type];

	      if (!entry) {
	        {
	          console.error("[vuex] unknown mutation type: ".concat(type));
	        }
	        return;
	      }

	      this._withCommit(function () {
	        entry.forEach(function commitIterator(handler) {
	          handler(payload);
	        });
	      });

	      this._subscribers.slice() // shallow copy to prevent iterator invalidation if subscriber synchronously calls unsubscribe
	      .forEach(function (sub) {
	        return sub(mutation, _this3.state);
	      });

	      if (options && options.silent) {
	        console.warn("[vuex] mutation type: ".concat(type, ". Silent option has been removed. ") + 'Use the filter functionality in the vue-devtools');
	      }
	    }
	  }, {
	    key: "dispatch",
	    value: function dispatch(_type, _payload) {
	      var _this4 = this;

	      // check object-style dispatch
	      var _unifyObjectStyle2 = unifyObjectStyle(_type, _payload),
	          type = _unifyObjectStyle2.type,
	          payload = _unifyObjectStyle2.payload;

	      var action = {
	        type: type,
	        payload: payload
	      };
	      var entry = this._actions[type];

	      if (!entry) {
	        {
	          console.error("[vuex] unknown action type: ".concat(type));
	        }
	        return;
	      }

	      try {
	        this._actionSubscribers.slice() // shallow copy to prevent iterator invalidation if subscriber synchronously calls unsubscribe
	        .filter(function (sub) {
	          return sub.before;
	        }).forEach(function (sub) {
	          return sub.before(action, _this4.state);
	        });
	      } catch (e) {
	        {
	          console.warn("[vuex] error in before action subscribers: ");
	          console.error(e);
	        }
	      }

	      var result = entry.length > 1 ? Promise.all(entry.map(function (handler) {
	        return handler(payload);
	      })) : entry[0](payload);
	      return new Promise(function (resolve, reject) {
	        result.then(function (res) {
	          try {
	            _this4._actionSubscribers.filter(function (sub) {
	              return sub.after;
	            }).forEach(function (sub) {
	              return sub.after(action, _this4.state);
	            });
	          } catch (e) {
	            {
	              console.warn("[vuex] error in after action subscribers: ");
	              console.error(e);
	            }
	          }

	          resolve(res);
	        }, function (error) {
	          try {
	            _this4._actionSubscribers.filter(function (sub) {
	              return sub.error;
	            }).forEach(function (sub) {
	              return sub.error(action, _this4.state, error);
	            });
	          } catch (e) {
	            {
	              console.warn("[vuex] error in error action subscribers: ");
	              console.error(e);
	            }
	          }

	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe(fn, options) {
	      return genericSubscribe(fn, this._subscribers, options);
	    }
	  }, {
	    key: "subscribeAction",
	    value: function subscribeAction(fn, options) {
	      var subs = typeof fn === 'function' ? {
	        before: fn
	      } : fn;
	      return genericSubscribe(subs, this._actionSubscribers, options);
	    }
	  }, {
	    key: "watch",
	    value: function watch(getter, cb, options) {
	      var _this5 = this;

	      {
	        assert(typeof getter === 'function', "store.watch only accepts a function.");
	      }
	      return this._watcherVM.$watch(function () {
	        return getter(_this5.state, _this5.getters);
	      }, cb, options);
	    }
	  }, {
	    key: "replaceState",
	    value: function replaceState(state) {
	      var _this6 = this;

	      this._withCommit(function () {
	        _this6._vm._data.$$state = state;
	      });
	    }
	  }, {
	    key: "registerModule",
	    value: function registerModule(path, rawModule) {
	      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      if (typeof path === 'string') path = [path];
	      {
	        assert(Array.isArray(path), "module path must be a string or an Array.");
	        assert(path.length > 0, 'cannot register the root module by using registerModule.');
	      }

	      this._modules.register(path, rawModule);

	      installModule(this, this.state, path, this._modules.get(path), options.preserveState); // reset store to update getters...

	      resetStoreVM(this, this.state);
	    }
	  }, {
	    key: "unregisterModule",
	    value: function unregisterModule(path) {
	      var _this7 = this;

	      if (typeof path === 'string') path = [path];
	      {
	        assert(Array.isArray(path), "module path must be a string or an Array.");
	      }

	      this._modules.unregister(path);

	      this._withCommit(function () {
	        var parentState = getNestedState(_this7.state, path.slice(0, -1));
	        ui_vue.VueVendor["delete"](parentState, path[path.length - 1]);
	      });

	      resetStore(this);
	    }
	  }, {
	    key: "hasModule",
	    value: function hasModule(path) {
	      if (typeof path === 'string') path = [path];
	      {
	        assert(Array.isArray(path), "module path must be a string or an Array.");
	      }
	      return this._modules.isRegistered(path);
	    }
	  }, {
	    key: "hotUpdate",
	    value: function hotUpdate(newOptions) {
	      this._modules.update(newOptions);

	      resetStore(this, true);
	    }
	  }, {
	    key: "_withCommit",
	    value: function _withCommit(fn) {
	      var committing = this._committing;
	      this._committing = true;
	      fn();
	      this._committing = committing;
	    }
	  }, {
	    key: "state",
	    get: function get() {
	      return this._vm._data.$$state;
	    },
	    set: function set(v) {
	      {
	        assert(false, "use store.replaceState() to explicit replace store state.");
	      }
	    }
	  }]);
	  return Store;
	}();

	function genericSubscribe(fn, subs, options) {
	  if (subs.indexOf(fn) < 0) {
	    options && options.prepend ? subs.unshift(fn) : subs.push(fn);
	  }

	  return function () {
	    var i = subs.indexOf(fn);

	    if (i > -1) {
	      subs.splice(i, 1);
	    }
	  };
	}

	function resetStore(store, hot) {
	  store._actions = Object.create(null);
	  store._mutations = Object.create(null);
	  store._wrappedGetters = Object.create(null);
	  store._modulesNamespaceMap = Object.create(null);
	  var state = store.state; // init all modules

	  installModule(store, state, [], store._modules.root, true); // reset vm

	  resetStoreVM(store, state, hot);
	}

	function resetStoreVM(store, state, hot) {
	  var oldVm = store._vm; // bind store public getters

	  store.getters = {}; // reset local getters cache

	  store._makeLocalGettersCache = Object.create(null);
	  var wrappedGetters = store._wrappedGetters;
	  var computed = {};
	  forEachValue(wrappedGetters, function (fn, key) {
	    // use computed to leverage its lazy-caching mechanism
	    // direct inline function use will lead to closure preserving oldVm.
	    // using partial to return function with only arguments preserved in closure environment.
	    computed[key] = partial(fn, store);
	    Object.defineProperty(store.getters, key, {
	      get: function get() {
	        return store._vm[key];
	      },
	      enumerable: true // for local getters

	    });
	  }); // use a Vue instance to store the state tree
	  // suppress warnings just in case the user has added
	  // some funky global mixins

	  var silent = ui_vue.VueVendor.config.silent;
	  ui_vue.VueVendor.config.silent = true;
	  store._vm = new ui_vue.VueVendor({
	    data: {
	      $$state: state
	    },
	    computed: computed
	  });
	  ui_vue.VueVendor.config.silent = silent; // enable strict mode for new vm

	  if (store.strict) {
	    enableStrictMode(store);
	  }

	  if (oldVm) {
	    if (hot) {
	      // dispatch changes in all subscribed watchers
	      // to force getter re-evaluation for hot reloading.
	      store._withCommit(function () {
	        oldVm._data.$$state = null;
	      });
	    }

	    ui_vue.VueVendor.nextTick(function () {
	      return oldVm.$destroy();
	    });
	  }
	}

	function installModule(store, rootState, path, module, hot) {
	  var isRoot = !path.length;

	  var namespace = store._modules.getNamespace(path); // register in namespace map


	  if (module.namespaced) {
	    if (store._modulesNamespaceMap[namespace] && true) {
	      console.error("[vuex] duplicate namespace ".concat(namespace, " for the namespaced module ").concat(path.join('/')));
	    }

	    store._modulesNamespaceMap[namespace] = module;
	  } // set state


	  if (!isRoot && !hot) {
	    var parentState = getNestedState(rootState, path.slice(0, -1));
	    var moduleName = path[path.length - 1];

	    store._withCommit(function () {
	      {
	        if (moduleName in parentState) {
	          console.warn("[vuex] state field \"".concat(moduleName, "\" was overridden by a module with the same name at \"").concat(path.join('.'), "\""));
	        }
	      }
	      ui_vue.VueVendor.set(parentState, moduleName, module.state);
	    });
	  }

	  var local = module.context = makeLocalContext(store, namespace, path);
	  module.forEachMutation(function (mutation, key) {
	    var namespacedType = namespace + key;
	    registerMutation(store, namespacedType, mutation, local);
	  });
	  module.forEachAction(function (action, key) {
	    var type = action.root ? key : namespace + key;
	    var handler = action.handler || action;
	    registerAction(store, type, handler, local);
	  });
	  module.forEachGetter(function (getter, key) {
	    var namespacedType = namespace + key;
	    registerGetter(store, namespacedType, getter, local);
	  });
	  module.forEachChild(function (child, key) {
	    installModule(store, rootState, path.concat(key), child, hot);
	  });
	}
	/**
	 * make localized dispatch, commit, getters and state
	 * if there is no namespace, just use root ones
	 */


	function makeLocalContext(store, namespace, path) {
	  var noNamespace = namespace === '';
	  var local = {
	    dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
	      var args = unifyObjectStyle(_type, _payload, _options);
	      var payload = args.payload,
	          options = args.options;
	      var type = args.type;

	      if (!options || !options.root) {
	        type = namespace + type;

	        if (!store._actions[type]) {
	          console.error("[vuex] unknown local action type: ".concat(args.type, ", global type: ").concat(type));
	          return;
	        }
	      }

	      return store.dispatch(type, payload);
	    },
	    commit: noNamespace ? store.commit : function (_type, _payload, _options) {
	      var args = unifyObjectStyle(_type, _payload, _options);
	      var payload = args.payload,
	          options = args.options;
	      var type = args.type;

	      if (!options || !options.root) {
	        type = namespace + type;

	        if (!store._mutations[type]) {
	          console.error("[vuex] unknown local mutation type: ".concat(args.type, ", global type: ").concat(type));
	          return;
	        }
	      }

	      store.commit(type, payload, options);
	    }
	  }; // getters and state object must be gotten lazily
	  // because they will be changed by vm update

	  Object.defineProperties(local, {
	    getters: {
	      get: noNamespace ? function () {
	        return store.getters;
	      } : function () {
	        return makeLocalGetters(store, namespace);
	      }
	    },
	    state: {
	      get: function get() {
	        return getNestedState(store.state, path);
	      }
	    }
	  });
	  return local;
	}

	function makeLocalGetters(store, namespace) {
	  if (!store._makeLocalGettersCache[namespace]) {
	    var gettersProxy = {};
	    var splitPos = namespace.length;
	    Object.keys(store.getters).forEach(function (type) {
	      // skip if the target getter is not match this namespace
	      if (type.slice(0, splitPos) !== namespace) return; // extract local getter type

	      var localType = type.slice(splitPos); // Add a port to the getters proxy.
	      // Define as getter property because
	      // we do not want to evaluate the getters in this time.

	      Object.defineProperty(gettersProxy, localType, {
	        get: function get() {
	          return store.getters[type];
	        },
	        enumerable: true
	      });
	    });
	    store._makeLocalGettersCache[namespace] = gettersProxy;
	  }

	  return store._makeLocalGettersCache[namespace];
	}

	function registerMutation(store, type, handler, local) {
	  var entry = store._mutations[type] || (store._mutations[type] = []);
	  entry.push(function wrappedMutationHandler(payload) {
	    handler.call(store, local.state, payload);
	  });
	}

	function registerAction(store, type, handler, local) {
	  var entry = store._actions[type] || (store._actions[type] = []);
	  entry.push(function wrappedActionHandler(payload) {
	    var res = handler.call(store, {
	      dispatch: local.dispatch,
	      commit: local.commit,
	      getters: local.getters,
	      state: local.state,
	      rootGetters: store.getters,
	      rootState: store.state
	    }, payload);

	    if (!isPromise(res)) {
	      res = Promise.resolve(res);
	    }

	    if (store._devtoolHook) {
	      return res["catch"](function (err) {
	        store._devtoolHook.emit('vuex:error', err);

	        throw err;
	      });
	    } else {
	      return res;
	    }
	  });
	}

	function registerGetter(store, type, rawGetter, local) {
	  if (store._wrappedGetters[type]) {
	    {
	      console.error("[vuex] duplicate getter key: ".concat(type));
	    }
	    return;
	  }

	  store._wrappedGetters[type] = function wrappedGetter(store) {
	    return rawGetter(local.state, // local state
	    local.getters, // local getters
	    store.state, // root state
	    store.getters // root getters
	    );
	  };
	}

	function enableStrictMode(store) {
	  store._vm.$watch(function () {
	    return this._data.$$state;
	  }, function () {
	    {
	      assert(store._committing, "do not mutate vuex store state outside mutation handlers.");
	    }
	  }, {
	    deep: true,
	    sync: true
	  });
	}

	function getNestedState(state, path) {
	  return path.reduce(function (state, key) {
	    return state[key];
	  }, state);
	}

	function unifyObjectStyle(type, payload, options) {
	  if (isObject(type) && type.type) {
	    options = payload;
	    payload = type;
	    type = type.type;
	  }

	  {
	    assert(typeof type === 'string', "expects string as the type, but found ".concat(babelHelpers["typeof"](type), "."));
	  }
	  return {
	    type: type,
	    payload: payload,
	    options: options
	  };
	}

	function install(_Vue) {
	  applyMixin(_Vue);
	}
	/**
	 * Reduce the code which written in Vue.js for getting the state.
	 * @param {String} [namespace] - Module's namespace
	 * @param {Object|Array} states # Object's item can be a function which accept state and getters for param, you can do something for state and getters in it.
	 * @param {Object}
	 */


	var mapState = normalizeNamespace(function (namespace, states) {
	  var res = {};

	  if (!isValidMap(states)) {
	    console.error('[vuex] mapState: mapper parameter must be either an Array or an Object');
	  }

	  normalizeMap(states).forEach(function (_ref) {
	    var key = _ref.key,
	        val = _ref.val;

	    res[key] = function mappedState() {
	      var state = this.$store.state;
	      var getters = this.$store.getters;

	      if (namespace) {
	        var module = getModuleByNamespace(this.$store, 'mapState', namespace);

	        if (!module) {
	          return;
	        }

	        state = module.context.state;
	        getters = module.context.getters;
	      }

	      return typeof val === 'function' ? val.call(this, state, getters) : state[val];
	    }; // mark vuex getter for devtools


	    res[key].vuex = true;
	  });
	  return res;
	});
	/**
	 * Reduce the code which written in Vue.js for committing the mutation
	 * @param {String} [namespace] - Module's namespace
	 * @param {Object|Array} mutations # Object's item can be a function which accept `commit` function as the first param, it can accept another params. You can commit mutation and do any other things in this function. specially, You need to pass anthor params from the mapped function.
	 * @return {Object}
	 */

	var mapMutations = normalizeNamespace(function (namespace, mutations) {
	  var res = {};

	  if (!isValidMap(mutations)) {
	    console.error('[vuex] mapMutations: mapper parameter must be either an Array or an Object');
	  }

	  normalizeMap(mutations).forEach(function (_ref2) {
	    var key = _ref2.key,
	        val = _ref2.val;

	    res[key] = function mappedMutation() {
	      // Get the commit method from store
	      var commit = this.$store.commit;

	      if (namespace) {
	        var module = getModuleByNamespace(this.$store, 'mapMutations', namespace);

	        if (!module) {
	          return;
	        }

	        commit = module.context.commit;
	      }

	      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	        args[_key] = arguments[_key];
	      }

	      return typeof val === 'function' ? val.apply(this, [commit].concat(args)) : commit.apply(this.$store, [val].concat(args));
	    };
	  });
	  return res;
	});
	/**
	 * Reduce the code which written in Vue.js for getting the getters
	 * @param {String} [namespace] - Module's namespace
	 * @param {Object|Array} getters
	 * @return {Object}
	 */

	var mapGetters = normalizeNamespace(function (namespace, getters) {
	  var res = {};

	  if (!isValidMap(getters)) {
	    console.error('[vuex] mapGetters: mapper parameter must be either an Array or an Object');
	  }

	  normalizeMap(getters).forEach(function (_ref3) {
	    var key = _ref3.key,
	        val = _ref3.val;
	    // The namespace has been mutated by normalizeNamespace
	    val = namespace + val;

	    res[key] = function mappedGetter() {
	      if (namespace && !getModuleByNamespace(this.$store, 'mapGetters', namespace)) {
	        return;
	      }

	      if (!(val in this.$store.getters)) {
	        console.error("[vuex] unknown getter: ".concat(val));
	        return;
	      }

	      return this.$store.getters[val];
	    }; // mark vuex getter for devtools


	    res[key].vuex = true;
	  });
	  return res;
	});
	/**
	 * Reduce the code which written in Vue.js for dispatch the action
	 * @param {String} [namespace] - Module's namespace
	 * @param {Object|Array} actions # Object's item can be a function which accept `dispatch` function as the first param, it can accept anthor params. You can dispatch action and do any other things in this function. specially, You need to pass anthor params from the mapped function.
	 * @return {Object}
	 */

	var mapActions = normalizeNamespace(function (namespace, actions) {
	  var res = {};

	  if (!isValidMap(actions)) {
	    console.error('[vuex] mapActions: mapper parameter must be either an Array or an Object');
	  }

	  normalizeMap(actions).forEach(function (_ref4) {
	    var key = _ref4.key,
	        val = _ref4.val;

	    res[key] = function mappedAction() {
	      // get dispatch function from store
	      var dispatch = this.$store.dispatch;

	      if (namespace) {
	        var module = getModuleByNamespace(this.$store, 'mapActions', namespace);

	        if (!module) {
	          return;
	        }

	        dispatch = module.context.dispatch;
	      }

	      for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	        args[_key2] = arguments[_key2];
	      }

	      return typeof val === 'function' ? val.apply(this, [dispatch].concat(args)) : dispatch.apply(this.$store, [val].concat(args));
	    };
	  });
	  return res;
	});
	/**
	 * Rebinding namespace param for mapXXX function in special scoped, and return them by simple object
	 * @param {String} namespace
	 * @return {Object}
	 */

	var createNamespacedHelpers = function createNamespacedHelpers(namespace) {
	  return {
	    mapState: mapState.bind(null, namespace),
	    mapGetters: mapGetters.bind(null, namespace),
	    mapMutations: mapMutations.bind(null, namespace),
	    mapActions: mapActions.bind(null, namespace)
	  };
	};
	/**
	 * Normalize the map
	 * normalizeMap([1, 2, 3]) => [ { key: 1, val: 1 }, { key: 2, val: 2 }, { key: 3, val: 3 } ]
	 * normalizeMap({a: 1, b: 2, c: 3}) => [ { key: 'a', val: 1 }, { key: 'b', val: 2 }, { key: 'c', val: 3 } ]
	 * @param {Array|Object} map
	 * @return {Object}
	 */


	function normalizeMap(map) {
	  if (!isValidMap(map)) {
	    return [];
	  }

	  return Array.isArray(map) ? map.map(function (key) {
	    return {
	      key: key,
	      val: key
	    };
	  }) : Object.keys(map).map(function (key) {
	    return {
	      key: key,
	      val: map[key]
	    };
	  });
	}
	/**
	 * Validate whether given map is valid or not
	 * @param {*} map
	 * @return {Boolean}
	 */


	function isValidMap(map) {
	  return Array.isArray(map) || isObject(map);
	}
	/**
	 * Return a function expect two param contains namespace and map. it will normalize the namespace and then the param's function will handle the new namespace and the map.
	 * @param {Function} fn
	 * @return {Function}
	 */


	function normalizeNamespace(fn) {
	  return function (namespace, map) {
	    if (typeof namespace !== 'string') {
	      map = namespace;
	      namespace = '';
	    } else if (namespace.charAt(namespace.length - 1) !== '/') {
	      namespace += '/';
	    }

	    return fn(namespace, map);
	  };
	}
	/**
	 * Search a special module from store by namespace. if module not exist, print error message.
	 * @param {Object} store
	 * @param {String} helper
	 * @param {String} namespace
	 * @return {Object}
	 */


	function getModuleByNamespace(store, helper, namespace) {
	  var module = store._modulesNamespaceMap[namespace];

	  if (!module) {
	    console.error("[vuex] module namespace not found in ".concat(helper, "(): ").concat(namespace));
	  }

	  return module;
	} // Credits: borrowed code from fcomb/redux-logger


	function createLogger() {
	  var _ref5 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	      _ref5$collapsed = _ref5.collapsed,
	      collapsed = _ref5$collapsed === void 0 ? true : _ref5$collapsed,
	      _ref5$filter = _ref5.filter,
	      filter = _ref5$filter === void 0 ? function (mutation, stateBefore, stateAfter) {
	    return true;
	  } : _ref5$filter,
	      _ref5$transformer = _ref5.transformer,
	      transformer = _ref5$transformer === void 0 ? function (state) {
	    return state;
	  } : _ref5$transformer,
	      _ref5$mutationTransfo = _ref5.mutationTransformer,
	      mutationTransformer = _ref5$mutationTransfo === void 0 ? function (mut) {
	    return mut;
	  } : _ref5$mutationTransfo,
	      _ref5$actionFilter = _ref5.actionFilter,
	      actionFilter = _ref5$actionFilter === void 0 ? function (action, state) {
	    return true;
	  } : _ref5$actionFilter,
	      _ref5$actionTransform = _ref5.actionTransformer,
	      actionTransformer = _ref5$actionTransform === void 0 ? function (act) {
	    return act;
	  } : _ref5$actionTransform,
	      _ref5$logMutations = _ref5.logMutations,
	      logMutations = _ref5$logMutations === void 0 ? true : _ref5$logMutations,
	      _ref5$logActions = _ref5.logActions,
	      logActions = _ref5$logActions === void 0 ? true : _ref5$logActions,
	      _ref5$logger = _ref5.logger,
	      logger = _ref5$logger === void 0 ? console : _ref5$logger;

	  return function (store) {
	    var prevState = deepCopy(store.state);

	    if (typeof logger === 'undefined') {
	      return;
	    }

	    if (logMutations) {
	      store.subscribe(function (mutation, state) {
	        var nextState = deepCopy(state);

	        if (filter(mutation, prevState, nextState)) {
	          var formattedTime = getFormattedTime();
	          var formattedMutation = mutationTransformer(mutation);
	          var message = "mutation ".concat(mutation.type).concat(formattedTime);
	          startMessage(logger, message, collapsed);
	          logger.log('%c prev state', 'color: #9E9E9E; font-weight: bold', transformer(prevState));
	          logger.log('%c mutation', 'color: #03A9F4; font-weight: bold', formattedMutation);
	          logger.log('%c next state', 'color: #4CAF50; font-weight: bold', transformer(nextState));
	          endMessage(logger);
	        }

	        prevState = nextState;
	      });
	    }

	    if (logActions) {
	      store.subscribeAction(function (action, state) {
	        if (actionFilter(action, state)) {
	          var formattedTime = getFormattedTime();
	          var formattedAction = actionTransformer(action);
	          var message = "action ".concat(action.type).concat(formattedTime);
	          startMessage(logger, message, collapsed);
	          logger.log('%c action', 'color: #03A9F4; font-weight: bold', formattedAction);
	          endMessage(logger);
	        }
	      });
	    }
	  };
	}

	function startMessage(logger, message, collapsed) {
	  var startMessage = collapsed ? logger.groupCollapsed : logger.group; // render

	  try {
	    startMessage.call(logger, message);
	  } catch (e) {
	    logger.log(message);
	  }
	}

	function endMessage(logger) {
	  try {
	    logger.groupEnd();
	  } catch (e) {
	    logger.log('-- log end --');
	  }
	}

	function getFormattedTime() {
	  var time = new Date();
	  return " @ ".concat(pad(time.getHours(), 2), ":").concat(pad(time.getMinutes(), 2), ":").concat(pad(time.getSeconds(), 2), ".").concat(pad(time.getMilliseconds(), 3));
	}

	function repeat(str, times) {
	  return new Array(times + 1).join(str);
	}

	function pad(num, maxLength) {
	  return repeat('0', maxLength - num.toString().length) + num;
	}

	var index = {
	  Store: Store,
	  install: install,
	  version: '3.6.2',
	  mapState: mapState,
	  mapMutations: mapMutations,
	  mapGetters: mapGetters,
	  mapActions: mapActions,
	  createNamespacedHelpers: createNamespacedHelpers,
	  createLogger: createLogger
	}; // origin-end

	index.store = function (params) {
	  return new Store(params);
	};

	index.createStore = function (params) {
	  return new Store(params);
	};

	ui_vue.VueVendor.use(index);

	exports.VuexBuilder = VuexBuilder$$1;
	exports.VuexBuilderModel = VuexBuilderModel$$1;
	exports.Vuex = index;
	exports.VuexVendorV3 = index;
	exports.VuexVendor = index;

}((this.BX = this.BX || {}),BX,BX.Dexie3,BX));



})();
//# sourceMappingURL=vuex.bundle.js.map