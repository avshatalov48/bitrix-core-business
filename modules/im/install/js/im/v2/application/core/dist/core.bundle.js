/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,ui_vue3,ui_vue3_vuex,pull_client,rest_client,im_v2_application_launch,im_v2_model,im_v2_provider_pull,im_v2_lib_logger,imopenlines_v2_lib_launchResources) {
	'use strict';

	class CoreApplication {
	  /* region 01. Initialize and store data */
	  constructor() {
	    this.applicationData = {};
	    this.inited = false;
	    this.initPromise = new Promise(resolve => {
	      this.initPromiseResolver = resolve;
	    });
	    this.offline = false;
	    this.vuexAdditionalModel = [];
	    this.store = null;
	    this.storeBuilder = null;
	    this.prepareVariables();
	    this.initRestClient();
	  }
	  start() {
	    this.initStorage().then(() => this.initPull()).then(() => this.initComplete()).catch(error => {
	      im_v2_lib_logger.Logger.error('Core: error starting core application', error);
	    });
	  }
	  prepareVariables() {
	    var _Number$parseInt, _Loc$getMessage, _Loc$getMessage2, _Loc$getMessage3;
	    this.localize = BX ? {
	      ...BX.message
	    } : {};
	    this.host = location.origin;
	    this.userId = (_Number$parseInt = Number.parseInt(main_core.Loc.getMessage('USER_ID'), 10)) != null ? _Number$parseInt : 0;
	    this.siteId = (_Loc$getMessage = main_core.Loc.getMessage('SITE_ID')) != null ? _Loc$getMessage : 's1';
	    this.siteDir = (_Loc$getMessage2 = main_core.Loc.getMessage('SITE_DIR')) != null ? _Loc$getMessage2 : 's1';
	    this.languageId = (_Loc$getMessage3 = main_core.Loc.getMessage('LANGUAGE_ID')) != null ? _Loc$getMessage3 : 'en';
	  }
	  initRestClient() {
	    this.restInstance = BX.RestClient;
	    this.restClient = BX.rest;
	  }
	  initStorage() {
	    const builder = ui_vue3_vuex.Builder.init().addModel(im_v2_model.ApplicationModel.create()).addModel(im_v2_model.MessagesModel.create()).addModel(im_v2_model.ChatsModel.create()).addModel(im_v2_model.FilesModel.create()).addModel(im_v2_model.UsersModel.create()).addModel(im_v2_model.RecentModel.create()).addModel(im_v2_model.CountersModel.create()).addModel(im_v2_model.NotificationsModel.create()).addModel(im_v2_model.SidebarModel.create()).addModel(im_v2_model.MarketModel.create()).addModel(im_v2_model.CopilotModel.create());
	    imopenlines_v2_lib_launchResources.OpenLinesLaunchResources.models.forEach(model => {
	      builder.addModel(model.create());
	    });
	    return builder.build().then(result => {
	      this.store = result.store;
	      this.storeBuilder = result.builder;
	      return true;
	    });
	  }
	  initPull() {
	    this.pullInstance = BX.PullClient;
	    this.pullClient = BX.PULL;
	    if (!this.pullClient) {
	      return Promise.reject(new Error('Core: error setting pull client'));
	    }
	    this.pullClient.subscribe(new im_v2_provider_pull.BasePullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.RecentPullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.NotificationPullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.NotifierPullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.OnlinePullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.CounterPullHandler());
	    imopenlines_v2_lib_launchResources.OpenLinesLaunchResources.pullHandlers.forEach(Handler => {
	      this.pullClient.subscribe(new Handler());
	    });
	    this.pullClient.subscribe({
	      type: this.pullInstance.SubscriptionType.Status,
	      callback: this.onPullStatusChange.bind(this)
	    });
	    return Promise.resolve();
	  }
	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	  }
	  /* endregion 01. Initialize and store data */

	  /* region 02. Push & Pull */
	  onPullStatusChange(data) {
	    if (data.status === this.pullInstance.PullStatus.Online) {
	      this.offline = false;
	    } else if (data.status === this.pullInstance.PullStatus.Offline) {
	      this.offline = true;
	    }
	  }
	  /* endregion 02. Push & Pull */

	  /* region 04. Template engine */
	  createVue(application, config = {}) {
	    const initConfig = {};
	    if (config.el) {
	      initConfig.el = config.el;
	    }
	    if (config.template) {
	      initConfig.template = config.template;
	    }
	    if (config.name) {
	      initConfig.name = config.name;
	    }
	    if (config.components) {
	      initConfig.components = config.components;
	    }
	    return new Promise(resolve => {
	      initConfig.created = function () {
	        resolve(this);
	      };
	      const bitrixVue = ui_vue3.BitrixVue.createApp(initConfig);
	      bitrixVue.config.errorHandler = function (err, vm, info) {
	        // eslint-disable-next-line no-console
	        console.error(err, vm, info);
	      };
	      bitrixVue.config.warnHandler = function (warn, vm, trace) {
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
	  getHost() {
	    return this.host;
	  }
	  getUserId() {
	    return this.userId;
	  }
	  getSiteId() {
	    return this.siteId;
	  }
	  getLanguageId() {
	    return this.languageId;
	  }
	  getStore() {
	    return this.store;
	  }
	  getRestClient() {
	    return this.restClient;
	  }
	  getPullClient() {
	    return this.pullClient;
	  }
	  setApplicationData(data) {
	    this.applicationData = {
	      ...this.applicationData,
	      ...data
	    };
	  }
	  getApplicationData() {
	    return this.applicationData;
	  }
	  isOnline() {
	    return !this.offline;
	  }
	  isCloud() {
	    const settings = main_core.Extension.getSettings('im.v2.application.core');
	    return settings.get('isCloud');
	  }
	  ready() {
	    if (this.inited) {
	      return Promise.resolve(this);
	    }
	    Core.start();
	    return this.initPromise;
	  }

	  /* endregion 05. Methods */
	}

	const Core = new CoreApplication();

	exports.Core = Core;
	exports.CoreApplication = CoreApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX,BX.Vue3,BX.Vue3.Vuex,BX,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Model,BX.Messenger.v2.Provider.Pull,BX.Messenger.v2.Lib,BX.OpenLines.v2.Lib));
//# sourceMappingURL=core.bundle.js.map
