this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,ui_vue3,ui_vue3_vuex,pull_client,rest_client,im_v2_application_launch,im_v2_model,im_v2_provider_pull,im_v2_lib_logger) {
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
	    this.initStorage().then(() => this.initPullClient()).then(() => this.initComplete()).catch(error => {
	      im_v2_lib_logger.Logger.error('Error initializing core controller', error);
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
	    this.initPull();
	    this.initRest();
	  }
	  initStorage() {
	    const builder = ui_vue3_vuex.Builder.init().addModel(im_v2_model.ApplicationModel.create()).addModel(im_v2_model.MessagesModel.create()).addModel(im_v2_model.DialoguesModel.create()).addModel(im_v2_model.FilesModel.create()).addModel(im_v2_model.UsersModel.create()).addModel(im_v2_model.RecentModel.create()).addModel(im_v2_model.NotificationsModel.create()).addModel(im_v2_model.SidebarModel.create()).addModel(im_v2_model.MarketModel.create());
	    return builder.build().then(result => {
	      this.store = result.store;
	      this.storeBuilder = result.builder;
	      return Promise.resolve();
	    });
	  }
	  initPullClient() {
	    if (!this.pullClient) {
	      return false;
	    }
	    this.pullClient.subscribe(new im_v2_provider_pull.BasePullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.RecentPullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.NotificationPullHandler());
	    this.pullClient.subscribe(new im_v2_provider_pull.NotifierPullHandler());
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
	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	  }
	  initRest() {
	    this.restInstance = rest_client.RestClient;
	    this.restClient = rest_client.rest;
	    return Promise.resolve();
	  }
	  initPull() {
	    this.pullInstance = pull_client.PullClient;
	    this.pullClient = pull_client.PULL;
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
	  onUsersOnlineChange(data) {
	    if (!['list', 'userStatus'].includes(data.command)) {
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
	  setApplicationData(applicationName, applicationData) {
	    this.applicationData[applicationName] = applicationData;
	  }
	  getApplicationData(applicationName) {
	    var _this$applicationData;
	    return (_this$applicationData = this.applicationData[applicationName]) != null ? _this$applicationData : {};
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
	    return this.initPromise;
	  }

	  /* endregion 05. Methods */
	}

	const Core = new CoreApplication();

	exports.Core = Core;
	exports.CoreApplication = CoreApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX,BX.Vue3,BX.Vue3.Vuex,BX,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Model,BX.Messenger.v2.Provider.Pull,BX.Messenger.v2.Lib));
//# sourceMappingURL=core.bundle.js.map
