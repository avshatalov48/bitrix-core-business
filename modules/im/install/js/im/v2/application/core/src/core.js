import { Extension, Loc } from 'main.core';
import { BitrixVue } from 'ui.vue3';
import { Builder, Store } from 'ui.vue3.vuex';

import 'pull.client';
import 'rest.client';

import 'im.v2.application.launch';

import {
	ApplicationModel,
	MessagesModel,
	ChatsModel,
	UsersModel,
	FilesModel,
	RecentModel,
	NotificationsModel,
	SidebarModel,
	MarketModel,
	CountersModel,
	CopilotModel,
} from 'im.v2.model';
import {
	BasePullHandler,
	RecentPullHandler,
	NotificationPullHandler,
	NotifierPullHandler,
	OnlinePullHandler,
	CounterPullHandler,
} from 'im.v2.provider.pull';
import { Logger } from 'im.v2.lib.logger';
import { OpenLinesLaunchResources } from 'imopenlines.v2.lib.launch-resources';

import type { RestClient } from 'rest.client';

class CoreApplication
{
	host: string;
	userId: number;
	siteId: string;
	siteDir: string;
	languageId: string;
	applicationData: {[key]: any} = {};

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
		this.initRestClient();
	}

	start()
	{
		this.initStorage()
			.then(() => this.initPull())
			.then(() => this.initComplete())
			.catch((error) => {
				Logger.error('Core: error starting core application', error);
			})
		;
	}

	prepareVariables()
	{
		this.localize = BX ? { ...BX.message } : {};

		this.host = location.origin;
		this.userId = Number.parseInt(Loc.getMessage('USER_ID'), 10) ?? 0;
		this.siteId = Loc.getMessage('SITE_ID') ?? 's1';
		this.siteDir = Loc.getMessage('SITE_DIR') ?? 's1';
		this.languageId = Loc.getMessage('LANGUAGE_ID') ?? 'en';
	}

	initRestClient()
	{
		this.restInstance = BX.RestClient;
		this.restClient = BX.rest;
	}

	initStorage(): Promise
	{
		const builder = Builder.init()
			.addModel(ApplicationModel.create())
			.addModel(MessagesModel.create())
			.addModel(ChatsModel.create())
			.addModel(FilesModel.create())
			.addModel(UsersModel.create())
			.addModel(RecentModel.create())
			.addModel(CountersModel.create())
			.addModel(NotificationsModel.create())
			.addModel(SidebarModel.create())
			.addModel(MarketModel.create())
			.addModel(CopilotModel.create())
		;

		OpenLinesLaunchResources.models.forEach((model) => {
			builder.addModel(model.create());
		});

		return builder.build().then((result) => {
			this.store = result.store;
			this.storeBuilder = result.builder;

			return true;
		});
	}

	initPull(): Promise
	{
		this.pullInstance = BX.PullClient;
		this.pullClient = BX.PULL;
		if (!this.pullClient)
		{
			return Promise.reject(new Error('Core: error setting pull client'));
		}

		this.pullClient.subscribe(new BasePullHandler());
		this.pullClient.subscribe(new RecentPullHandler());
		this.pullClient.subscribe(new NotificationPullHandler());
		this.pullClient.subscribe(new NotifierPullHandler());
		this.pullClient.subscribe(new OnlinePullHandler());
		this.pullClient.subscribe(new CounterPullHandler());

		OpenLinesLaunchResources.pullHandlers.forEach((Handler) => {
			this.pullClient.subscribe(new Handler());
		});

		this.pullClient.subscribe({
			type: this.pullInstance.SubscriptionType.Status,
			callback: this.onPullStatusChange.bind(this),
		});

		return Promise.resolve();
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
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
	/* endregion 02. Push & Pull */

	/* region 04. Template engine */
	createVue(application, config = {}): Promise
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
			bitrixVue.config.errorHandler = function(err, vm, info) {
				// eslint-disable-next-line no-console
				console.error(err, vm, info);
			};

			bitrixVue.config.warnHandler = function(warn, vm, trace) {
				// eslint-disable-next-line no-console
				console.warn(warn, vm, trace);
			};

			// todo: remove after updating Vue to 3.3+
			bitrixVue.config.unwrapInjectedRef = true;

			// eslint-disable-next-line no-param-reassign
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

	getPullClient(): Pull
	{
		return this.pullClient;
	}

	setApplicationData(data: {string: any})
	{
		this.applicationData = { ...this.applicationData, ...data };
	}

	getApplicationData(): {[key]: any}
	{
		return this.applicationData;
	}

	isOnline(): boolean
	{
		return !this.offline;
	}

	isCloud(): boolean
	{
		const settings = Extension.getSettings('im.v2.application.core');

		return settings.get('isCloud');
	}

	ready(): Promise
	{
		if (this.inited)
		{
			return Promise.resolve(this);
		}

		Core.start();

		return this.initPromise;
	}

	/* endregion 05. Methods */
}

const Core = new CoreApplication();
export { Core, CoreApplication };
