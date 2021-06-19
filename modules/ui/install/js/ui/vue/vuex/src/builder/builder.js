/**
 * Bitrix Vuex wrapper
 * Vuex builder
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import {BitrixVue} from "ui.vue";
import {Vuex} from "../vuex.js";
import {VuexBuilderModel} from "./model.js";

const DatabaseType = Object.freeze({
	indexedDb: 'indexedDb',
	localStorage: 'localStorage',
	jnSharedStorage: 'jnSharedStorage',
});

export class VuexBuilder
{
	/**
	 * @deprecated use init() method.
	 * @returns {VuexBuilder}
	 */
	static create()
	{
		if (BitrixVue.developerMode)
		{
			console.warn('VuexBuilder: Method VuexBuilder.create is deprecated, use VuexBuilder.init instead.');
		}

		return new this;
	}

	/**
	 * Create new instance of builder and initialize Vuex store
	 *
	 * @param store {Vuex}
	 *
	 * @returns {VuexBuilder}
	 */
	static init(store)
	{
		if (store)
		{
			if (!(store instanceof Vuex.Store))
			{
				console.warn('VuexBuilder.init: passed store is not a Vuex.Store', store);
				return new this;
			}
		}

		return new this(store);
	}

	constructor(store)
	{
		this.models = [];

		this.databaseConfig = {
			name: null,
			type: null,
			siteId: null,
			userId: null,
			timeout: null,
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
	addModel(model)
	{
		if (this.builded)
		{
			return this;
		}

		if (!(model instanceof VuexBuilderModel))
		{
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
	addDynamicModel(model)
	{
		if (!(model instanceof VuexBuilderModel))
		{
			return new Promise((resolve, reject) =>
			{
				console.error('BX.VuexBuilder.addDynamicModel: passed model is not a BX.VuexBuilderModel', model);
				reject('MODEL_ERROR');
			});
		}

		if (
			this.store.hasModule(model.getNamespace())
			|| this.models.find(stored => stored.getNamespace() === model.getNamespace())
		)
		{
			return new Promise((resolve, reject) =>
			{
				console.error('BX.VuexBuilder.addDynamicModel: model `'+model.getNamespace()+'` was not added because it is already registered.');
				reject('DUPLICATE_MODEL');
			});
		}

		this.models.push(model);

		if (this.databaseConfig.active && model.databaseConfig.active !== false)
		{
			model.useDatabase(true, this.databaseConfig)
		}
		else
		{
			model.useDatabase(false);
		}

		model.setStore(this.store)

		const promise = model.getModule();

		return new Promise((resolve, reject) =>
		{
			promise.then(result => {
				this.store.registerModule(result.namespace, result.module);
				resolve();
			}, error => {
				console.error('BX.VuexBuilder.addDynamicModel: storage was not created due to runtime errors.', error? error: '');
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
	removeDynamicModel(namespace)
	{
		if (!this.builded)
		{
			console.error('BX.VuexBuilder.removeDynamicModel: you cannot use the method until builder is built.');
			return this;
		}

		if (!this.store.hasModule(namespace))
		{
			console.error('BX.VuexBuilder.removeDynamicModel: module `'+namespace+'` not registered.');
			return this;
		}

		this.models = this.models.filter(stored => stored.getNamespace() !== namespace);

		this.store.unregisterModule(namespace);

		return this;
	}

	/**
	 * @returns {VuexBuilder}
	 * @deprecated
	 */
	useNamespace(active)
	{
		if (BitrixVue.developerMode)
		{
			if (active)
			{
				console.warn('VuexBuilder: Method `useNamespace` is deprecated, please remove this call.');
			}
			else
			{
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
	setDatabaseConfig(config = {})
	{
		if (!(typeof config === 'object' && config))
		{
			return this;
		}

		this.databaseConfig.active = true;
		this.databaseConfig.storage = config.name;

		this.databaseConfig.type = config.type || this.databaseConfig.type;
		this.databaseConfig.siteId = config.siteId || this.databaseConfig.siteId;
		this.databaseConfig.userId = config.userId || this.databaseConfig.userId;
		this.databaseConfig.timeout = typeof config.timeout !== 'undefined'? config.timeout: this.databaseConfig.timeout;

		return this;
	}

	clearModelState(callback = null)
	{
		if (!this.builded)
		{
			return new Promise((resolve, reject) =>
			{
				console.error('BX.VuexBuilder.clearModelState: you cannot use the method until builder is built.');
				if (typeof callback !== 'function')
				{
					reject('BUILDER_NOT_BUILD');
				}
			});
		}

		var results = [];

		this.models.forEach(model => {
			results.push(model.clearState());
		});

		return new Promise((resolve, reject) =>
		{
			Promise.all(results).then(stores =>
			{
				resolve(true);
				if (typeof callback === 'function')
				{
					callback(true);
				}
			}, error =>
			{
				console.error('BX.VuexBuilder.clearModelState: storage was not clear due to runtime errors.', error? error: '');
				if (typeof callback !== 'function')
				{
					reject('ERROR_WHILE_CLEARING');
				}
			});
		});
	}

	clearDatabase()
	{
		if (!this.builded)
		{
			return new Promise((resolve, reject) =>
			{
				console.error('BX.VuexBuilder.clearModelState: you cannot use the method until builder is built.');
				reject('BUILDER_NOT_BUILD');
			});
		}

		this.models.forEach(model => model.clearDatabase());

		return new Promise((resolve, reject) => resolve(true));
	}

	/**
	 * Build Vuex Store asynchronously
	 *
	 * @param callback {Function|null}
	 * @returns {Promise<any>}
	 */
	build(callback = null)
	{
		if (this.builded)
		{
			return this;
		}

		let promises = [];

		if (!this.store)
		{
			this.store = Vuex.createStore();
		}

		this.models.forEach(model =>
		{
			if (this.databaseConfig.active && model.databaseConfig.active !== false)
			{
				model.useDatabase(true, this.databaseConfig)
			}

			model.setStore(this.store)

			promises.push(model.getModule());
		});

		return new Promise((resolve, reject) =>
		{
			Promise.all(promises).then(modules =>
			{
				modules.forEach(result => {
					this.store.registerModule(result.namespace, result.module);
				});

				const result = {
					store: this.store,
					models: this.models,
					builder: this
				};

				this.builded = true;

				if (typeof callback === 'function')
				{
					callback(result);
				}

				resolve(result);

			}, error => {
				console.error('BX.VuexBuilder.create: storage was not created due to runtime errors.', error? error: '');
				if (typeof callback !== 'function')
				{
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
	syncBuild()
	{
		if (this.builded)
		{
			return {
				store: this.store,
				models: this.models,
				builder: this
			};
		}

		if (!this.store)
		{
			this.store = Vuex.createStore();
		}

		if (this.databaseConfig.active)
		{
			if (BitrixVue.developerMode)
			{
				console.error('VuexBuilder: Method `syncBuild` creates storage in synchronous mode, the database does not work in this mode.');
			}

			this.databaseConfig.active = false;
		}

		this.models.forEach(model =>
		{
			model.useDatabase(false);
			model.setStore(this.store);

			const {namespace, module} = model.getModuleWithDefaultState();
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

VuexBuilder.DatabaseType = DatabaseType;