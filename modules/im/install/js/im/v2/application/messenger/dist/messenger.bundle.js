/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_component_messenger,im_v2_provider_pull) {
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

	    // eslint-disable-next-line promise/catch-or-return
	    this.initCore().then(() => this.initPullHandlers()).then(() => this.initComplete());
	  }
	  async initCore() {
	    im_v2_application_core.Core.setApplicationData(this.params);
	    this.controller = await im_v2_application_core.Core.ready();
	    return true;
	  }
	  initPullHandlers() {
	    this.controller.pullClient.subscribe(new im_v2_provider_pull.SidebarPullHandler());
	    return Promise.resolve();
	  }
	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	  }
	  async initComponent(node) {
	    this.unmountComponent();
	    this.vueInstance = await this.controller.createVue(this, {
	      name: babelHelpers.classPrivateFieldLooseBase(this, _applicationName)[_applicationName],
	      el: node || this.rootNode,
	      components: {
	        MessengerComponent: im_v2_component_messenger.Messenger
	      },
	      template: '<MessengerComponent />'
	    });
	    return true;
	  }
	  unmountComponent() {
	    if (!this.vueInstance) {
	      return;
	    }
	    this.bitrixVue.unmount();
	    this.vueInstance = null;
	  }
	  ready() {
	    if (this.inited) {
	      return Promise.resolve(this);
	    }
	    return this.initPromise;
	  }
	}

	exports.MessengerApplication = MessengerApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Component,BX.Messenger.v2.Provider.Pull));
//# sourceMappingURL=messenger.bundle.js.map
