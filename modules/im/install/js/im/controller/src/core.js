/**
 * Bitrix im
 * Core controller class
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2020 Bitrix
 */

import {PullClient, PULL as Pull} from "pull.client";
import {RestClient, rest as Rest} from "rest.client";

// ui
import {BitrixVue} from "ui.vue";
import {VuexBuilder} from "ui.vue.vuex";

// messenger files
import {ApplicationModel, MessagesModel, DialoguesModel, UsersModel, FilesModel, RecentModel, NotificationsModel} from 'im.model';
import {DeviceType, DeviceOrientation} from 'im.const';
import {Utils} from "im.lib.utils";
import {ImBasePullHandler} from "im.provider.pull";
import {CoreRestHandler} from "im.provider.rest";
import {ApplicationController} from "./application";
import {Logger} from "im.lib.logger";

export class Controller
{
	/* region 01. Initialize and store data */

	constructor(params = {})
	{
		this.inited = false;
		this.initPromise = new Promise((resolve, reject) => {
			this.initPromiseResolver = resolve;
		});

		this.offline = false;

		this.restAnswerHandler = [];
		this.vuexAdditionalModel = [];

		this.store = null;
		this.storeBuilder = null;

		this.init()
			.then(() => this.prepareParams(params))
			.then(() => this.initController())
			.then(() => this.initLocalStorage())
			.then(() => this.initStorage())
			.then(() => this.initRestClient())
			.then(() => this.initPullClient())
			.then(() => this.initEnvironment())
			.then(() => this.initComplete())
			.catch(error => {
				Logger.error('error initializing core controller', error);
			})
		;
	}

	init()
	{
		return Promise.resolve();
	}

	prepareParams(params)
	{
		if (typeof params.localize !== 'undefined')
		{
			this.localize = params.localize;
		}
		else
		{
			if (typeof BX !== 'undefined')
			{
				this.localize = {...BX.message};
			}
			else
			{
				this.localize = {};
			}
		}

		if (typeof params.host !== 'undefined')
		{
			this.host = params.host;
		}
		else
		{
			this.host = location.origin;
		}

		if (typeof params.userId !== 'undefined')
		{
			const parsedUserId = parseInt(params.userId);
			if (!isNaN(parsedUserId))
			{
				this.userId = parsedUserId;
			}
			else
			{
				this.userId = 0;
			}
		}
		else
		{
			let userId = this.getLocalize('USER_ID');
			this.userId = userId? parseInt(userId): 0;
		}

		if (typeof params.siteId !== 'undefined')
		{
			if (typeof params.siteId === 'string' && params.siteId !== '')
			{
				this.siteId = params.siteId;
			}
			else
			{
				this.siteId = 's1';
			}
		}
		else
		{
			this.siteId = this.getLocalize('SITE_ID') || 's1';
		}

		if (typeof params.siteDir !== 'undefined')
		{
			if (typeof params.siteDir === 'string' && params.siteDir !== '')
			{
				this.siteDir = params.siteDir;
			}
			else
			{
				this.siteDir = 's1';
			}
		}
		else
		{
			this.siteDir = this.getLocalize('SITE_DIR') || 's1';
		}

		if (typeof params.languageId !== 'undefined')
		{
			if (typeof params.languageId === 'string' && params.languageId !== '')
			{
				this.languageId = params.languageId;
			}
			else
			{
				this.languageId = 'en';
			}
		}
		else
		{
			this.languageId = this.getLocalize('LANGUAGE_ID') || 'en';
		}

		this.pullInstance = PullClient;
		this.pullClient = Pull;

		if (typeof params.pull !== 'undefined')
		{
			if (typeof params.pull.instance !== 'undefined')
			{
				this.pullInstance = params.pull.instance;
			}
			if (typeof params.pull.client !== 'undefined')
			{
				this.pullClient = params.pull.client;
			}
		}


		this.restInstance = RestClient;
		this.restClient = Rest;

		if (typeof params.rest !== 'undefined')
		{
			if (typeof params.rest.instance !== 'undefined')
			{
				this.restInstance = params.rest.instance;
			}
			if (typeof params.rest.client !== 'undefined')
			{
				this.restClient = params.rest.client;
			}
		}


		this.vuexBuilder = {
			database: false,
			databaseName: 'desktop/im',
			databaseType: VuexBuilder.DatabaseType.indexedDb
		};

		if (typeof params.vuexBuilder !== 'undefined')
		{
			if (typeof params.vuexBuilder.database !== 'undefined')
			{
				this.vuexBuilder.database = params.vuexBuilder.database;
			}
			if (typeof params.vuexBuilder.databaseName !== 'undefined')
			{
				this.vuexBuilder.databaseName = params.vuexBuilder.databaseName;
			}
			if (typeof params.vuexBuilder.databaseType !== 'undefined')
			{
				this.vuexBuilder.databaseType = params.vuexBuilder.databaseType;
			}
			if (typeof params.vuexBuilder.models !== 'undefined')
			{
				params.vuexBuilder.models.forEach(model => {
					this.addVuexModel(model);
				});
			}
		}

		return Promise.resolve();
	}

