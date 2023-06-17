this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
(function (exports,im_oldChatEmbedding_application_launch,pull_client,rest_client,main_core,ui_vue3,ui_vue3_vuex,im_oldChatEmbedding_model,im_oldChatEmbedding_const,im_oldChatEmbedding_provider_pull,im_oldChatEmbedding_lib_logger,im_oldChatEmbedding_lib_utils,im_oldChatEmbedding_lib_smileManager) {
	'use strict';

	class CoreApplication {
	  /* region 01. Initialize and store data */
	  constructor(params = {}) {
	    this.applicationData = {};
	    this.inited = false;
	    this.initPromise = new Promise(resolve => {
	      this.initPromiseResolver = resolve;
	    });
	    this.offline = false;
	    this.vuexAdditionalModel = [];
	    this.store = null;
	    this.storeBuilder = null;
	    this.pullHandlers = [];
	    this.prepareParams(params);
	    this.initStorage().then(() => this.initPullClient()).then(() => this.initComplete()).catch(error => {
	      im_oldChatEmbedding_lib_logger.Logger.error('Error initializing core controller', error);
	    });
	  }
	  prepareParams(params) {
	    var _params$host;
	    if (!main_core.Type.isUndefined(params.localize)) {
	      this.localize = params.localize;
	    } else {
	      this.localize = BX ? {
	        ...BX.message
	      } : {};
	    }
	    this.host = (_params$host = params.host) != null ? _params$host : location.origin;
	    this.userId = this.prepareUserId(params.userId);
	    this.siteId = this.getLocalize('SITE_ID') || 's1';
	    if (main_core.Type.isStringFilled(params.siteId)) {
	      this.siteId = params.siteId;
	    }
	    this.siteDir = this.getLocalize('SITE_DIR') || 's1';
	    if (main_core.Type.isStringFilled(params.siteDir)) {
	      this.siteDir = params.siteDir;
	    }
	    this.languageId = this.getLocalize('LANGUAGE_ID') || 'en';
	    if (main_core.Type.isStringFilled(params.languageId)) {
	      this.languageId = params.languageId;
	    }
	    this.initPull(params);
	    this.initRest(params);
	    this.initVuexBuilder(params);
	  }
	  initStorage() {
	    const applicationVariables = {
	      common: {
	        host: this.getHost(),
	        userId: this.getUserId(),
	        siteId: this.getSiteId(),
	        languageId: this.getLanguageId()
	      },
	      dialog: {
	        messageLimit: 50,
	        enableReadMessages: true
	      },
	      device: {
	        type: im_oldChatEmbedding_lib_utils.Utils.device.isMobile() ? im_oldChatEmbedding_const.DeviceType.mobile : im_oldChatEmbedding_const.DeviceType.desktop,
	        orientation: im_oldChatEmbedding_lib_utils.Utils.device.getOrientation()
	      }
	    };
	    const builder = ui_vue3_vuex.Builder.init().addModel(im_oldChatEmbedding_model.ApplicationModel.create().useDatabase(false).setVariables(applicationVariables)).addModel(im_oldChatEmbedding_model.DialoguesModel.create().useDatabase(false)).addModel(im_oldChatEmbedding_model.UsersModel.create().useDatabase(false)).addModel(im_oldChatEmbedding_model.RecentModel.create().useDatabase(false));
	    this.vuexAdditionalModel.forEach(model => {
	      builder.addModel(model);
	    });
	    builder.setDatabaseConfig({
	      name: this.vuexBuilder.databaseName,
	      type: this.vuexBuilder.databaseType,
	      siteId: this.getSiteId(),
	      userId: this.getUserId()
	    });
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
	    this.pullClient.subscribe(new im_oldChatEmbedding_provider_pull.BasePullHandler());
	    this.pullClient.subscribe(new im_oldChatEmbedding_provider_pull.RecentPullHandler());
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
	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	  }
	  initRest(params) {
	    this.restInstance = rest_client.RestClient;
	    this.restClient = rest_client.rest;
	    if (!main_core.Type.isUndefined(params.rest)) {
	      if (!main_core.Type.isUndefined(params.rest.instance)) {
	        this.restInstance = params.rest.instance;
	      }
	      if (!main_core.Type.isUndefined(params.rest.client)) {
	        this.restClient = params.rest.client;
	      }
	    }
	    return Promise.resolve();
	  }
	  initPull(params) {
	    this.pullInstance = pull_client.PullClient;
	    this.pullClient = pull_client.PULL;
	    if (params.pull) {
	      if (params.pull.instance) {
	        this.pullInstance = params.pull.instance;
	      }
	      if (params.pull.client) {
	        this.pullClient = params.pull.client;
	      }
	    }
	  }
	  initVuexBuilder(params) {
	    this.vuexBuilder = {
	      database: false,
	      databaseName: 'desktop/im',
	      databaseType: ui_vue3_vuex.BuilderDatabaseType.indexedDb
	    };
	    if (params.vuexBuilder) {
	      if (main_core.Type.isBoolean(params.vuexBuilder.database)) {
	        this.vuexBuilder.database = params.vuexBuilder.database;
	      }
	      if (main_core.Type.isStringFilled(params.vuexBuilder.databaseName)) {
	        this.vuexBuilder.databaseName = params.vuexBuilder.databaseName;
	      }
	      if (main_core.Type.isStringFilled(params.vuexBuilder.databaseType)) {
	        this.vuexBuilder.databaseType = params.vuexBuilder.databaseType;
	      }
	      if (main_core.Type.isArray(params.vuexBuilder.models)) {
	        params.vuexBuilder.models.forEach(model => {
	          this.addVuexModel(model);
	        });
	      }
	    }
	  }
	  prepareUserId(userId) {
	    let result = 0;
	    if (!main_core.Type.isUndefined(userId)) {
	      const parsedUserId = Number.parseInt(params.userId, 10);
	      if (parsedUserId) {
	        result = parsedUserId;
	      }
	    } else if (this.getLocalize('USER_ID')) {
	      result = Number.parseInt(this.getLocalize('USER_ID'), 10);
	    }
	    return result;
	  }

	  /* endregion 01. Initialize and store data */

	  /* region 02. Push & Pull */

	  eventStatusInteraction(data) {
	    if (data.status === this.pullInstance.PullStatus.Online) {
	      this.offline = false;
	    } else if (data.status === this.pullInstance.PullStatus.Offline) {
	      this.offline = true;
	    }
	  }
	  eventOnlineInteraction(data) {
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
	    let beforeCreateFunction = () => {};
	    if (config.beforeCreate) {
	      beforeCreateFunction = config.beforeCreate;
	    }
	    let unmountedFunction = () => {};
	    if (config.unmounted) {
	      unmountedFunction = config.unmounted;
	    }
	    let createdFunction = () => {};
	    if (config.created) {
	      createdFunction = config.created;
	    }
	    const controller = this;
	    const initConfig = {
	      // store: this.store,
	      beforeCreate() {
	        this.$bitrix.Data.set('controller', controller);
	        this.$bitrix.Application.set(application);
	        this.$bitrix.Loc.setMessage(controller.localize);
	        if (controller.restClient) {
	          this.$bitrix.RestClient.set(controller.restClient);
	        }
	        if (controller.pullClient) {
	          this.$bitrix.PullClient.set(controller.pullClient);
	        }
	        beforeCreateFunction.bind(this)();
	      },
	      created() {
	        createdFunction.bind(this)();
	      },
	      unmounted() {
	        unmountedFunction.bind(this)();
	      }
	    };
	    if (config.el) {
	      initConfig.el = config.el;
	    }
	    if (config.template) {
	      initConfig.template = config.template;
	    }
	    if (config.computed) {
	      initConfig.computed = config.computed;
	    }
	    if (config.data) {
	      initConfig.data = config.data;
	    }
	    if (config.name) {
	      initConfig.name = config.name;
	    }
	    if (config.components) {
	      initConfig.components = config.components;
	    }
	    const initConfigCreatedFunction = initConfig.created;
	    return new Promise(resolve => {
	      initConfig.created = function () {
	        initConfigCreatedFunction.bind(this)();
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
	  addVuexModel(model) {
	    this.vuexAdditionalModel.push(model);
	  }
	  isOnline() {
	    return !this.offline;
	  }
	  ready() {
	    if (this.inited) {
	      return Promise.resolve(this);
	    }
	    return this.initPromise;
	  }

	  /* endregion 05. Methods */

	  /* region 06. Interaction and utils */
	  addLocalize(phrases) {
	    if (!main_core.Type.isPlainObject(phrases)) {
	      return false;
	    }
	    Object.entries(phrases).forEach(([key, value]) => {
	      this.localize[key] = value;
	    });
	    return true;
	  }
	  getLocalize(name) {
	    let phrase = '';
	    if (typeof name === 'undefined') {
	      return this.localize;
	    } else if (typeof this.localize[name.toString()] === 'undefined') {
	      im_oldChatEmbedding_lib_logger.Logger.warn(`Controller.Core.getLocalize: message with code '${name.toString()}' is undefined.`);
	    } else {
	      phrase = this.localize[name];
	    }
	    return phrase;
	  }

	  /* endregion 06. Interaction and utils */
	}

	const Core = new CoreApplication();

	exports.Core = Core;
	exports.CoreApplication = CoreApplication;

}((this.BX.Messenger.Embedding.Application = this.BX.Messenger.Embedding.Application || {}),BX.Messenger.Embedding.Application,BX,BX,BX,BX.Vue3,BX.Vue3.Vuex,BX.Messenger.Embedding.Model,BX.Messenger.Embedding.Const,BX.Messenger.Embedding.Provider.Pull,BX.Messenger.Embedding.Lib,BX.Messenger.Embedding.Lib,BX.Messenger.Embedding.Lib));
//# sourceMappingURL=core.bundle.js.map
