import 'im.old-chat-embedding.application.launch';

import {PullClient, PULL as Pull} from 'pull.client';
import {RestClient, rest as Rest} from 'rest.client';

import {Type} from 'main.core';
import {BitrixVue} from 'ui.vue3';
import {Builder, BuilderDatabaseType, Store} from 'ui.vue3.vuex';

import {ApplicationModel, DialoguesModel, UsersModel, RecentModel} from 'im.old-chat-embedding.model';
import {DeviceType} from 'im.old-chat-embedding.const';
import {BasePullHandler, RecentPullHandler} from 'im.old-chat-embedding.provider.pull';
import {Logger} from 'im.old-chat-embedding.lib.logger';
import {Utils} from 'im.old-chat-embedding.lib.utils';
import {SmileManager} from 'im.old-chat-embedding.lib.smile-manager';

class CoreApplication
{
	applicationData: {[applicationName: string]: {string: any}} = {};

	/* region 01. Initialize and store data */
	constructor(params = {})
	{
		this.inited = false;
		this.initPromise = new Promise((resolve) => {
			this.initPromiseResolver = resolve;
		});

		this.offline = false;

		this.vuexAdditionalModel = [];

		this.store = null;
		this.storeBuilder = null;
		this.pullHandlers = [];

		this.prepareParams(params);

		this.initStorage()
			.then(() => this.initPullClient())
			.then(() => this.initComplete())
			.catch(error => {
				Logger.error('Error initializing core controller', error);
			})
		;
	}

	prepareParams(params)
	{
		if (!Type.isUndefined(params.localize))
		{
			this.localize = params.localize;
		}
		else
		{
			this.localize = BX ? {...BX.message} : {};
		}

		this.host = params.host ?? location.origin;

		this.userId = this.prepareUserId(params.userId);

		this.siteId = this.getLocalize('SITE_ID') || 's1';
		if (Type.isStringFilled(params.siteId))
		{
			this.siteId = params.siteId;
		}

		this.siteDir = this.getLocalize('SITE_DIR') || 's1';
		if (Type.isStringFilled(params.siteDir))
		{
			this.siteDir = params.siteDir;
		}

		this.languageId = this.getLocalize('LANGUAGE_ID') || 'en';
		if (Type.isStringFilled(params.languageId))
		{
			this.languageId = params.languageId;
		}

		this.initPull(params);
		this.initRest(params);
		this.initVuexBuilder(params);
	}

	initStorage()
	{
		const applicationVariables = {
			common: {
				host: this.getHost(),
				userId: this.getUserId(),
				siteId: this.getSiteId(),
				languageId: this.getLanguageId(),
			},
			dialog: {
				messageLimit: 50,
				enableReadMessages: true,
			},
			device: {
				type: Utils.device.isMobile()? DeviceType.mobile: DeviceType.desktop,
				orientation: Utils.device.getOrientation(),
			},
		};

		const builder = Builder.init()
			.addModel(ApplicationModel.create().useDatabase(false).setVariables(applicationVariables))
			.addModel(DialoguesModel.create().useDatabase(false))
			.addModel(UsersModel.create().useDatabase(false))
			.addModel(RecentModel.create().useDatabase(false))
		;

		this.vuexAdditionalModel.forEach(model => {
			builder.addModel(model);
		});

		builder.setDatabaseConfig({
			name: this.vuexBuilder.databaseName,
			type: this.vuexBuilder.databaseType,
			siteId: this.getSiteId(),
			userId: this.getUserId(),
		});

		return builder.build().then(result => {
			this.store = result.store;
			this.storeBuilder = result.builder;

			return Promise.resolve();
		});
	}

	initPullClient()
	{
		if (!this.pullClient)
		{
			return false;
		}

		this.pullClient.subscribe(new BasePullHandler());
		this.pullClient.subscribe(new RecentPullHandler());

		this.pullClient.subscribe({
			type: this.pullInstance.SubscriptionType.Status,
			callback: this.eventStatusInteraction.bind(this)
		});

		this.pullClient.subscribe({
			type: this.pullInstance.SubscriptionType.Online,
			callback: this.eventOnlineInteraction.bind(this)
		});

		return Promise.resolve();
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
	}

	initRest(params)
	{
		this.restInstance = RestClient;
		this.restClient = Rest;

		if (!Type.isUndefined(params.rest))
		{
			if (!Type.isUndefined(params.rest.instance))
			{
				this.restInstance = params.rest.instance;
			}
			if (!Type.isUndefined(params.rest.client))
			{
				this.restClient = params.rest.client;
			}
		}

		return Promise.resolve();
	}

	initPull(params)
	{
		this.pullInstance = PullClient;
		this.pullClient = Pull;

		if (params.pull)
		{
			if (params.pull.instance)
			{
				this.pullInstance = params.pull.instance;
			}
			if (params.pull.client)
			{
				this.pullClient = params.pull.client;
			}
		}
	}

	initVuexBuilder(params)
	{
		this.vuexBuilder = {
			database: false,
			databaseName: 'desktop/im',
			databaseType: BuilderDatabaseType.indexedDb
		};

		if (params.vuexBuilder)
		{
			if (Type.isBoolean(params.vuexBuilder.database))
			{
				this.vuexBuilder.database = params.vuexBuilder.database;
			}
			if (Type.isStringFilled(params.vuexBuilder.databaseName))
			{
				this.vuexBuilder.databaseName = params.vuexBuilder.databaseName;
			}
			if (Type.isStringFilled(params.vuexBuilder.databaseType))
			{
				this.vuexBuilder.databaseType = params.vuexBuilder.databaseType;
			}
			if (Type.isArray(params.vuexBuilder.models))
			{
				params.vuexBuilder.models.forEach(model => {
					this.addVuexModel(model);
				});
			}
		}
	}

