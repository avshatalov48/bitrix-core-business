/**
 * Bitrix Vuex wrapper
 * Interface Vuex model (Vuex builder model)
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2022 Bitrix
 */

import {BuilderDatabaseIndexedDB} from "./database/indexeddb.js";
import {BuilderDatabaseLocalStorage} from "./database/localstorage.js";
import {BuilderDatabaseJnSharedStorage} from "./database/jnsharedstorage.js";
import {BuilderDatabaseType} from "./builder.js";
import {Store} from "./../vuex";
import {BitrixVue} from 'ui.vue3';
import {Type} from 'main.core';

export class BuilderModel
{
	static create(): BuilderModel
	{
		return new this();
	}

	getName(): string
	{
		return '';
	}

	getState(): Object
	{
		return {};
	}

	/**
	 * Get default element state for models with collection.
	 *
 	 * @override
	 * @returns {Object}
	 */
	getElementState(params = {}): Object
	{
		return {};
	}

	/**
	 * Get object containing fields to exclude during the save to database.
	 *
 	 * @override
	 * @returns {Object|undefined}
	 */
	getStateSaveException(): undefined
	{
		return undefined;
	}

	getGetters(): Object
	{
		return {};
	}

	getActions(): Object
	{
		return {};
	}

	getMutations(): Object
	{
		return {};
	}

	getNestedModules(): {[moduleName: string]: BuilderModel}
	{
		return {};
	}

	validate(fields, options = {}): Object
	{
		return {};
	}

	setVariables(variables = {}): BuilderModel
	{
		if (!Type.isObjectLike(variables))
		{
			this.logger('error', 'BuilderModel.setVariables: passed variables is not a Object', 'Model: '+this.getName(), {variables}, store);
			return this;
		}

		this.variables = variables;

		return this;
	}

