;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vue3 !== 'undefined'
		&& typeof this.BX.Vue3.Vuex !== 'undefined'
	)
	{
		var currentVersion = '4.0.2';

		if (this.BX.Vue3.Vuex.version !== currentVersion)
		{
			console.warn('BX.Vue3.Vuex already loaded. Loaded: ' + this.BX.Vue3.Vuex.version + ', Skipped: ' + currentVersion + '. Version differences may cause errors!');
		}

		return;
	}

this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,ui_dexie,main_md5,main_core,ui_vue3) {
	'use strict';

	/**
	 * Bitrix Vuex wrapper
	 * IndexedDB driver for Vuex Builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2022 Bitrix
	 */
	class BuilderDatabaseIndexedDB {
	  constructor(config = {}) {
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

	  get() {
	    return new Promise((resolve, reject) => {
	      this.db.data.where('code').equals(this.code).first().then(data => {
	        resolve(data ? data.value : null);
	      }, error => {
	        reject(error);
	      });
	    });
	  }

	  set(value) {
	    return new Promise((resolve, reject) => {
	      this.db.data.put({
	        code: this.code,
	        value
	      }).then(() => {
	        resolve(true);
	      }, error => {
	        reject(error);
	      });
	    });
	  }

	  clear() {
	    return new Promise((resolve, reject) => {
	      this.db.data.delete(this.code).then(() => {
	        resolve(true);
	      }, error => {
	        reject(error);
	      });
	    });
	  }

	}

	/**
	 * Bitrix Vuex wrapper
	 * LocalStorage driver for Vuex Builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2022 Bitrix
	 */
	class BuilderDatabaseLocalStorage {
	  constructor(config = {}) {
	    this.siteId = config.siteId || 'default';
	    this.userId = config.userId || 0;
	    this.storage = config.storage || 'default';
	    this.name = config.name || '';
	    this.enabled = false;

	    if (!main_core.Type.isUndefined(window.localStorage)) {
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

	  get() {
	    return new Promise((resolve, reject) => {
	      if (!this.enabled) {
	        resolve(null);
	        return true;
	      }

	      const result = window.localStorage.getItem(this.code);

	      if (!main_core.Type.isString(result)) {
	        resolve(null);
	        return true;
	      }

	      try {
	        resolve(this.prepareValueAfterGet(JSON.parse(result)));
	      } catch (error) {
	        reject(error);
	      }
	    });
	  }

	  set(value) {
	    return new Promise(resolve => {
	      if (this.enabled) {
	        window.localStorage.setItem(this.code, JSON.stringify(this.prepareValueBeforeSet(value)));
	      }

	      resolve(true);
	    });
	  }

	  clear() {
	    return new Promise(resolve => {
	      if (this.enabled) {
	        window.localStorage.removeItem(this.code);
	      }

	      resolve(true);
	    });
	  }
	  /**
	   * @private
	   */


	  prepareValueAfterGet(value) {
	    if (value instanceof Array) {
	      value = value.map(element => this.prepareValueAfterGet(element));
	    } else if (value instanceof Date) ; else if (main_core.Type.isObjectLike(value)) {
	      for (const index in value) {
	        if (value.hasOwnProperty(index)) {
	          value[index] = this.prepareValueAfterGet(value[index]);
	        }
	      }
	    } else if (main_core.Type.isString(value)) {
	      if (value.startsWith('#DT#')) {
	        value = new Date(value.substring(4));
	      }
	    }

	    return value;
	  }
	  /**
	   * @private
	   */


	  prepareValueBeforeSet(value) {
	    if (value instanceof Array) {
	      value = value.map(element => this.prepareValueBeforeSet(element));
	    } else if (value instanceof Date) {
	      value = '#DT#' + value.toISOString();
	    } else if (main_core.Type.isObjectLike(value)) {
	      for (const index in value) {
	        if (value.hasOwnProperty(index)) {
	          value[index] = this.prepareValueBeforeSet(value[index]);
	        }
	      }
	    }

	    return value;
	  }

	}

	/**
	 * Bitrix Vuex wrapper
	 * BitrixMobile ApplicationStorage driver for Vuex Builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2022 Bitrix
	 */
	class BuilderDatabaseJnSharedStorage {
	  constructor(config = {}) {
	    this.siteId = config.siteId || 'default';
	    this.userId = config.userId || 0;
	    this.storage = config.storage || 'default';
	    this.name = config.name || '';
	    this.code = (window.md5 || main_md5.md5)(this.siteId + '/' + this.userId + '/' + this.storage + '/' + this.name);

	    if (!this.isJnContext() && main_core.Type.isUndefined(ApplicationStorage)) {
	      console.error('ApplicationStorage is not defined, load "webcomponent/storage" extension.');
	    }
	  }

	  get() {
	    return new Promise(resolve => {
	      if (this.isJnContext()) {
	        const result = Application.sharedStorage.get(this.code);
	        resolve(result ? result : null);
	      } else if (!main_core.Type.isUndefined(ApplicationStorage)) {
	        ApplicationStorage.get(this.code, null).then(data => resolve(this.prepareValueAfterGet(JSON.parse(data))));
	      } else {
	        resolve(null);
	      }
	    });
	  }

	  set(value) {
	    return new Promise(resolve => {
	      if (this.isJnContext()) {
	        Application.sharedStorage().set(this.code, JSON.stringify(this.prepareValueBeforeSet(value)));
	        resolve();
	      } else if (!main_core.Type.isUndefined(ApplicationStorage)) {
	        ApplicationStorage.set(this.code, JSON.stringify(this.prepareValueBeforeSet(value))).then(() => resolve());
	      } else {
	        resolve();
	      }
	    });
	  }

	  clear() {
	    return this.set(null);
	  }
	  /**
	   * @private
	   */


	  isJnContext() {
	    return !main_core.Type.isUndefined(env);
	  }
	  /**
	   * @private
	   */


	  prepareValueAfterGet(value) {
	    if (value instanceof Array) {
	      value = value.map(element => this.prepareValueAfterGet(element));
	    } else if (value instanceof Date) ; else if (main_core.Type.isObjectLike(value)) {
	      for (const index in value) {
	        if (value.hasOwnProperty(index)) {
	          value[index] = this.prepareValueAfterGet(value[index]);
	        }
	      }
	    } else if (main_core.Type.isString(value)) {
	      if (value.startsWith('#DT#')) {
	        value = new Date(value.substring(4));
	      }
	    }

	    return value;
	  }
	  /**
	   * @private
	   */


	  prepareValueBeforeSet(value) {
	    if (value instanceof Array) {
	      value = value.map(element => this.prepareValueBeforeSet(element));
	    } else if (value instanceof Date) {
	      value = '#DT#' + value.toISOString();
	    } else if (main_core.Type.isObjectLike(value)) {
	      for (const index in value) {
	        if (value.hasOwnProperty(index)) {
	          value[index] = this.prepareValueBeforeSet(value[index]);
	        }
	      }
	    }

	    return value;
	  }

	}

	/**
	 * Bitrix Vuex wrapper
	 * Interface Vuex model (Vuex builder model)
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2022 Bitrix
	 */

	var _getStoreFromDatabase = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStoreFromDatabase");

	var _mergeState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mergeState");

	var _createStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createStore");

	class BuilderModel$$1 {
	  /**
	   * Create new instance of model.
	   *
	   * @returns {BuilderModel}
	   */
	  static create() {
	    return new this();
	  }
	  /**
	   * Get name of model
	   *
	   * @override
	   * @returns {String}
	   */


	  getName() {
	    return '';
	  }
	  /**
	   * Get default state
	   *
	  	 * @override
	   * @returns {Object}
	   */


	  getState() {
	    return {};
	  }
	  /**
	   * Get default element state for models with collection.
	   *
	  	 * @override
	   * @returns {Object}
	   */


	  getElementState() {
	    return {};
	  }
	  /**
	   * Get object containing fields to exclude during the save to database.
	   *
	  	 * @override
	   * @returns {Object|undefined}
	   */


	  getStateSaveException() {
	    return undefined;
	  }
	  /**
	   * Get getters
	  	 * @override
	   * @returns {Object}
	   */


	  getGetters() {
	    return {};
	  }
	  /**
	   * Get actions
	   *
	  	 * @override
	   * @returns {Object}
	   */


	  getActions() {
	    return {};
	  }
	  /**
	   * Get mutations
	   *
	  	 * @override
	   * @returns {Object}
	   */


	  getMutations() {
	    return {};
	  }
	  /**
	   * Method for validation and sanitizing input fields before save in model
	   *
	   * @override
	   * @param fields {Object}
	   * @param options {Object}
	   * @returns {Object} - Sanitizing fields
	   */


	  validate(fields, options = {}) {
	    return {};
	  }
	  /**
	   * Set external variable.
	   *
	   * @param variables {Object}
	   * @returns {BuilderModel}
	   */


	  setVariables(variables = {}) {
	    if (!main_core.Type.isObjectLike(variables)) {
	      this.logger('error', 'BuilderModel.setVariables: passed variables is not a Object', 'Model: ' + this.getName(), {
	        variables
	      }, store);
	      return this;
	    }

	    this.variables = variables;
	    return this;
	  }
	  /**
	   *
	   * @param name
	   * @param defaultValue
	   * @return {undefined|*}
	   */


	  getVariable(name, defaultValue = undefined) {
	    if (!name) {
	      return defaultValue;
	    }

	    const nameParts = name.toString().split('.');

	    if (nameParts.length === 1) {
	      return this.variables[nameParts[0]];
	    }

	    let result;
	    let variables = Object.assign({}, this.variables);

	    for (let i = 0; i < nameParts.length; i++) {
	      if (!main_core.Type.isUndefined(variables[nameParts[i]])) {
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


	  getNamespace() {
	    return this.namespace ? this.namespace : this.getName();
	  }
	  /**
	   * Set namespace
	   *
	   * @param name {String}
	   * @returns {BuilderModel}
	   */


	  setNamespace(name) {
	    this.namespace = name.toString();
	    this.databaseConfig.name = this.namespace;
	    return this;
	  }
	  /**
	   * Set database config for model or disable this feature.
	   *
	   * @param active {boolean}
	   * @param config {{name: String, siteId: String, userId: Number, type: BuilderDatabaseType, storage: String, siteId: String, userId: Number, timeout: Number}}
	   *
	   * @returns {BuilderModel}
	   */


	  useDatabase(active, config = {}) {
	    this.databaseConfig.active = !!active;
	    let updateDriver = this.db === null;

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

	    if (main_core.Type.isInteger(config.timeout)) {
	      this.databaseConfig.timeout = config.timeout;
	    }

	    if (!this.databaseConfig.active && this.db !== null) {
	      this.databaseConfig.type = null;
	      updateDriver = true;
	    }

	    if (updateDriver) {
	      if (this.databaseConfig.type === BuilderDatabaseType$$1.indexedDb) {
	        this.db = new BuilderDatabaseIndexedDB(this.databaseConfig);
	      } else if (this.databaseConfig.type === BuilderDatabaseType$$1.localStorage) {
	        this.db = new BuilderDatabaseLocalStorage(this.databaseConfig);
	      } else if (this.databaseConfig.type === BuilderDatabaseType$$1.jnSharedStorage) {
	        this.db = new BuilderDatabaseJnSharedStorage(this.databaseConfig);
	      } else {
	        this.db = null;
	      }
	    }

	    return this;
	  }
	  /**
	   * @returns {BuilderModel}
	   * @deprecated
	   */


	  useNamespace(active) {
	    if (ui_vue3.BitrixVue.developerMode) {
	      if (active) {
	        console.warn('BuilderModel: Method `useNamespace` is deprecated, please remove this call.');
	      } else {
	        console.error('BuilderModel: Method `useNamespace` is deprecated, using Vuex.Builder without namespaces is no longer supported.');
	      }
	    }

	    return this;
	  }
	  /**
	   * @returns {Promise}
	   * @deprecated use getModule instead.
	   */


	  getStore() {
	    console.warn('BuilderModel: Method `getStore` is deprecated, please remove this call.');
	    return this.getModule();
	  }
	  /**
	   * Get Vuex module.
	   *
	   * @returns {Promise}
	   */


	  getModule() {
	    return new Promise((resolve, reject) => {
	      const namespace = this.namespace ? this.namespace : this.getName();

	      if (!namespace) {
	        this.logger('error', 'VuexModel.getStore: current model can not be run in Vuex modules mode', this.getState());
	        reject();
	      }

	      if (this.db) {
	        babelHelpers.classPrivateFieldLooseBase(this, _getStoreFromDatabase)[_getStoreFromDatabase]().then(state => resolve({
	          namespace,
	          module: babelHelpers.classPrivateFieldLooseBase(this, _createStore)[_createStore](state)
	        }));
	      } else {
	        resolve({
	          namespace,
	          module: babelHelpers.classPrivateFieldLooseBase(this, _createStore)[_createStore](this.getState())
	        });
	      }
	    });
	  }
	  /**
	   * Get default state of Vuex module.
	   *
	   * @returns {Object}
	   */


	  getModuleWithDefaultState() {
	    const namespace = this.namespace ? this.namespace : this.getName();

	    if (!namespace) {
	      this.logger('error', 'VuexModel.getStore: current model can not be run in Vuex modules mode', this.getState());
	      return null;
	    }

	    return {
	      namespace,
	      module: babelHelpers.classPrivateFieldLooseBase(this, _createStore)[_createStore](this.getState())
	    };
	  }
	  /**
	   * Get timeout for save to database
	   *
	  	 * @override
	   *
	   * @returns {number}
	   */


	  getSaveTimeout() {
	    return 150;
	  }
	  /**
	   * Get timeout for load from database
	   *
	   * @override
	   *
	   * @returns {number|boolean}
	   */


	  getLoadTimeout() {
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


	  getLoadedState(state = {}) {
	    return state;
	  }
	  /**
	   * Save current state after change state to database
	   *
	  	 * @param state {Object|function}
	   *
	   * @returns {boolean}
	   */


	  saveState(state = {}) {
	    if (!this.isSaveAvailable()) {
	      return true;
	    }

	    this.lastSaveState = state;

	    if (this.saveStateTimeout) {
	      this.logger('log', 'VuexModel.saveState: wait save...', this.getName());
	      return true;
	    }

	    this.logger('log', 'VuexModel.saveState: start saving', this.getName());
	    let timeout = this.getSaveTimeout();

	    if (main_core.Type.isInteger(this.databaseConfig.timeout)) {
	      timeout = this.databaseConfig.timeout;
	    }

	    this.saveStateTimeout = setTimeout(() => {
	      this.logger('log', 'VuexModel.saveState: saved!', this.getName());
	      let lastState = this.lastSaveState;

	      if (main_core.Type.isFunction(lastState)) {
	        lastState = lastState();

	        if (!main_core.Type.isObjectLike(lastState) || !lastState) {
	          return false;
	        }
	      }

	      this.db.set(this.cloneState(lastState, this.getStateSaveException()));
	      this.lastState = null;
	      this.saveStateTimeout = null;
	    }, timeout);
	    return true;
	  }
	  /**
	   * Reset current store to default state
	   **
	   * @returns {Promise|boolean}
	   */


	  clearState() {
	    if (this.store) {
	      this.store.commit(this.getNamespace() + '/' + 'vuexBuilderModelClearState');
	      return true;
	    }

	    return this.saveState(this.getState());
	  }
	  /**
	   * Clear database only, store state does not change
	   **
	   * @returns {boolean}
	   */


	  clearDatabase() {
	    if (!this.isSaveAvailable()) {
	      return true;
	    }

	    this.db.clear();
	    return true;
	  }
	  /**
	   * @return boolean
	   */


	  isSaveAvailable() {
	    return this.db && this.databaseConfig.active;
	  }
	  /**
	   *
	   * @param payload
	   * @return {boolean}
	   */


	  isSaveNeeded(payload) {
	    if (!this.isSaveAvailable()) {
	      return false;
	    }

	    const checkFunction = function (payload, filter = null) {
	      if (!filter) {
	        return true;
	      }

	      for (const field in payload) {
	        if (!payload.hasOwnProperty(field)) {
	          continue;
	        }

	        if (main_core.Type.isUndefined(filter[field])) {
	          return true;
	        } else if (main_core.Type.isObjectLike(filter[field])) {
	          const result = checkFunction(payload[field], filter[field]);

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


	  constructor() {
	    Object.defineProperty(this, _createStore, {
	      value: _createStore2
	    });
	    Object.defineProperty(this, _getStoreFromDatabase, {
	      value: _getStoreFromDatabase2
	    });
	    this.databaseConfig = {
	      type: BuilderDatabaseType$$1.indexedDb,
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

	  setStore(store) {
	    if (!(store instanceof Store)) {
	      this.logger('error', 'VuexModel.setStore: passed store is not a Vuex.Store', store);
	      return this;
	    }

	    this.store = store;
	    return this;
	  }

	  /**
	   * Utils. Convert Object to Array
	   * @param object
	   * @returns {Array}
	   */
	  static convertToArray(object) {
	    const result = [];

	    for (const i in object) {
	      if (object.hasOwnProperty(i)) {
	        result.push(object[i]);
	      }
	    }

	    return result;
	  }
	  /**
	   * Clone state without observers
	   * @param element {object}
	   * @param exceptions {object}
	   */


	  cloneState(element, exceptions = undefined) {
	    let result;

	    if (element instanceof Array) {
	      result = [].concat(element.map(element => this.cloneState(element)));
	    } else if (element instanceof Date) {
	      result = new Date(element.toISOString());
	    } else if (main_core.Type.isObjectLike(element)) {
	      result = {};

	      for (const param in element) {
	        if (!element.hasOwnProperty(param)) {
	          continue;
	        }

	        if (main_core.Type.isUndefined(exceptions) || main_core.Type.isUndefined(exceptions[param])) {
	          result[param] = this.cloneState(element[param]);
	        } else if (main_core.Type.isObjectLike(exceptions[param])) {
	          result[param] = this.cloneState(element[param], exceptions[param]);
	        }
	      }
	    } else {
	      result = element;
	    }

	    return result;
	  }

	  logger(type, ...args) {
	    if (type === 'error') {
	      console.error(...args);
	      return;
	    } else if (!ui_vue3.BitrixVue.developerMode) {
	      return;
	    }

	    if (type === 'log') {
	      // eslint-disable-next-line no-console
	      console.log(...args);
	    } else if (type === 'info') {
	      console.info(...args);
	    } else if (type === 'warn') {
	      console.warn(...args);
	    }
	  }

	}

	function _getStoreFromDatabase2() {
	  clearTimeout(this.cacheTimeout);
	  return new Promise(resolve => {
	    const loadTimeout = this.getLoadTimeout();

	    if (loadTimeout !== false && main_core.Type.isInteger(loadTimeout)) {
	      this.cacheTimeout = setTimeout(() => {
	        this.logger('warn', 'VuexModel.getStoreFromDatabase: Cache loading timeout', this.getName());
	        resolve(this.getState());
	      }, loadTimeout);
	    } else {
	      this.cacheTimeout = null;
	    }

	    this.db.get().then(cache => {
	      clearTimeout(this.cacheTimeout);
	      cache = this.getLoadedState(cache ? cache : {});
	      let state = this.getState();

	      if (cache) {
	        state = babelHelpers.classPrivateFieldLooseBase(BuilderModel$$1, _mergeState)[_mergeState](state, cache);
	      }

	      resolve(state);
	    }, () => {
	      clearTimeout(this.cacheTimeout);
	      resolve(this.getState());
	    });
	  });
	}

	function _mergeState2(currentState, newState) {
	  for (const key in currentState) {
	    if (!currentState.hasOwnProperty(key)) {
	      continue;
	    }

	    if (main_core.Type.isUndefined(newState[key])) {
	      newState[key] = currentState[key];
	    } else if (!(newState[key] instanceof Array) && main_core.Type.isObjectLike(newState[key]) && main_core.Type.isObjectLike(currentState[key])) {
	      newState[key] = Object.assign({}, currentState[key], newState[key]);
	    }
	  }

	  return newState;
	}

	function _createStore2(state) {
	  const result = {
	    namespaced: true,
	    state,
	    getters: this.getGetters(),
	    actions: this.getActions(),
	    mutations: this.getMutations()
	  };

	  result.mutations.vuexBuilderModelClearState = state => {
	    state = Object.assign(state, this.getState());
	    this.saveState(state);
	  };

	  return result;
	}

	Object.defineProperty(BuilderModel$$1, _mergeState, {
	  value: _mergeState2
	});

	/**
	 * Bitrix Vuex wrapper
	 * Vuex builder
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2022 Bitrix
	 */
	const BuilderDatabaseType$$1 = Object.freeze({
	  indexedDb: 'indexedDb',
	  localStorage: 'localStorage',
	  jnSharedStorage: 'jnSharedStorage'
	});
	class Builder$$1 {
	  /**
	   * Create new instance of builder.
	   *
	   * @returns {Builder}
	   */
	  static create() {
	    if (BitrixVue.developerMode) {
	      console.warn('VuexBuilder: Method VuexBuilder.create is deprecated, use VuexBuilder.init instead.');
	    }

	    return new this();
	  }
	  /**
	   * Create new instance of builder and initialize Vuex store
	   *
	   * @param store {Vuex}
	   *
	   * @returns {Builder}
	   */


	  static init(store) {
	    if (store) {
	      if (!(store instanceof Vuex.Store)) {
	        console.warn('VuexBuilder.init: passed store is not a Vuex.Store', store);
	        return new this();
	      }
	    }

	    return new this(store);
	  }

	  constructor(store) {
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
	   * @param model {BuilderModel}
	   *
	   * @returns {Builder}
	   */


	  addModel(model) {
	    if (this.builded) {
	      return this;
	    }

	    if (!(model instanceof BuilderModel$$1)) {
	      console.error('VuexBuilder.addModel: passed model is not a BuilderModel', model);
	      return this;
	    }

	    this.models.push(model);
	    return this;
	  }
	  /**
	   * Add dynamic Vuex module.
	   *
	   * @param model {BuilderModel}
	   *
	   * @returns {Promise}
	   */


	  addDynamicModel(model) {
	    if (!(model instanceof BuilderModel$$1)) {
	      return new Promise((resolve, reject) => {
	        console.error('VuexBuilder.addDynamicModel: passed model is not a BuilderModel', model);
	        reject('MODEL_ERROR');
	      });
	    }

	    if (this.store.hasModule(model.getNamespace()) || this.models.find(stored => stored.getNamespace() === model.getNamespace())) {
	      return new Promise((resolve, reject) => {
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
	    const promise = model.getModule();
	    return new Promise((resolve, reject) => {
	      promise.then(result => {
	        this.store.registerModule(result.namespace, result.module);
	        resolve();
	      }, error => {
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
	   * @returns {Builder}
	   */


	  removeDynamicModel(namespace) {
	    if (!this.builded) {
	      console.error('BX.VuexBuilder.removeDynamicModel: you cannot use the method until builder is built.');
	      return this;
	    }

	    if (!this.store.hasModule(namespace)) {
	      console.error('BX.VuexBuilder.removeDynamicModel: module `' + namespace + '` not registered.');
	      return this;
	    }

	    this.models = this.models.filter(stored => stored.getNamespace() !== namespace);
	    this.store.unregisterModule(namespace);
	    return this;
	  }
	  /**
	   * @returns {Builder}
	   * @deprecated
	   */


	  useNamespace(active) {
	    if (BitrixVue.developerMode) {
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
	   * @param config {{name: String, siteId: String, userId: Number, type: DatabaseType, timeout: Number}}
	   * @returns {Builder}
	   */


	  setDatabaseConfig(config = {}) {
	    if (!main_core.Type.isObjectLike(config)) {
	      return this;
	    }

	    this.databaseConfig.active = true;
	    this.databaseConfig.storage = config.name;
	    this.databaseConfig.type = config.type || this.databaseConfig.type;
	    this.databaseConfig.siteId = config.siteId || this.databaseConfig.siteId;
	    this.databaseConfig.userId = config.userId || this.databaseConfig.userId;
	    this.databaseConfig.timeout = !main_core.Type.isUndefined(config.timeout) ? config.timeout : this.databaseConfig.timeout;
	    return this;
	  }

	  clearModelState(callback = null) {
	    if (!this.builded) {
	      return new Promise((resolve, reject) => {
	        console.error('BX.VuexBuilder.clearModelState: you cannot use the method until builder is built.');

	        if (!main_core.Type.isFunction(callback)) {
	          reject('BUILDER_NOT_BUILD');
	        }
	      });
	    }

	    const results = [];
	    this.models.forEach(model => {
	      results.push(model.clearState());
	    });
	    return new Promise((resolve, reject) => {
	      Promise.all(results).then(() => {
	        resolve(true);

	        if (main_core.Type.isFunction(callback)) {
	          callback(true);
	        }
	      }, error => {
	        console.error('BX.VuexBuilder.clearModelState: storage was not clear due to runtime errors.', error ? error : '');

	        if (!main_core.Type.isFunction(callback)) {
	          reject('ERROR_WHILE_CLEARING');
	        }
	      });
	    });
	  }

	  clearDatabase() {
	    if (!this.builded) {
	      return new Promise((resolve, reject) => {
	        console.error('BX.VuexBuilder.clearModelState: you cannot use the method until builder is built.');
	        reject('BUILDER_NOT_BUILD');
	      });
	    }

	    this.models.forEach(model => model.clearDatabase());
	    return new Promise(resolve => resolve(true));
	  }
	  /**
	   * Build Vuex Store asynchronously
	   *
	   * @param callback {Function|null}
	   * @returns {Promise<object>}
	   */


	  build(callback = null) {
	    if (this.builded) {
	      return this;
	    }

	    const promises = [];

	    if (!this.store) {
	      this.store = createStore();
	    }

	    this.models.forEach(model => {
	      if (this.databaseConfig.active && model.databaseConfig.active !== false) {
	        model.useDatabase(true, this.databaseConfig);
	      }

	      model.setStore(this.store);
	      promises.push(model.getModule());
	    });
	    return new Promise((resolve, reject) => {
	      Promise.all(promises).then(modules => {
	        modules.forEach(result => {
	          this.store.registerModule(result.namespace, result.module);
	        });
	        const result = {
	          store: this.store,
	          models: this.models,
	          builder: this
	        };
	        this.builded = true;

	        if (main_core.Type.isFunction(callback)) {
	          callback(result);
	        }

	        resolve(result);
	      }, error => {
	        console.error('BX.VuexBuilder.create: storage was not created due to runtime errors.', error ? error : '');

	        if (!main_core.Type.isFunction(callback)) {
	          reject('ERROR_IN_MODEL');
	        }
	      });
	    });
	  }
	  /**
	   * Build Vuex Store synchronously
	   *
	   * @returns {Object}
	   */


	  syncBuild() {
	    if (this.builded) {
	      return {
	        store: this.store,
	        models: this.models,
	        builder: this
	      };
	    }

	    if (!this.store) {
	      this.store = createStore();
	    }

	    if (this.databaseConfig.active) {
	      if (BitrixVue.developerMode) {
	        console.error('VuexBuilder: Method `syncBuild` creates storage in synchronous mode, the database does not work in this mode.');
	      }

	      this.databaseConfig.active = false;
	    }

	    this.models.forEach(model => {
	      model.useDatabase(false);
	      model.setStore(this.store);
	      const {
	        namespace,
	        module
	      } = model.getModuleWithDefaultState();
	      this.store.registerModule(namespace, module);
	    });
	    this.builded = true;
	    return {
	      store: this.store,
	      models: this.models,
	      builder: this
	    };
	  }

	}

	/*!
	 * vuex v4.0.2
	 * (c) 2021 Evan You
	 * @license MIT
	 *
	 * @source: https://unpkg.com/vuex@4.0.2/dist/vuex.esm-browser.js
	 */

	function getDevtoolsGlobalHook() {
	  return getTarget().__VUE_DEVTOOLS_GLOBAL_HOOK__;
	}

	function getTarget() {
	  // @ts-ignore
	  return typeof navigator !== 'undefined' ? window : typeof global !== 'undefined' ? global : {};
	}

	const HOOK_SETUP = 'devtools-plugin:setup';

	function setupDevtoolsPlugin(pluginDescriptor, setupFn) {
	  const hook = getDevtoolsGlobalHook();

	  if (hook) {
	    hook.emit(HOOK_SETUP, pluginDescriptor, setupFn);
	  } else {
	    const target = getTarget();
	    const list = target.__VUE_DEVTOOLS_PLUGINS__ = target.__VUE_DEVTOOLS_PLUGINS__ || [];
	    list.push({
	      pluginDescriptor,
	      setupFn
	    });
	  }
	} // origin-start


	var storeKey = 'store';

	function useStore(key) {
	  if (key === void 0) key = null;
	  return ui_vue3.inject(key !== null ? key : storeKey);
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


	function deepCopy(obj, cache) {
	  if (cache === void 0) cache = []; // just return if obj is immutable value

	  if (obj === null || typeof obj !== 'object') {
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
	  return obj !== null && typeof obj === 'object';
	}

	function isPromise(val) {
	  return val && typeof val.then === 'function';
	}

	function assert(condition, msg) {
	  if (!condition) {
	    throw new Error("[vuex] " + msg);
	  }
	}

	function partial(fn, arg) {
	  return function () {
	    return fn(arg);
	  };
	}

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

	  installModule(store, state, [], store._modules.root, true); // reset state

	  resetStoreState(store, state, hot);
	}

	function resetStoreState(store, state, hot) {
	  var oldState = store._state; // bind store public getters

	  store.getters = {}; // reset local getters cache

	  store._makeLocalGettersCache = Object.create(null);
	  var wrappedGetters = store._wrappedGetters;
	  var computedObj = {};
	  forEachValue(wrappedGetters, function (fn, key) {
	    // use computed to leverage its lazy-caching mechanism
	    // direct inline function use will lead to closure preserving oldState.
	    // using partial to return function with only arguments preserved in closure environment.
	    computedObj[key] = partial(fn, store);
	    Object.defineProperty(store.getters, key, {
	      // TODO: use `computed` when it's possible. at the moment we can't due to
	      // https://github.com/vuejs/vuex/pull/1883
	      get: function () {
	        return computedObj[key]();
	      },
	      enumerable: true // for local getters

	    });
	  });
	  store._state = ui_vue3.reactive({
	    data: state
	  }); // enable strict mode for new state

	  if (store.strict) {
	    enableStrictMode(store);
	  }

	  if (oldState) {
	    if (hot) {
	      // dispatch changes in all subscribed watchers
	      // to force getter re-evaluation for hot reloading.
	      store._withCommit(function () {
	        oldState.data = null;
	      });
	    }
	  }
	}

	function installModule(store, rootState, path, module, hot) {
	  var isRoot = !path.length;

	  var namespace = store._modules.getNamespace(path); // register in namespace map


	  if (module.namespaced) {
	    if (store._modulesNamespaceMap[namespace] && true) {
	      console.error("[vuex] duplicate namespace " + namespace + " for the namespaced module " + path.join('/'));
	    }

	    store._modulesNamespaceMap[namespace] = module;
	  } // set state


	  if (!isRoot && !hot) {
	    var parentState = getNestedState(rootState, path.slice(0, -1));
	    var moduleName = path[path.length - 1];

	    store._withCommit(function () {
	      {
	        if (moduleName in parentState) {
	          console.warn("[vuex] state field \"" + moduleName + "\" was overridden by a module with the same name at \"" + path.join('.') + "\"");
	        }
	      }
	      parentState[moduleName] = module.state;
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
	      var payload = args.payload;
	      var options = args.options;
	      var type = args.type;

	      if (!options || !options.root) {
	        type = namespace + type;

	        if (!store._actions[type]) {
	          console.error("[vuex] unknown local action type: " + args.type + ", global type: " + type);
	          return;
	        }
	      }

	      return store.dispatch(type, payload);
	    },
	    commit: noNamespace ? store.commit : function (_type, _payload, _options) {
	      var args = unifyObjectStyle(_type, _payload, _options);
	      var payload = args.payload;
	      var options = args.options;
	      var type = args.type;

	      if (!options || !options.root) {
	        type = namespace + type;

	        if (!store._mutations[type]) {
	          console.error("[vuex] unknown local mutation type: " + args.type + ", global type: " + type);
	          return;
	        }
	      }

	      store.commit(type, payload, options);
	    }
	  }; // getters and state object must be gotten lazily
	  // because they will be changed by state update

	  Object.defineProperties(local, {
	    getters: {
	      get: noNamespace ? function () {
	        return store.getters;
	      } : function () {
	        return makeLocalGetters(store, namespace);
	      }
	    },
	    state: {
	      get: function () {
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
	      if (type.slice(0, splitPos) !== namespace) {
	        return;
	      } // extract local getter type


	      var localType = type.slice(splitPos); // Add a port to the getters proxy.
	      // Define as getter property because
	      // we do not want to evaluate the getters in this time.

	      Object.defineProperty(gettersProxy, localType, {
	        get: function () {
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
	      return res.catch(function (err) {
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
	      console.error("[vuex] duplicate getter key: " + type);
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
	  ui_vue3.watch(function () {
	    return store._state.data;
	  }, function () {
	    {
	      assert(store._committing, "do not mutate vuex store state outside mutation handlers.");
	    }
	  }, {
	    deep: true,
	    flush: 'sync'
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
	    assert(typeof type === 'string', "expects string as the type, but found " + typeof type + ".");
	  }
	  return {
	    type: type,
	    payload: payload,
	    options: options
	  };
	}

	var LABEL_VUEX_BINDINGS = 'vuex bindings';
	var MUTATIONS_LAYER_ID = 'vuex:mutations';
	var ACTIONS_LAYER_ID = 'vuex:actions';
	var INSPECTOR_ID = 'vuex';
	var actionId = 0;

	function addDevtools(app, store) {
	  setupDevtoolsPlugin({
	    id: 'org.vuejs.vuex',
	    app: app,
	    label: 'Vuex',
	    homepage: 'https://next.vuex.vuejs.org/',
	    logo: 'https://vuejs.org/images/icons/favicon-96x96.png',
	    packageName: 'vuex',
	    componentStateTypes: [LABEL_VUEX_BINDINGS]
	  }, function (api) {
	    api.addTimelineLayer({
	      id: MUTATIONS_LAYER_ID,
	      label: 'Vuex Mutations',
	      color: COLOR_LIME_500
	    });
	    api.addTimelineLayer({
	      id: ACTIONS_LAYER_ID,
	      label: 'Vuex Actions',
	      color: COLOR_LIME_500
	    });
	    api.addInspector({
	      id: INSPECTOR_ID,
	      label: 'Vuex',
	      icon: 'storage',
	      treeFilterPlaceholder: 'Filter stores...'
	    });
	    api.on.getInspectorTree(function (payload) {
	      if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
	        if (payload.filter) {
	          var nodes = [];
	          flattenStoreForInspectorTree(nodes, store._modules.root, payload.filter, '');
	          payload.rootNodes = nodes;
	        } else {
	          payload.rootNodes = [formatStoreForInspectorTree(store._modules.root, '')];
	        }
	      }
	    });
	    api.on.getInspectorState(function (payload) {
	      if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
	        var modulePath = payload.nodeId;
	        makeLocalGetters(store, modulePath);
	        payload.state = formatStoreForInspectorState(getStoreModule(store._modules, modulePath), modulePath === 'root' ? store.getters : store._makeLocalGettersCache, modulePath);
	      }
	    });
	    api.on.editInspectorState(function (payload) {
	      if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
	        var modulePath = payload.nodeId;
	        var path = payload.path;

	        if (modulePath !== 'root') {
	          path = modulePath.split('/').filter(Boolean).concat(path);
	        }

	        store._withCommit(function () {
	          payload.set(store._state.data, path, payload.state.value);
	        });
	      }
	    });
	    store.subscribe(function (mutation, state) {
	      var data = {};

	      if (mutation.payload) {
	        data.payload = mutation.payload;
	      }

	      data.state = state;
	      api.notifyComponentUpdate();
	      api.sendInspectorTree(INSPECTOR_ID);
	      api.sendInspectorState(INSPECTOR_ID);
	      api.addTimelineEvent({
	        layerId: MUTATIONS_LAYER_ID,
	        event: {
	          time: Date.now(),
	          title: mutation.type,
	          data: data
	        }
	      });
	    });
	    store.subscribeAction({
	      before: function (action, state) {
	        var data = {};

	        if (action.payload) {
	          data.payload = action.payload;
	        }

	        action._id = actionId++;
	        action._time = Date.now();
	        data.state = state;
	        api.addTimelineEvent({
	          layerId: ACTIONS_LAYER_ID,
	          event: {
	            time: action._time,
	            title: action.type,
	            groupId: action._id,
	            subtitle: 'start',
	            data: data
	          }
	        });
	      },
	      after: function (action, state) {
	        var data = {};

	        var duration = Date.now() - action._time;

	        data.duration = {
	          _custom: {
	            type: 'duration',
	            display: duration + "ms",
	            tooltip: 'Action duration',
	            value: duration
	          }
	        };

	        if (action.payload) {
	          data.payload = action.payload;
	        }

	        data.state = state;
	        api.addTimelineEvent({
	          layerId: ACTIONS_LAYER_ID,
	          event: {
	            time: Date.now(),
	            title: action.type,
	            groupId: action._id,
	            subtitle: 'end',
	            data: data
	          }
	        });
	      }
	    });
	  });
	} // extracted from tailwind palette


	var COLOR_LIME_500 = 0x84cc16;
	var COLOR_DARK = 0x666666;
	var COLOR_WHITE = 0xffffff;
	var TAG_NAMESPACED = {
	  label: 'namespaced',
	  textColor: COLOR_WHITE,
	  backgroundColor: COLOR_DARK
	};
	/**
	 * @param {string} path
	 */

	function extractNameFromPath(path) {
	  return path && path !== 'root' ? path.split('/').slice(-2, -1)[0] : 'Root';
	}
	/**
	 * @param {*} module
	 * @return {import('@vue/devtools-api').CustomInspectorNode}
	 */


	function formatStoreForInspectorTree(module, path) {
	  return {
	    id: path || 'root',
	    // all modules end with a `/`, we want the last segment only
	    // cart/ -> cart
	    // nested/cart/ -> cart
	    label: extractNameFromPath(path),
	    tags: module.namespaced ? [TAG_NAMESPACED] : [],
	    children: Object.keys(module._children).map(function (moduleName) {
	      return formatStoreForInspectorTree(module._children[moduleName], path + moduleName + '/');
	    })
	  };
	}
	/**
	 * @param {import('@vue/devtools-api').CustomInspectorNode[]} result
	 * @param {*} module
	 * @param {string} filter
	 * @param {string} path
	 */


	function flattenStoreForInspectorTree(result, module, filter, path) {
	  if (path.includes(filter)) {
	    result.push({
	      id: path || 'root',
	      label: path.endsWith('/') ? path.slice(0, path.length - 1) : path || 'Root',
	      tags: module.namespaced ? [TAG_NAMESPACED] : []
	    });
	  }

	  Object.keys(module._children).forEach(function (moduleName) {
	    flattenStoreForInspectorTree(result, module._children[moduleName], filter, path + moduleName + '/');
	  });
	}
	/**
	 * @param {*} module
	 * @return {import('@vue/devtools-api').CustomInspectorState}
	 */


	function formatStoreForInspectorState(module, getters, path) {
	  getters = path === 'root' ? getters : getters[path];
	  var gettersKeys = Object.keys(getters);
	  var storeState = {
	    state: Object.keys(module.state).map(function (key) {
	      return {
	        key: key,
	        editable: true,
	        value: module.state[key]
	      };
	    })
	  };

	  if (gettersKeys.length) {
	    var tree = transformPathsToObjectTree(getters);
	    storeState.getters = Object.keys(tree).map(function (key) {
	      return {
	        key: key.endsWith('/') ? extractNameFromPath(key) : key,
	        editable: false,
	        value: canThrow(function () {
	          return tree[key];
	        })
	      };
	    });
	  }

	  return storeState;
	}

	function transformPathsToObjectTree(getters) {
	  var result = {};
	  Object.keys(getters).forEach(function (key) {
	    var path = key.split('/');

	    if (path.length > 1) {
	      var target = result;
	      var leafKey = path.pop();
	      path.forEach(function (p) {
	        if (!target[p]) {
	          target[p] = {
	            _custom: {
	              value: {},
	              display: p,
	              tooltip: 'Module',
	              abstract: true
	            }
	          };
	        }

	        target = target[p]._custom.value;
	      });
	      target[leafKey] = canThrow(function () {
	        return getters[key];
	      });
	    } else {
	      result[key] = canThrow(function () {
	        return getters[key];
	      });
	    }
	  });
	  return result;
	}

	function getStoreModule(moduleMap, path) {
	  var names = path.split('/').filter(function (n) {
	    return n;
	  });
	  return names.reduce(function (module, moduleName, i) {
	    var child = module[moduleName];

	    if (!child) {
	      throw new Error("Missing module \"" + moduleName + "\" for path \"" + path + "\".");
	    }

	    return i === names.length - 1 ? child : child._children;
	  }, path === 'root' ? moduleMap : moduleMap.root._children);
	}

	function canThrow(cb) {
	  try {
	    return cb();
	  } catch (e) {
	    return e;
	  }
	} // Base data struct for store's module, package with some attribute and method


	var Module = function Module(rawModule, runtime) {
	  this.runtime = runtime; // Store some children item

	  this._children = Object.create(null); // Store the origin module object which passed by programmer

	  this._rawModule = rawModule;
	  var rawState = rawModule.state; // Store the origin module's state

	  this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
	};

	var prototypeAccessors$1 = {
	  namespaced: {
	    configurable: true
	  }
	};

	prototypeAccessors$1.namespaced.get = function () {
	  return !!this._rawModule.namespaced;
	};

	Module.prototype.addChild = function addChild(key, module) {
	  this._children[key] = module;
	};

	Module.prototype.removeChild = function removeChild(key) {
	  delete this._children[key];
	};

	Module.prototype.getChild = function getChild(key) {
	  return this._children[key];
	};

	Module.prototype.hasChild = function hasChild(key) {
	  return key in this._children;
	};

	Module.prototype.update = function update(rawModule) {
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
	};

	Module.prototype.forEachChild = function forEachChild(fn) {
	  forEachValue(this._children, fn);
	};

	Module.prototype.forEachGetter = function forEachGetter(fn) {
	  if (this._rawModule.getters) {
	    forEachValue(this._rawModule.getters, fn);
	  }
	};

	Module.prototype.forEachAction = function forEachAction(fn) {
	  if (this._rawModule.actions) {
	    forEachValue(this._rawModule.actions, fn);
	  }
	};

	Module.prototype.forEachMutation = function forEachMutation(fn) {
	  if (this._rawModule.mutations) {
	    forEachValue(this._rawModule.mutations, fn);
	  }
	};

	Object.defineProperties(Module.prototype, prototypeAccessors$1);

	var ModuleCollection = function ModuleCollection(rawRootModule) {
	  // register root module (Vuex.Store options)
	  this.register([], rawRootModule, false);
	};

	ModuleCollection.prototype.get = function get(path) {
	  return path.reduce(function (module, key) {
	    return module.getChild(key);
	  }, this.root);
	};

	ModuleCollection.prototype.getNamespace = function getNamespace(path) {
	  var module = this.root;
	  return path.reduce(function (namespace, key) {
	    module = module.getChild(key);
	    return namespace + (module.namespaced ? key + '/' : '');
	  }, '');
	};

	ModuleCollection.prototype.update = function update$1(rawRootModule) {
	  update([], this.root, rawRootModule);
	};

	ModuleCollection.prototype.register = function register(path, rawModule, runtime) {
	  var this$1$1 = this;
	  if (runtime === void 0) runtime = true;
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
	      this$1$1.register(path.concat(key), rawChildModule, runtime);
	    });
	  }
	};

	ModuleCollection.prototype.unregister = function unregister(path) {
	  var parent = this.get(path.slice(0, -1));
	  var key = path[path.length - 1];
	  var child = parent.getChild(key);

	  if (!child) {
	    {
	      console.warn("[vuex] trying to unregister module '" + key + "', which is " + "not registered");
	    }
	    return;
	  }

	  if (!child.runtime) {
	    return;
	  }

	  parent.removeChild(key);
	};

	ModuleCollection.prototype.isRegistered = function isRegistered(path) {
	  var parent = this.get(path.slice(0, -1));
	  var key = path[path.length - 1];

	  if (parent) {
	    return parent.hasChild(key);
	  }

	  return false;
	};

	function update(path, targetModule, newModule) {
	  {
	    assertRawModule(path, newModule);
	  } // update target module

	  targetModule.update(newModule); // update nested modules

	  if (newModule.modules) {
	    for (var key in newModule.modules) {
	      if (!targetModule.getChild(key)) {
	        {
	          console.warn("[vuex] trying to add a new module '" + key + "' on hot reloading, " + 'manual reload is needed');
	        }
	        return;
	      }

	      update(path.concat(key), targetModule.getChild(key), newModule.modules[key]);
	    }
	  }
	}

	var functionAssert = {
	  assert: function (value) {
	    return typeof value === 'function';
	  },
	  expected: 'function'
	};
	var objectAssert = {
	  assert: function (value) {
	    return typeof value === 'function' || typeof value === 'object' && typeof value.handler === 'function';
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
	    if (!rawModule[key]) {
	      return;
	    }

	    var assertOptions = assertTypes[key];
	    forEachValue(rawModule[key], function (value, type) {
	      assert(assertOptions.assert(value), makeAssertionMessage(path, key, type, value, assertOptions.expected));
	    });
	  });
	}

	function makeAssertionMessage(path, key, type, value, expected) {
	  var buf = key + " should be " + expected + " but \"" + key + "." + type + "\"";

	  if (path.length > 0) {
	    buf += " in module \"" + path.join('.') + "\"";
	  }

	  buf += " is " + JSON.stringify(value) + ".";
	  return buf;
	}

	function createStore(options) {
	  return new Store(options);
	}

	var Store = function Store(options) {
	  var this$1$1 = this;
	  if (options === void 0) options = {};
	  {
	    assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
	    assert(this instanceof Store, "store must be called with the new operator.");
	  }
	  var plugins = options.plugins;
	  if (plugins === void 0) plugins = [];
	  var strict = options.strict;
	  if (strict === void 0) strict = false;
	  var devtools = options.devtools; // store internal state

	  this._committing = false;
	  this._actions = Object.create(null);
	  this._actionSubscribers = [];
	  this._mutations = Object.create(null);
	  this._wrappedGetters = Object.create(null);
	  this._modules = new ModuleCollection(options);
	  this._modulesNamespaceMap = Object.create(null);
	  this._subscribers = [];
	  this._makeLocalGettersCache = Object.create(null);
	  this._devtools = devtools; // bind commit and dispatch to self

	  var store = this;
	  var ref = this;
	  var dispatch = ref.dispatch;
	  var commit = ref.commit;

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

	  installModule(this, state, [], this._modules.root); // initialize the store state, which is responsible for the reactivity
	  // (also registers _wrappedGetters as computed properties)

	  resetStoreState(this, state); // apply plugins

	  plugins.forEach(function (plugin) {
	    return plugin(this$1$1);
	  });
	};

	var prototypeAccessors = {
	  state: {
	    configurable: true
	  }
	};

	Store.prototype.install = function install(app, injectKey) {
	  app.provide(injectKey || storeKey, this);
	  app.config.globalProperties.$store = this;
	  var useDevtools = this._devtools !== undefined ? this._devtools : true;

	  if (useDevtools) {
	    addDevtools(app, this);
	  }
	};

	prototypeAccessors.state.get = function () {
	  return this._state.data;
	};

	prototypeAccessors.state.set = function (v) {
	  {
	    assert(false, "use store.replaceState() to explicit replace store state.");
	  }
	};

	Store.prototype.commit = function commit(_type, _payload, _options) {
	  var this$1$1 = this; // check object-style commit

	  var ref = unifyObjectStyle(_type, _payload, _options);
	  var type = ref.type;
	  var payload = ref.payload;
	  var options = ref.options;
	  var mutation = {
	    type: type,
	    payload: payload
	  };
	  var entry = this._mutations[type];

	  if (!entry) {
	    {
	      console.error("[vuex] unknown mutation type: " + type);
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
	    return sub(mutation, this$1$1.state);
	  });

	  if (options && options.silent) {
	    console.warn("[vuex] mutation type: " + type + ". Silent option has been removed. " + 'Use the filter functionality in the vue-devtools');
	  }
	};

	Store.prototype.dispatch = function dispatch(_type, _payload) {
	  var this$1$1 = this; // check object-style dispatch

	  var ref = unifyObjectStyle(_type, _payload);
	  var type = ref.type;
	  var payload = ref.payload;
	  var action = {
	    type: type,
	    payload: payload
	  };
	  var entry = this._actions[type];

	  if (!entry) {
	    {
	      console.error("[vuex] unknown action type: " + type);
	    }
	    return;
	  }

	  try {
	    this._actionSubscribers.slice() // shallow copy to prevent iterator invalidation if subscriber synchronously calls unsubscribe
	    .filter(function (sub) {
	      return sub.before;
	    }).forEach(function (sub) {
	      return sub.before(action, this$1$1.state);
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
	        this$1$1._actionSubscribers.filter(function (sub) {
	          return sub.after;
	        }).forEach(function (sub) {
	          return sub.after(action, this$1$1.state);
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
	        this$1$1._actionSubscribers.filter(function (sub) {
	          return sub.error;
	        }).forEach(function (sub) {
	          return sub.error(action, this$1$1.state, error);
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
	};

	Store.prototype.subscribe = function subscribe(fn, options) {
	  return genericSubscribe(fn, this._subscribers, options);
	};

	Store.prototype.subscribeAction = function subscribeAction(fn, options) {
	  var subs = typeof fn === 'function' ? {
	    before: fn
	  } : fn;
	  return genericSubscribe(subs, this._actionSubscribers, options);
	};

	Store.prototype.watch = function watch$1(getter, cb, options) {
	  var this$1$1 = this;
	  {
	    assert(typeof getter === 'function', "store.watch only accepts a function.");
	  }
	  return ui_vue3.watch(function () {
	    return getter(this$1$1.state, this$1$1.getters);
	  }, cb, Object.assign({}, options));
	};

	Store.prototype.replaceState = function replaceState(state) {
	  var this$1$1 = this;

	  this._withCommit(function () {
	    this$1$1._state.data = state;
	  });
	};

	Store.prototype.registerModule = function registerModule(path, rawModule, options) {
	  if (options === void 0) options = {};

	  if (typeof path === 'string') {
	    path = [path];
	  }

	  {
	    assert(Array.isArray(path), "module path must be a string or an Array.");
	    assert(path.length > 0, 'cannot register the root module by using registerModule.');
	  }

	  this._modules.register(path, rawModule);

	  installModule(this, this.state, path, this._modules.get(path), options.preserveState); // reset store to update getters...

	  resetStoreState(this, this.state);
	};

	Store.prototype.unregisterModule = function unregisterModule(path) {
	  var this$1$1 = this;

	  if (typeof path === 'string') {
	    path = [path];
	  }

	  {
	    assert(Array.isArray(path), "module path must be a string or an Array.");
	  }

	  this._modules.unregister(path);

	  this._withCommit(function () {
	    var parentState = getNestedState(this$1$1.state, path.slice(0, -1));
	    delete parentState[path[path.length - 1]];
	  });

	  resetStore(this);
	};

	Store.prototype.hasModule = function hasModule(path) {
	  if (typeof path === 'string') {
	    path = [path];
	  }

	  {
	    assert(Array.isArray(path), "module path must be a string or an Array.");
	  }
	  return this._modules.isRegistered(path);
	};

	Store.prototype.hotUpdate = function hotUpdate(newOptions) {
	  this._modules.update(newOptions);

	  resetStore(this, true);
	};

	Store.prototype._withCommit = function _withCommit(fn) {
	  var committing = this._committing;
	  this._committing = true;
	  fn();
	  this._committing = committing;
	};

	Object.defineProperties(Store.prototype, prototypeAccessors);
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

	  normalizeMap(states).forEach(function (ref) {
	    var key = ref.key;
	    var val = ref.val;

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

	  normalizeMap(mutations).forEach(function (ref) {
	    var key = ref.key;
	    var val = ref.val;

	    res[key] = function mappedMutation() {
	      var args = [],
	          len = arguments.length;

	      while (len--) args[len] = arguments[len]; // Get the commit method from store


	      var commit = this.$store.commit;

	      if (namespace) {
	        var module = getModuleByNamespace(this.$store, 'mapMutations', namespace);

	        if (!module) {
	          return;
	        }

	        commit = module.context.commit;
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

	  normalizeMap(getters).forEach(function (ref) {
	    var key = ref.key;
	    var val = ref.val; // The namespace has been mutated by normalizeNamespace

	    val = namespace + val;

	    res[key] = function mappedGetter() {
	      if (namespace && !getModuleByNamespace(this.$store, 'mapGetters', namespace)) {
	        return;
	      }

	      if (!(val in this.$store.getters)) {
	        console.error("[vuex] unknown getter: " + val);
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

	  normalizeMap(actions).forEach(function (ref) {
	    var key = ref.key;
	    var val = ref.val;

	    res[key] = function mappedAction() {
	      var args = [],
	          len = arguments.length;

	      while (len--) args[len] = arguments[len]; // get dispatch function from store


	      var dispatch = this.$store.dispatch;

	      if (namespace) {
	        var module = getModuleByNamespace(this.$store, 'mapActions', namespace);

	        if (!module) {
	          return;
	        }

	        dispatch = module.context.dispatch;
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

	var createNamespacedHelpers = function (namespace) {
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
	    console.error("[vuex] module namespace not found in " + helper + "(): " + namespace);
	  }

	  return module;
	} // Credits: borrowed code from fcomb/redux-logger


	function createLogger(ref) {
	  if (ref === void 0) ref = {};
	  var collapsed = ref.collapsed;
	  if (collapsed === void 0) collapsed = true;
	  var filter = ref.filter;
	  if (filter === void 0) filter = function (mutation, stateBefore, stateAfter) {
	    return true;
	  };
	  var transformer = ref.transformer;
	  if (transformer === void 0) transformer = function (state) {
	    return state;
	  };
	  var mutationTransformer = ref.mutationTransformer;
	  if (mutationTransformer === void 0) mutationTransformer = function (mut) {
	    return mut;
	  };
	  var actionFilter = ref.actionFilter;
	  if (actionFilter === void 0) actionFilter = function (action, state) {
	    return true;
	  };
	  var actionTransformer = ref.actionTransformer;
	  if (actionTransformer === void 0) actionTransformer = function (act) {
	    return act;
	  };
	  var logMutations = ref.logMutations;
	  if (logMutations === void 0) logMutations = true;
	  var logActions = ref.logActions;
	  if (logActions === void 0) logActions = true;
	  var logger = ref.logger;
	  if (logger === void 0) logger = console;
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
	          var message = "mutation " + mutation.type + formattedTime;
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
	          var message = "action " + action.type + formattedTime;
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
	    logger.log('--- log end ---');
	  }
	}

	function getFormattedTime() {
	  var time = new Date();
	  return " @ " + pad(time.getHours(), 2) + ":" + pad(time.getMinutes(), 2) + ":" + pad(time.getSeconds(), 2) + "." + pad(time.getMilliseconds(), 3);
	}

	function repeat(str, times) {
	  return new Array(times + 1).join(str);
	}

	function pad(num, maxLength) {
	  return repeat('0', maxLength - num.toString().length) + num;
	}

	const version = '4.0.2';

	exports.Builder = Builder$$1;
	exports.BuilderModel = BuilderModel$$1;
	exports.BuilderDatabaseType = BuilderDatabaseType$$1;
	exports.Store = Store;
	exports.createLogger = createLogger;
	exports.createNamespacedHelpers = createNamespacedHelpers;
	exports.createStore = createStore;
	exports.mapActions = mapActions;
	exports.mapGetters = mapGetters;
	exports.mapMutations = mapMutations;
	exports.mapState = mapState;
	exports.storeKey = storeKey;
	exports.useStore = useStore;
	exports.version = version;

}((this.BX.Vue3.Vuex = this.BX.Vue3.Vuex || {}),BX,BX,BX,BX.Vue3));



})();
//# sourceMappingURL=vuex.bundle.js.map