	initController()
	{
		this.application = new ApplicationController();
		this.application.setCoreController(this);

		return new Promise((resolve, reject) => resolve());
	}

	initLocalStorage()
	{
		return new Promise((resolve, reject) => resolve());
	}

	initStorage()
	{
		let applicationVariables = {
			common: {
				host: this.getHost(),
				userId: this.getUserId(),
				siteId: this.getSiteId(),
				languageId: this.getLanguageId(),
			},
			dialog: {
				messageLimit: this.application.getDefaultMessageLimit(),
				enableReadMessages: true,
			},
			device: {
				type: Utils.device.isMobile()? DeviceType.mobile: DeviceType.desktop,
				orientation: Utils.device.getOrientation(),
			},
		};

		let builder = new VuexBuilder()
			.addModel(ApplicationModel.create().useDatabase(false).setVariables(applicationVariables))
			.addModel(MessagesModel.create().useDatabase(this.vuexBuilder.database).setVariables({host: this.getHost()}))
			.addModel(DialoguesModel.create().useDatabase(this.vuexBuilder.database).setVariables({host: this.getHost()}))
			.addModel(FilesModel.create().useDatabase(this.vuexBuilder.database).setVariables({host: this.getHost(), default: {name: 'File is deleted'}}))
			.addModel(UsersModel.create().useDatabase(this.vuexBuilder.database).setVariables({host: this.getHost(), default: {name: 'Anonymous'}}))
			.addModel(RecentModel.create().useDatabase(false).setVariables({host: this.getHost()}))
			.addModel(NotificationsModel.create().useDatabase(false).setVariables({host: this.getHost()}))
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
			return new Promise((resolve, reject) => resolve());
		})
	}

	initRestClient(result)
	{
		this.addRestAnswerHandler(
			CoreRestHandler.create({
				store: this.store,
				controller: this,
			})
		);

		return new Promise((resolve, reject) => resolve());
	}

	initPullClient()
	{
		if (!this.pullClient)
		{
			return false;
		}

		this.pullClient.subscribe(
			this.pullBaseHandler = new ImBasePullHandler({
				store: this.store,
				controller: this,
			})
		);

		this.pullClient.subscribe({
			type: this.pullInstance.SubscriptionType.Status,
			callback: this.eventStatusInteraction.bind(this)
		});

		this.pullClient.subscribe({
			type: this.pullInstance.SubscriptionType.Online,
			callback: this.eventOnlineInteraction.bind(this)
		});

		return new Promise((resolve, reject) => resolve());
	}

	initEnvironment(result)
	{
		window.addEventListener('orientationchange', () =>
		{
			if (!this.store)
			{
				return;
			}

			this.store.commit('application/set', {device: {
				orientation: Utils.device.getOrientation()
			}});

			if (
				this.store.state.application.device.type === DeviceType.mobile
				&& this.store.state.application.device.orientation === DeviceOrientation.horizontal
			)
			{
				document.activeElement.blur();
			}
		});

		return new Promise((resolve, reject) => resolve());
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
	}

/* endregion 01. Initialize and store data */