	getVariable(name, defaultValue = undefined): any
	{
		if (!name)
		{
			return defaultValue;
		}

		const nameParts = name.toString().split('.');

		let result;
		let variables = Object.assign({}, this.variables);

		for (let i = 0; i < nameParts.length; i++)
		{
			if (!Type.isUndefined(variables[nameParts[i]]))
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

	getNamespace(): string
	{
		return this.namespace? this.namespace: this.getName();
	}

	setNamespace(name): BuilderModel
	{
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
	useDatabase(active, config = {}): BuilderModel
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
		if (Type.isInteger(config.timeout))
		{
			this.databaseConfig.timeout = config.timeout;
		}

		if (!this.databaseConfig.active && this.db !== null)
		{
			this.databaseConfig.type = null;
			updateDriver = true;
		}

		if (updateDriver)
		{
			if (this.databaseConfig.type === BuilderDatabaseType.indexedDb)
			{
				this.db = new BuilderDatabaseIndexedDB(this.databaseConfig);
			}
			else if (this.databaseConfig.type === BuilderDatabaseType.localStorage)
			{
				this.db = new BuilderDatabaseLocalStorage(this.databaseConfig);
			}
			else if (this.databaseConfig.type === BuilderDatabaseType.jnSharedStorage)
			{
				this.db = new BuilderDatabaseJnSharedStorage(this.databaseConfig);
			}
			else
			{
				this.db = null;
			}
		}

		return this;
	}

	/**
	 * @returns {BuilderModel}
	 * @deprecated
	 */
	useNamespace(active): BuilderModel
	{
		if (BitrixVue.developerMode)
		{
			if (active)
			{
				console.warn('BuilderModel: Method `useNamespace` is deprecated, please remove this call.');
			}
			else
			{
				console.error('BuilderModel: Method `useNamespace` is deprecated, using Vuex.Builder without namespaces is no longer supported.');
			}
		}

		return this;
	}

	/**
	 * @returns {Promise}
	 * @deprecated use getModule instead.
	 */
	getStore(): Promise<Object>
	{
		console.warn('BuilderModel: Method `getStore` is deprecated, please remove this call.');
		return this.getModule();
	}

	getModule(): Promise<Object>
	{
		return new Promise((resolve, reject) => {
			const namespace = this.namespace? this.namespace: this.getName();
			if (!namespace)
			{
				this.logger('error', 'VuexModel.getStore: current model can not be run in Vuex modules mode', this.getState());
				reject();
			}

			if (this.db)
			{
				this.#getStoreFromDatabase().then(state => resolve({
					namespace,
					module: this.#createStore(state)
				}));
			}
			else
			{
				resolve({
					namespace,
					module: this.#createStore(this.getState())
				});
			}
		});
	}

	getNestedModule(nestedModule: BuilderModel): Object
	{
		const map = {'constructor': nestedModule};
		const instance = new map['constructor']();

		return {
			namespaced: true,
			state: instance.getState(),
			getters: instance.getGetters(),
			actions: instance.getActions(),
			mutations: instance.getMutations()
		};
	}

	getModuleWithDefaultState(): Object
	{
		const namespace = this.namespace? this.namespace: this.getName();
		if (!namespace)
		{
			this.logger('error', 'VuexModel.getStore: current model can not be run in Vuex modules mode', this.getState());
			return null;
		}

		return {
			namespace,
			module: this.#createStore(this.getState())
		};
	}

	getSaveTimeout(): number
	{
		return 150;
	}

	getLoadTimeout(): number | boolean
	{
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
	getLoadedState(state = {}): Object
	{
		return state;
	}

	/**
	 * Save current state after change state to database
	 *
 	 * @param state {Object|function}
	 *
	 * @returns {boolean}
	 */
	saveState(state = {}): boolean
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
		if (Type.isInteger(this.databaseConfig.timeout))
		{
			timeout = this.databaseConfig.timeout;
		}

		this.saveStateTimeout = setTimeout(() =>
		{
			this.logger('log', 'VuexModel.saveState: saved!', this.getName());
			let lastState = this.lastSaveState;
			if (Type.isFunction(lastState))
			{
				lastState = lastState();
				if (!Type.isObjectLike(lastState) || !lastState)
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

		return true;
	}

	/**
	 * Reset current store to default state
	 **
	 * @returns {Promise|boolean}
	 */
	clearState(): Promise<Object> | boolean
	{
		if (this.store)
		{
			this.store.commit(this.getNamespace()+'/'+'vuexBuilderModelClearState');

			return true;
		}

		return this.saveState(
			this.getState()
		);
	}

	/**
	 * Clear database only, store state does not change
	 **
	 * @returns {boolean}
	 */
	clearDatabase(): boolean
	{
		if (!this.isSaveAvailable())
		{
			return true;
		}

		this.db.clear();

		return true;
	}

	/**
	 * @return boolean
	 */
	isSaveAvailable(): boolean
	{
		return this.db && this.databaseConfig.active;
	}

	isSaveNeeded(payload): boolean
	{
		if (!this.isSaveAvailable())
		{
			return false;
		}

		const checkFunction = function(payload, filter = null): boolean
		{
			if (!filter)
			{
				return true;
			}

			for (const field in payload)
			{
				if (!payload.hasOwnProperty(field))
				{
					continue;
				}

				if (Type.isUndefined(filter[field]))
				{
					return true;
				}
				else if (Type.isObjectLike(filter[field]))
				{
					const result = checkFunction(payload[field], filter[field]);
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
	constructor(): void
	{
		this.databaseConfig = {
			type: BuilderDatabaseType.indexedDb,
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

	setStore(store): BuilderModel
	{
		if (!(store instanceof Store))
		{
			this.logger('error', 'VuexModel.setStore: passed store is not a Vuex.Store', store);
			return this;
		}

		this.store = store;
		return this;
	}

	#getStoreFromDatabase(): Promise<Object>
	{
		clearTimeout(this.cacheTimeout);
		return new Promise((resolve) =>
		{
			const loadTimeout = this.getLoadTimeout();

			if (
				loadTimeout !== false
				&& Type.isInteger(loadTimeout)
			)
			{
				this.cacheTimeout = setTimeout(() => {
					this.logger('warn', 'VuexModel.getStoreFromDatabase: Cache loading timeout', this.getName());
					resolve(this.getState());
				}, loadTimeout);
			}
			else
			{
				this.cacheTimeout = null;
			}

			this.db.get().then(cache =>
			{
				clearTimeout(this.cacheTimeout);
				cache = this.getLoadedState(cache? cache: {});

				let state = this.getState();
				if (cache)
				{
					state = BuilderModel.#mergeState(state, cache);
				}

				resolve(state);
			},
			() =>
			{
				clearTimeout(this.cacheTimeout);
				resolve(this.getState());
			})
		});
	}

	static #mergeState(currentState, newState): Object
	{
		for (const key in currentState)
		{
			if (!currentState.hasOwnProperty(key))
			{
				continue;
			}

			if (Type.isUndefined(newState[key]))
			{
				newState[key] = currentState[key];
			}
			else if (
				!(newState[key] instanceof Array)
				&& Type.isObjectLike(newState[key])
				&& Type.isObjectLike(currentState[key])
			)
			{
				newState[key] = Object.assign({}, currentState[key], newState[key]);
			}
		}

		return newState;
	}

	#createStore(state): Object
	{
		const result = {
			namespaced: true,
			state,
			getters: this.getGetters(),
			actions: this.getActions(),
			mutations: this.getMutations()
		};

		const nestedModules = this.prepareNestedModules();
		if (nestedModules)
		{
			result.modules = nestedModules;
		}

		result.mutations.vuexBuilderModelClearState = (state) => {
			state = Object.assign(state, this.getState());
			this.saveState(state);
		};

		return result;
	}

	prepareNestedModules(): ?Object
	{
		const nestedModules = Object.entries(this.getNestedModules());
		if (nestedModules.length === 0)
		{
			return null;
		}

		const preparedNestedModules = {};
		nestedModules.forEach(([moduleName, module]) => {
			preparedNestedModules[moduleName] = this.getNestedModule(module);
		});

		return preparedNestedModules;
	}

	static convertToArray(object): Array
	{
		const result = [];
		for (const i in object)
		{
			if (object.hasOwnProperty(i))
			{
				result.push(object[i]);
			}
		}
		return result;
	}

	cloneState(element, exceptions = undefined): Object
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
		else if (Type.isObjectLike(element))
		{
			result = {};
			for (const param in element)
			{
				if (!element.hasOwnProperty(param))
				{
					continue;
				}
				if (
					Type.isUndefined(exceptions)
					|| Type.isUndefined(exceptions[param])
				)
				{
					result[param] = this.cloneState(element[param])
				}
				else if (Type.isObjectLike(exceptions[param]))
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

	logger(type, ...args): void
	{
		if (type === 'error')
		{
			console.error(...args);
			return;
		}
		else if (!BitrixVue.developerMode)
		{
			return;
		}

		if (type === 'log')
		{
			// eslint-disable-next-line no-console
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