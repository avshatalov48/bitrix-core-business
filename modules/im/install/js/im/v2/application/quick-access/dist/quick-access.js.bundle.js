/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_component_quickAccess,im_v2_const,im_public) {
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
	    this.initCore().then(() => this.initComponent()).then(() => this.initComplete()).then(() => this.checkGetParams());
	  }
	  initCore() {
	    return new Promise(resolve => {
	      // eslint-disable-next-line promise/catch-or-return
	      im_v2_application_core.Core.ready().then(controller => {
	        this.controller = controller;
	        im_v2_application_core.Core.setApplicationData(this.params);
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
	      template: '<QuickAccess :compactMode="true"/>'
	    }).then(vue => {
	      this.vueInstance = vue;
	    });
	  }
	  initComplete() {
	    this.inited = true;
	    this.initPromiseResolver(this);
	    return Promise.resolve();
	  }
	  checkGetParams() {
	    const urlParams = new URLSearchParams(window.location.search);
	    if (urlParams.has(im_v2_const.GetParameter.openNotifications)) {
	      im_public.Messenger.openNotifications();
	    } else if (urlParams.has(im_v2_const.GetParameter.openHistory)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openHistory);
	      im_public.Messenger.openLinesHistory(dialogId);
	    } else if (urlParams.has(im_v2_const.GetParameter.openLines)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openLines);
	      im_public.Messenger.openLines(dialogId);
	    } else if (urlParams.has(im_v2_const.GetParameter.openChat)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openChat);
	      im_public.Messenger.openChat(dialogId);
	    }
	  }
	  ready() {
	    if (this.inited) {
	      return Promise.resolve(this);
	    }
	    return this.initPromise;
	  }
	}

	exports.QuickAccessApplication = QuickAccessApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Component,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=quick-access.js.bundle.js.map