/* region 02. Push & Pull */

	eventStatusInteraction(data)
	{
		if (data.status === this.pullInstance.PullStatus.Online)
		{
			this.offline = false;

			//this.pullBaseHandler.option.skip = true;
			// this.getDialogUnread().then(() => {
			// 	this.pullBaseHandler.option.skip = false;
			// 	this.processSendMessages();
			// 	this.emit(EventType.dialog.sendReadMessages);
			// }).catch(() => {
			// 	this.pullBaseHandler.option.skip = false;
			// 	this.processSendMessages();
			// });
		}
		else if (data.status === this.pullInstance.PullStatus.Offline)
		{
			this.offline = true;
		}
	}

	eventOnlineInteraction(data)
	{
		if (data.command === 'list' || data.command === 'userStatus')
		{
			for (let userId in data.params.users)
			{
				if (!data.params.users.hasOwnProperty(userId))
				{
					continue;
				}

				this.store.dispatch('users/update', {
					id: data.params.users[userId].id,
					fields: data.params.users[userId]
				});
			}
		}
	}

/* endregion 02. Push & Pull */

/* region 03. Rest */

	executeRestAnswer(command, result, extra)
	{
		Logger.warn('Core.controller.executeRestAnswer', command, result, extra);

		this.restAnswerHandler.forEach(handler => {
			handler.execute(command, result, extra);
		});
	}

/* endregion 03. Rest */

/* region 04. Template engine */

	createVue(application, config = {})
	{
		const controller = this;

		let beforeCreateFunction = () => {};
		if (config.beforeCreate)
		{
			beforeCreateFunction = config.beforeCreate;
		}

		let destroyedFunction = () => {};
		if (config.destroyed)
		{
			destroyedFunction = config.destroyed;
		}

		let createdFunction = () => {};
		if (config.created)
		{
			createdFunction = config.created;
		}

		let initConfig = {
			store: this.store,
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
			destroyed()
			{
				destroyedFunction.bind(this)();
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

		const initConfigCreatedFunction = initConfig.created;
		return new Promise((resolve, reject) => {
			initConfig.created = function() {
				initConfigCreatedFunction.bind(this)();
				resolve(this);
			};
			BitrixVue.createApp(initConfig);
		});
	}

/* endregion 04. Template engine */

/* region 05. Core methods */
	getHost()
	{
		return this.host;
	}

	setHost(host)
	{
		this.host = host;

		this.store.commit('application/set', {
			common: {host},
		});
	}

	getUserId()
	{
		return this.userId;
	}

	setUserId(userId)
	{
		const parsedUserId = parseInt(userId);
		if (!isNaN(parsedUserId))
		{
			this.userId = parsedUserId;
		}
		else
		{
			this.userId = 0;
		}

		this.store.commit('application/set', {
			common: {userId},
		});
	}

	getSiteId()
	{
		return this.siteId;
	}

	setSiteId(siteId)
	{
		if (typeof siteId === 'string' && siteId !== '')
		{
			this.siteId = siteId;
		}
		else
		{
			this.siteId = 's1';
		}

		this.store.commit('application/set', {
			common: {siteId: this.siteId},
		});
	}

	getLanguageId()
	{
		return this.languageId;
	}

	setLanguageId(languageId)
	{
		if (typeof languageId === 'string' && languageId !== '')
		{
			this.languageId = languageId;
		}
		else
		{
			this.languageId = 'en';
		}

		this.store.commit('application/set', {
			common: {languageId: this.languageId},
		});
	}

	getStore()
	{
		return this.store;
	}

	getStoreBuilder()
	{
		return this.storeBuilder;
	}

	addRestAnswerHandler(handler)
	{
		this.restAnswerHandler.push(handler);
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

	setError(code = '', description = '')
	{
		Logger.error(`Messenger.Application.error: ${code} (${description})`);

		let localizeDescription = '';
		if (code.endsWith('LOCALIZED'))
		{
			localizeDescription = description;
		}

		this.store.commit('application/set', {error: {
			active: true,
			code,
			description: localizeDescription
		}});
	}

	clearError()
	{
		this.store.commit('application/set', {error: {
			active: false,
			code: '',
			description: ''}
		});
	}

	addLocalize(phrases)
	{
		if (typeof phrases !== "object" || !phrases)
		{
			return false;
		}

		for (let name in phrases)
		{
			if (phrases.hasOwnProperty(name))
			{
				this.localize[name] = phrases[name];
			}
		}

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
			//Logger.trace();
		}
		else
		{
			phrase = this.localize[name];
		}

		return phrase;
	}

/* endregion 06. Interaction and utils */
}
