this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_component_recentList,im_v2_provider_pull) {
	'use strict';

	var _applicationName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applicationName");

	class RecentApplication {
	  constructor(params = {}) {
	    this.inited = false;
	    this.initPromise = null;
	    this.initPromiseResolver = null;
	    this.rootNode = null;
	    this.vueInstance = null;
	    this.controller = null;
	    Object.defineProperty(this, _applicationName, {
	      writable: true,
	      value: 'RecentList'
	    });
	    this.initPromise = new Promise(resolve => {
	      this.initPromiseResolver = resolve;
	    });
	    this.params = params;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.initCore().then(() => this.initPullHandler()).then(() => this.initComponent()).then(() => this.initComplete());
	  }

	  initCore() {
	    return new Promise(resolve => {
	      im_v2_application_core.Core.ready().then(controller => {
	        this.controller = controller;
	        resolve();
	      });
	    });
	  }

	  initPullHandler() {
	    this.controller.pullClient.subscribe(new im_v2_provider_pull.RecentPullHandler({
	      store: this.controller.getStore(),
	      controller: this.controller,
	      application: this
	    }));
	    return Promise.resolve();
	  }

	  initComponent() {
	    return this.controller.createVue(this, {
	      name: babelHelpers.classPrivateFieldLooseBase(this, _applicationName)[_applicationName],
	      el: this.rootNode,
	      components: {
	        RecentListComponent: im_v2_component_recentList.RecentList
	      },
	      template: `<RecentListComponent />`
	    }).then(vue => {
	      this.vueInstance = vue;
	      return Promise.resolve();
	    });
	  }

	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	  }

	  ready() {
	    if (this.inited) {
	      return Promise.resolve(this);
	    }

	    return this.initPromise;
	  }

	}

	exports.RecentApplication = RecentApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2,BX.Messenger.v2.Provider.Pull));
//# sourceMappingURL=recent.bundle.js.map
