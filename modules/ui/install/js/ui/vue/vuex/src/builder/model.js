/**
 * Bitrix Vuex wrapper
 * Interface Vuex model (Vuex builder model)
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import {VuexBuilderDatabaseIndexedDB} from "./database/indexeddb.js";
import {VuexBuilderDatabaseLocalStorage} from "./database/localstorage.js";
import {VuexBuilderDatabaseJnSharedStorage} from "./database/jnsharedstorage.js";
import {VuexBuilder} from "./builder.js";
import {VuexVendor} from "ui.vue.vuex";

export class VuexBuilderModel
{
	/**
	 * Create new instance of model.
	 *
	 * @returns {VuexBuilderModel}
	 */
	static create()
	{
		return new this;
	}

	/**
	 * Get name of model
	 *
	 * @override
	 *
	 * @returns {String}
	 */
	getName()
	{
		return '';
	}

	/**
	 * Get default state
	 *
 	 * @override
	 *
	 * @returns {Object}
	 */
	getState()
	{
		return {};
	}

	/**
	 * Get default element state for models with collection.
	 *
 	 * @override
	 *
	 * @returns {Object}
	 */
	getElementState()
	{
		return {};
	}

	/**
	 * Get object containing fields to exclude during the save to database.
	 *
 	 * @override
	 *
	 * @returns {Object}
	 */
	getStateSaveException()
	{
		return undefined;
	}

	/**
	 * Get getters
	 *
 	 * @override
	 *
	 * @returns {Object}
	 */
	getGetters()
	{
		return {};
	}

	/**
	 * Get mutations
	 *
 	 * @override
	 *
	 * @returns {Object}
	 */
	getActions()
	{
		return {};
	}

	/**
	 * Get mutations
	 *
 	 * @override
	 *
	 * @returns {Object}
	 */
	getMutations()
	{
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
	validate(fields, options = {})
	{
		return {};
	}

	/**
	 * Set external variable.
	 *
	 * @param variables {Object}
	 * @returns {VuexBuilder}
	 */
	setVariables(variables = {})
	{
		if (!(typeof variables === 'object' && variables))
		{
			this.logger('error', 'VuexBuilderModel.setVars: passed variables is not a Object', store);
			return this;
		}

		this.variables = variables;

		return this;
	}

	getVariable(name, defaultValue = undefined)
	{
		if (!name)
		{
			return defaultValue;
		}

		let nameParts = name.toString().split('.');
		if (nameParts.length === 1)
		{
			return this.variables[nameParts[0]];
		}

		let result;
		let variables = Object.assign({}, this.variables);

		for (let i = 0; i < nameParts.length; i++)
		{
			if (typeof variables[nameParts[i]] !== 'undefined')
			{
				variables = result = variables[nameParts[i]];
			}
			else
			{
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
	getNamespace()
	{
		return this.namespace? this.namespace: this.getName();
	}

	/**
	 * Set namespace
	 *
	 * @param name {String}
	 *
	 * @returns {VuexBuilderModel}
	 */
	setNamespace(name)
	{
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
	 * @returns {VuexBuilder}
	 */
	useDatabase(active, config = {})
	{
		this.databaseConfig.active = !!active;

		let updateDriver = this.db === null;
		if (config.type)
		{
			this.databaseConfig.type = config.type.toString();
			updateDriver = true;
		}
		if (config.storage)
		{
			this.databaseConfig.storage = config.storage.toString();
		}
		if (config.siteId)
		{
			this.databaseConfig.siteId = config.siteId.toString();
		}
		if (config.userId)
		{
			this.databaseConfig.userId = config.userId;
		}
		if (typeof config.timeout === 'number')
		{
			this.databaseConfig.timeout = config.timeout;
		}

		if (updateDriver)
		{
			if (this.databaseConfig.type === VuexBuilder.DatabaseType.indexedDb)
			{
				this.db = new VuexBuilderDatabaseIndexedDB(this.databaseConfig);
			}
			else if (this.databaseConfig.type === VuexBuilder.DatabaseType.localStorage)
			{
				this.db = new VuexBuilderDatabaseLocalStorage(this.databaseConfig);
			}
			else if (this.databaseConfig.type === VuexBuilder.DatabaseType.jnSharedStorage)
			{
				this.db = new VuexBuilderDatabaseJnSharedStorage(this.databaseConfig);
			}
			else
			{
				this.db = null;
			}
		}

		return this;
	}

	/**
	 * Enable namespace option for model.
	 *
	 * @param active {boolean}
	 * @returns {VuexBuilder}
	 */
	useNamespace(active)
	{
		this.withNamespace = !!active;

		return this;
	}

	/**
	 * Get store config for Vuex.
	 *
	 * @returns {Promise}
	 */
	getStore()
	{
		return new Promise((resolve, reject) => {

			let namespace = '';
			if (this.withNamespace)
			{
				namespace = this.namespace? this.namespace: this.getName();
				if (!namespace && this.withNamespace)
				{
					this.logger('error', 'VuexModel.getStore: current model can not be run in Vuex modules mode', this.getState());
					reject();
				}
			}

			if (this.db)
			{
				this._getStoreFromDatabase().then(state => resolve(this._createStore(state, namespace)));
			}
			else
			{
				resolve(this._createStore(this.getState(), namespace));
			}
		});
	}

	/**
	 * Get timeout for save to database
	 *
 	 * @override
	 *
	 * @returns {number}
	 */
	getSaveTimeout()
	{
		return 150;
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
	getLoadedState(state = {})
	{
		return state;
	}

	/**
	 * Save current state after change state to database
	 *
 	 * @param state {Object|function}
	 *
	 * @returns {Promise}
	 */
	saveState(state = {})
	{
		if (!this.isSaveAvailable())
		{
			return true;
		}

		this.lastSaveState = state;

		if (this.saveStateTimeout)
		{
			this.logger('log', 'VuexModel.saveState: wait save...', this.getName());
			return true;
		}

		this.logger('log', 'VuexModel.saveState: start saving', this.getName());

		let timeout = this.getSaveTimeout();
		if (typeof this.databaseConfig.timeout === 'number')
		{
			timeout = this.databaseConfig.timeout;
		}

		this.saveStateTimeout = setTimeout(() =>
		{
			this.logger('log', 'VuexModel.saveState: saved!', this.getName());
			let lastState = this.lastSaveState;
			if (typeof lastState === 'function')
			{
				lastState = lastState();
				if (typeof lastState !== 'object' || !lastState)
				{
					return false;
				}
			}

			this.db.set(
				this.cloneState(lastState, this.getStateSaveException())
			);

			this.lastState = null;
			this.saveStateTimeout = null;
		}, timeout);

		return true
	}

	/**
	 * Reset current store to default state
	 **
	 * @returns {Promise}
	 */
	clearState()
	{
		if (this.store)
		{
			let command = 'vuexBuilderModelClearState';
			command = this.withNamespace? this.getNamespace()+'/'+command: command;

			this.store.commit(command);

			return true;
		}

		return this.saveState(
			this.getState()
		);
	}

	/**
	 * Clear database only, store state does not change
	 **
	 * @returns {Promise}
	 */
	clearDatabase()
	{
		if (!this.isSaveAvailable())
		{
			return true;
		}

		this.db.clear();

		return true;
	}

	isSaveAvailable()
	{
		return this.db && this.databaseConfig.active;
	}

	isSaveNeeded(payload)
	{
		if (!this.isSaveAvailable())
		{
			return false;
		}

		let checkFunction = function(payload, filter = null)
		{
			if (!filter)
			{
				return true;
			}

			for (let field in payload)
			{
				if (!payload.hasOwnProperty(field))
				{
					continue;
				}

				if (typeof filter[field] === 'undefined')
				{
					return true;
				}
				else if (typeof filter[field] === 'object' && filter[field])
				{
					let result = checkFunction(payload[field], filter[field]);
					if (result)
					{
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
	constructor()
	{
		this.databaseConfig = {
		 	type: VuexBuilder.DatabaseType.indexedDb,
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

		this.withNamespace = false;
	}

	setStore(store)
	{
		if (!(store instanceof VuexVendor.Store))
		{
			this.logger('error', 'VuexBuilderModel.setStore: passed store is not a Vuex.Store', store);
			return this;
		}

		this.store = store;
		return this;
	}

	_getStoreFromDatabase()
	{
		clearTimeout(this.cacheTimeout);
		return new Promise((resolve) =>
		{
			this.cacheTimeout = setTimeout(() => {
				this.logger('warn', 'VuexModel.getStoreFromDatabase: Cache loading timeout', this.getName());
				resolve(this.getState());
			}, 1000);

			this.db.get().then(cache =>
			{
				clearTimeout(this.cacheTimeout);
				cache = this.getLoadedState(cache? cache: {});

				let state = this.getState();
				if (cache)
				{
					state = this._mergeState(state, cache);
				}

				resolve(state);
			}, (error) =>
			{
				clearTimeout(this.cacheTimeout);
				resolve(this.getState());
			})
		});
	}

	_mergeState(currentState, newState)
	{
		for (let key in currentState)
		{
			if (!currentState.hasOwnProperty(key))
			{
				continue;
			}

			if (typeof newState[key] === 'undefined')
			{
				newState[key] = currentState[key];
			}
			else if (
				!(newState[key] instanceof Array) &&
				typeof newState[key] === 'object' && newState[key] &&
				typeof currentState[key] === 'object' && currentState[key]
			)
			{
				newState[key] = Object.assign({}, currentState[key], newState[key]);
			}
		}

		return newState;
	}

	_createStore(state, namespace = '')
	{
		let result = {
			state,
			getters: this.getGetters(),
			actions: this.getActions(),
			mutations: this.getMutations()
		};

		result.mutations.vuexBuilderModelClearState = (state) => {
			state = Object.assign(state, this.getState());
			this.saveState(state);
		};

		if (namespace)
		{
			result.namespaced = true;
			result = {[namespace]: result};
		}

		return result;
	}

	/**
	 * Utils. Convert Object to Array
	 * @param object
	 * @returns {Array}
	 */
	static convertToArray(object)
	{
		let result = [];
		for (let i in object)
		{
			if (object.hasOwnProperty(i))
			{
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
	cloneState(element, exceptions = undefined)
	{
		let result;

		if (element instanceof Array)
		{
			result = [].concat(
				element.map(element => this.cloneState(element))
			);
		}
		else if (element instanceof Date)
		{
			result = new Date(element.toISOString());
		}
		else if (typeof element === 'object' && element)
		{
			result = {};
			for (let param in element)
			{
				if (!element.hasOwnProperty(param))
				{
					continue;
				}
				if (
					typeof exceptions === 'undefined'
					|| typeof exceptions[param] === 'undefined'
				)
				{
					result[param] = this.cloneState(element[param])
				}
				else if (typeof exceptions[param] === 'object' && exceptions[param])
				{
					result[param] = this.cloneState(element[param], exceptions[param])
				}
			}
		}
		else
		{
			result = element;
		}

		return result;
	}

	logger(type, ...args)
	{
		if (type === 'error')
		{
			console.error(...args);
			return undefined;
		}
		else if (typeof BX.VueDevTools === 'undefined')
		{
			return undefined;
		}

		if (type === 'log')
		{
			console.log(...args);
		}
		else if (type === 'info')
		{
			console.info(...args);
		}
		else if (type === 'warn')
		{
			console.warn(...args);
		}
	}
}