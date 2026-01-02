/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_component_quickAccess) {
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

	    // eslint-disable-next-line promise/catch-or-return
	    this.initCore().then(() => this.initComponent()).then(() => this.initComplete());
	  }
	  async initCore() {
	    im_v2_application_core.Core.setApplicationData(this.params);
	    this.controller = await im_v2_application_core.Core.ready();
	    return true;
	  }
	  async initComponent() {
	    this.vueInstance = await this.controller.createVue(this, {
	      name: babelHelpers.classPrivateFieldLooseBase(this, _applicationName)[_applicationName],
	      el: this.rootNode,
	      components: {
	        QuickAccess: im_v2_component_quickAccess.QuickAccess
	      },
	      template: '<QuickAccess />'
	    });
	    return true;
	  }
	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	    return Promise.resolve();
	  }
	  ready() {
	    if (this.inited) {
	      return Promise.resolve(this);
	    }
	    return this.initPromise;
	  }
	}

	exports.QuickAccessApplication = QuickAccessApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Component));
//# sourceMappingURL=quick-access.js.bundle.js.map
