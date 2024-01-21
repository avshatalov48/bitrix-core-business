/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_application_core,im_v2_lib_localStorage,im_v2_lib_logger,im_v2_const) {
	'use strict';

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _lastOpenedElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastOpenedElement");
	var _onGoToMessageContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onGoToMessageContext");
	var _onDesktopReload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDesktopReload");
	class LayoutManager {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  static init() {
	    LayoutManager.getInstance();
	  }
	  constructor() {
	    Object.defineProperty(this, _onDesktopReload, {
	      value: _onDesktopReload2
	    });
	    Object.defineProperty(this, _onGoToMessageContext, {
	      value: _onGoToMessageContext2
	    });
	    Object.defineProperty(this, _lastOpenedElement, {
	      writable: true,
	      value: {}
	    });
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.goToMessageContext, babelHelpers.classPrivateFieldLooseBase(this, _onGoToMessageContext)[_onGoToMessageContext].bind(this));
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.desktop.onReload, babelHelpers.classPrivateFieldLooseBase(this, _onDesktopReload)[_onDesktopReload].bind(this));
	  }
	  async setLayout(config) {
	    if (config.entityId) {
	      this.setLastOpenedElement(config.name, config.entityId);
	    }
	    return im_v2_application_core.Core.getStore().dispatch('application/setLayout', config);
	  }
	  getLayout() {
	    return im_v2_application_core.Core.getStore().getters['application/getLayout'];
	  }
	  saveCurrentLayout() {
	    const currentLayout = this.getLayout();
	    im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.layoutConfig, {
	      name: currentLayout.name,
	      entityId: currentLayout.entityId
	    });
	  }
	  restoreLastLayout() {
	    const layoutConfig = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.layoutConfig);
	    if (!layoutConfig) {
	      return Promise.resolve();
	    }
	    im_v2_lib_logger.Logger.warn('LayoutManager: last layout was restored', layoutConfig);
	    im_v2_lib_localStorage.LocalStorageManager.getInstance().remove(im_v2_const.LocalStorageKey.layoutConfig);
	    return this.setLayout(layoutConfig);
	  }
	  getLastOpenedElement(layoutName) {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _lastOpenedElement)[_lastOpenedElement][layoutName]) != null ? _babelHelpers$classPr : null;
	  }
	  setLastOpenedElement(layoutName, entityId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _lastOpenedElement)[_lastOpenedElement][layoutName] = entityId;
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.goToMessageContext, babelHelpers.classPrivateFieldLooseBase(this, _onGoToMessageContext)[_onGoToMessageContext]);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.desktop.onReload, babelHelpers.classPrivateFieldLooseBase(this, _onDesktopReload)[_onDesktopReload].bind(this));
	  }
	}
	function _onGoToMessageContext2(event) {
	  const {
	    dialogId,
	    messageId
	  } = event.getData();
	  if (this.getLayout().entityId === dialogId) {
	    return;
	  }
	  this.setLayout({
	    name: im_v2_const.Layout.chat.name,
	    entityId: dialogId,
	    contextId: messageId
	  });
	}
	function _onDesktopReload2() {
	  this.saveCurrentLayout();
	}
	Object.defineProperty(LayoutManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.LayoutManager = LayoutManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=layout.bundle.js.map
