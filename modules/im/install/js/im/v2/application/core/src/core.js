import {Extension, Loc} from 'main.core';
import {BitrixVue} from 'ui.vue3';
import {Builder, Store} from 'ui.vue3.vuex';

import {PullClient, PULL as Pull} from 'pull.client';
import {RestClient, rest as Rest} from 'rest.client';

import 'im.v2.application.launch';

import {
	ApplicationModel,
	MessagesModel,
	DialoguesModel,
	UsersModel,
	FilesModel,
	RecentModel,
	NotificationsModel,
	SidebarModel,
	MarketModel
} from 'im.v2.model';
import {
	BasePullHandler,
	RecentPullHandler,
	NotificationPullHandler,
	NotifierPullHandler
} from 'im.v2.provider.pull';
import {Logger} from 'im.v2.lib.logger';

class CoreApplication
{
	host: string;
	userId: number;
	siteId: string;
	siteDir: string;
	languageId: string;
	applicationData: {[applicationName: string]: {string: any}} = {};

	/* region 01. Initialize and store data */
	constructor()
	{
		this.inited = false;
		this.initPromise = new Promise((resolve) => {
			this.initPromiseResolver = resolve;
		});

		this.offline = false;

		this.vuexAdditionalModel = [];

		this.store = null;
		this.storeBuilder = null;

		this.prepareVariables();

		this.initStorage()
			.then(() => this.initPullClient())
			.then(() => this.initComplete())
			.catch(error => {
				Logger.error('Error initializing core controller', error);
			})
		;
	}

	prepareVariables()
	{
		this.localize = BX ? {...BX.message} : {};

		this.host = location.origin;
		this.userId = Number.parseInt(Loc.getMessage('USER_ID'), 10) ?? 0;
		this.siteId = Loc.getMessage('SITE_ID') ?? 's1';
		this.siteDir = Loc.getMessage('SITE_DIR') ?? 's1';
		this.languageId = Loc.getMessage('LANGUAGE_ID') ?? 'en';

		this.initPull();
		this.initRest();
	}

	initStorage()
	{
		const builder = Builder.init()
			.addModel(ApplicationModel.create())
			.addModel(MessagesModel.create())
			.addModel(DialoguesModel.create())
			.addModel(FilesModel.create())
			.addModel(UsersModel.create())
			.addModel(RecentModel.create())
			.addModel(NotificationsModel.create())
			.addModel(SidebarModel.create())
			.addModel(MarketModel.create())
		;

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
		this.pullClient.subscribe(new NotificationPullHandler());
		this.pullClient.subscribe(new NotifierPullHandler());

		this.pullClient.subscribe({
			type: this.pullInstance.SubscriptionType.Status,
			callback: this.onPullStatusChange.bind(this)
		});

		this.pullClient.subscribe({
			type: this.pullInstance.SubscriptionType.Online,
			callback: this.onUsersOnlineChange.bind(this)
		});

		return Promise.resolve();
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
	}

	initRest()
	{
		this.restInstance = RestClient;
		this.restClient = Rest;

		return Promise.resolve();
	}

	initPull()
	{
		this.pullInstance = PullClient;
		this.pullClient = Pull;
	}
	/* endregion 01. Initialize and store data */

	/* region 02. Push & Pull */
	onPullStatusChange(data)
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

	onUsersOnlineChange(data)
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
		const initConfig = {};

		if (config.el)
		{
			initConfig.el = config.el;
		}

		if (config.template)
		{
			initConfig.template = config.template;
		}

		if (config.name)
		{
			initConfig.name = config.name;
		}

		if (config.components)
		{
			initConfig.components = config.components;
		}

		return new Promise((resolve) => {
			initConfig.created = function() {
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

	isOnline()
	{
		return !this.offline;
	}

	isCloud(): boolean
	{
		const settings = Extension.getSettings('im.v2.application.core');

		return settings.get('isCloud');
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
}

const Core = new CoreApplication();
export {Core, CoreApplication};