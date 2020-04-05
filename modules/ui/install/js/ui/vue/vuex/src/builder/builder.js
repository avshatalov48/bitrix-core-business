/**
 * Bitrix Vuex wrapper
 * Vuex builder
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import {Vuex} from "./../vuex.bitrix.js";
import {VuexBuilderModel} from "./model.js";

const DatabaseType = Object.freeze({
	indexedDb: 'indexedDb',
	localStorage: 'localStorage',
});

export class VuexBuilder
{
	/**
	 * Create new instance of builder.
	 *
	 * @returns {VuexBuilder}
	 */
	static create()
	{
		return new this;
	}

	constructor()
	{
		this.models = [];

		this.databaseConfig = {
			name: null,
			type: null,
			siteId: null,
			userId: null,
			timeout: null,
		};

		this.withNamespace = true;
	}

	/**
	 * Add vuex module.
	 *
	 * @param model {VuexBuilderModel}
	 *
	 * @returns {VuexBuilder}
	 */
	addModel(model)
	{
		if (!(model instanceof VuexBuilderModel))
		{
			console.error('BX.VuexBuilder.addModel: passed model is not a BX.VuexBuilderModel', model, name);
			return this;
		}

		this.models.push(model);

		return this;
	}

	/**
	 * Disable namespace for builder with single model.
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

	/**
	 * Build Vuex Store
	 *
	 * @param callback {Function|null}
	 * @returns {Promise<any>}
	 */
	build(callback = null)
	{
		let withNamespace = this.models.length > 1;
		if (!this.withNamespace && withNamespace)
		{
			return new Promise((resolve, reject) => {
				console.error('BX.VuexBuilder.create: you can not use the "no namespace" mode with multiple databases.');

				if (typeof callback !== 'function')
				{
					reject('MULTIPLE_MODULES_WITHOUT_NAMESPACE');
				}
			});
		}

		let results = [];

		this.models.forEach(model =>
		{
			if (this.databaseConfig.active && model.databaseConfig.active !== false)
			{
				model.useDatabase(true, this.databaseConfig)
			}
			if (this.withNamespace)
			{
				model.useNamespace(true);
			}

			results.push(model.getStore());
		});

		return new Promise((resolve, reject) =>
		{
			Promise.all(results).then(stores =>
			{
				let modules = {};

				stores.forEach(store => {
					Object.assign(modules, store);
				});

				let store = Vuex.store(this.withNamespace? {modules}: modules);
				this.models.forEach(model => model.setStore(store));

				resolve({store, models: this.models, builder: this});
				if (typeof callback === 'function')
				{
					callback({store, models: this.models, builder: this});
				}
			}, error => {
				console.error('BX.VuexBuilder.create: storage was not created due to runtime errors.', error? error: '');
				if (typeof callback !== 'function')
				{
					reject('ERROR_IN_MODEL');
				}
			});
		});
	}
}

VuexBuilder.DatabaseType = DatabaseType;