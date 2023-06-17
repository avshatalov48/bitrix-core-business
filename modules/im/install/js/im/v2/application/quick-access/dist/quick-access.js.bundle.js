this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_component_quickAccess,im_v2_const) {
	'use strict';

	var _applicationName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applicationName");
	class QuickAccessApplication {
	  constructor(params = {}) {
	    this.inited = false;
	    this.initPromise = null;
	    this.initPromiseResolver = null;
	    this.rootNode = null;
	    this.vueInstance = null;
	    this.controller = null;
	    Object.defineProperty(this, _applicationName, {
	      writable: true,
	      value: 'Sidebar'
	    });
	    this.initPromise = new Promise(resolve => {
	      this.initPromiseResolver = resolve;
	    });
	    this.params = params;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.initCore().then(() => this.initComponent()).then(() => this.initComplete());
	  }
	  initCore() {
	    return new Promise(resolve => {
	      im_v2_application_core.Core.ready().then(controller => {
	        this.controller = controller;
	        im_v2_application_core.Core.setApplicationData(im_v2_const.ApplicationName.quickAccess, this.params);
	        resolve();
	      });
	    });
	  }
	  initComponent() {
	    return this.controller.createVue(this, {
	      name: babelHelpers.classPrivateFieldLooseBase(this, _applicationName)[_applicationName],
	      el: this.rootNode,
	      components: {
	        QuickAccess: im_v2_component_quickAccess.QuickAccess
	      },
	      template: `<QuickAccess :compactMode="true"/>`
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

	exports.QuickAccessApplication = QuickAccessApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Component,BX.Messenger.v2.Const));
//# sourceMappingURL=quick-access.js.bundle.js.map
