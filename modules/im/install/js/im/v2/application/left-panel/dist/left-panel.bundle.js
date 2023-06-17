this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_component_oldChatEmbedding_leftPanel) {
	'use strict';

	var _applicationName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applicationName");
	class LeftPanelApplication {
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
	      value: 'LeftPanel'
	    });
	    this.initPromise = new Promise(resolve => {
	      this.initPromiseResolver = resolve;
	    });
	    this.params = params;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.initCore().then(() => this.initComplete());
	  }
	  initCore() {
	    return new Promise(resolve => {
	      im_v2_application_core.Core.ready().then(controller => {
	        this.controller = controller;
	        resolve();
	      });
	    });
	  }
	  initComponent(node) {
	    if (this.vueInstance) {
	      this.bitrixVue.unmount();
	      this.vueInstance = null;
	    }
	    return this.controller.createVue(this, {
	      name: babelHelpers.classPrivateFieldLooseBase(this, _applicationName)[_applicationName],
	      el: node,
	      components: {
	        LeftPanelComponent: im_v2_component_oldChatEmbedding_leftPanel.LeftPanel
	      },
	      template: `<LeftPanelComponent />`
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

	exports.LeftPanelApplication = LeftPanelApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.ComponentLegacy));
//# sourceMappingURL=left-panel.bundle.js.map