	prepareUserId(userId)
	{
		let result = 0;
		if (!Type.isUndefined(userId))
		{
			const parsedUserId = Number.parseInt(params.userId, 10);
			if (parsedUserId)
			{
				result = parsedUserId;
			}
		}
		else if (this.getLocalize('USER_ID'))
		{
			result = Number.parseInt(this.getLocalize('USER_ID'), 10);
		}

		return result;
	}

	/* endregion 01. Initialize and store data */

	/* region 02. Push & Pull */

	eventStatusInteraction(data)
	{
		if (data.status === this.pullInstance.PullStatus.Online)
		{
			this.offline = false;
		}
		else if (data.status === this.pullInstance.PullStatus.Offline)
		{
			this.offline = true;
		}
	}

	eventOnlineInteraction(data)
	{
		if (!['list', 'userStatus'].includes(data.command))
		{
			return false;
		}

		Object.values(data.params.users).forEach(userInfo => {
			this.store.dispatch('users/update', {
				id: userInfo.id,
				fields: userInfo
			});
		});
	}

	/* endregion 02. Push & Pull */

	/* region 04. Template engine */

	createVue(application, config = {})
	{
		let beforeCreateFunction = () => {};
		if (config.beforeCreate)
		{
			beforeCreateFunction = config.beforeCreate;
		}

		let unmountedFunction = () => {};
		if (config.unmounted)
		{
			unmountedFunction = config.unmounted;
		}

		let createdFunction = () => {};
		if (config.created)
		{
			createdFunction = config.created;
		}

		const controller = this;
		const initConfig = {
			// store: this.store,
			beforeCreate()
			{
				this.$bitrix.Data.set('controller', controller);

				this.$bitrix.Application.set(application);
				this.$bitrix.Loc.setMessage(controller.localize);

				if (controller.restClient)
				{
					this.$bitrix.RestClient.set(controller.restClient);
				}
				if (controller.pullClient)
				{
					this.$bitrix.PullClient.set(controller.pullClient);
				}

				beforeCreateFunction.bind(this)();
			},
			created()
			{
				createdFunction.bind(this)();
			},
			unmounted()
			{
				unmountedFunction.bind(this)();
			}
		};

		if (config.el)
		{
			initConfig.el = config.el;
		}

		if (config.template)
		{
			initConfig.template = config.template;
		}

		if (config.computed)
		{
			initConfig.computed = config.computed;
		}

		if (config.data)
		{
			initConfig.data = config.data;
		}

		if (config.name)
		{
			initConfig.name = config.name;
		}

		if (config.components)
		{
			initConfig.components = config.components;
		}

		const initConfigCreatedFunction = initConfig.created;
		return new Promise((resolve) => {
			initConfig.created = function() {
				initConfigCreatedFunction.bind(this)();
				resolve(this);
			};
			const bitrixVue = BitrixVue.createApp(initConfig);
			bitrixVue.config.errorHandler = function (err, vm, info) {
				console.error(err, info);
			};
			bitrixVue.config.warnHandler = function (warn, vm, trace) {
				console.warn(warn, trace);
			};
			application.bitrixVue = bitrixVue;
			bitrixVue.use(this.store).mount(initConfig.el);
		});
	}

	/* endregion 04. Template engine */

	/* region 05. Core methods */
	getHost(): string
	{
		return this.host;
	}

	getUserId(): number
	{
		return this.userId;
	}

	getSiteId(): string
	{
		return this.siteId;
	}

	getLanguageId(): string
	{
		return this.languageId;
	}

	getStore(): Store
	{
		return this.store;
	}

	getRestClient(): RestClient
	{
		return this.restClient;
	}

	setApplicationData(applicationName: string, applicationData: {string: any})
	{
		this.applicationData[applicationName] = applicationData;
	}

	getApplicationData(applicationName: string)
	{
		return this.applicationData[applicationName] ?? {};
	}

	addVuexModel(model)
	{
		this.vuexAdditionalModel.push(model);
	}

	isOnline()
	{
		return !this.offline;
	}

	ready()
	{
		if (this.inited)
		{
			return Promise.resolve(this);
		}

		return this.initPromise;
	}

	/* endregion 05. Methods */

	/* region 06. Interaction and utils */
	addLocalize(phrases)
	{
		if (!Type.isPlainObject(phrases))
		{
			return false;
		}

		Object.entries(phrases).forEach(([key, value]) => {
			this.localize[key] = value;
		});

		return true;
	}

	getLocalize(name)
	{
		let phrase = '';
		if (typeof name === 'undefined')
		{
			return this.localize;
		}
		else if (typeof this.localize[name.toString()] === 'undefined')
		{
			Logger.warn(`Controller.Core.getLocalize: message with code '${name.toString()}' is undefined.`);
		}
		else
		{
			phrase = this.localize[name];
		}

		return phrase;
	}

	/* endregion 06. Interaction and utils */
}

const Core = new CoreApplication();
export {Core, CoreApplication};