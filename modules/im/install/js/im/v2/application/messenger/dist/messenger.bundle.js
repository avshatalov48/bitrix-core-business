this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_component_messenger,im_v2_provider_pull,im_v2_const) {
	'use strict';

	var _applicationName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applicationName");
	class MessengerApplication {
	  constructor(params = {}) {
	    this.inited = false;
	    this.initPromise = null;
	    this.initPromiseResolver = null;
	    this.rootNode = null;
	    this.vueInstance = null;
	    this.controller = null;
	    this.bitrixVue = null;
	    Object.defineProperty(this, _applicationName, {
	      writable: true,
	      value: 'Messenger'
	    });
	    this.initPromise = new Promise(resolve => {
	      this.initPromiseResolver = resolve;
	    });
	    this.params = params;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.initCore()
	    // .then(() => this.initComponent())
	    .then(() => this.initPullHandlers()).then(() => this.initComplete());
	  }
	  initCore() {
	    return new Promise(resolve => {
	      im_v2_application_core.Core.ready().then(controller => {
	        this.controller = controller;
	        im_v2_application_core.Core.setApplicationData(im_v2_const.ApplicationName.messenger, this.params);
	        resolve();
	      });
	    });
	  }
	  initComponent(node) {
	    this.unmountComponent();
	    return this.controller.createVue(this, {
	      name: 'Messenger',
	      el: node || this.rootNode,
	      components: {
	        MessengerComponent: im_v2_component_messenger.Messenger
	      },
	      template: `<MessengerComponent />`
	    }).then(vue => {
	      this.vueInstance = vue;
	      return Promise.resolve();
	    });
	  }
	  unmountComponent() {
	    if (!this.vueInstance) {
	      return false;
	    }
	    this.bitrixVue.unmount();
	    this.vueInstance = null;
	  }
	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	  }
	  initPullHandlers() {
	    this.controller.pullClient.subscribe(new im_v2_provider_pull.SidebarPullHandler());
	    return Promise.resolve();
	  }
	  ready() {
	    if (this.inited) {
	      return Promise.resolve(this);
	    }
	    return this.initPromise;
	  }
	}

	exports.MessengerApplication = MessengerApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Component,BX.Messenger.v2.Provider.Pull,BX.Messenger.v2.Const));
//# sourceMappingURL=messenger.bundle.js.map
