/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_application_core,im_v2_lib_analytics,im_v2_lib_localStorage,im_v2_const,im_v2_lib_logger,im_v2_lib_channel,im_v2_lib_access,im_v2_lib_feature,im_v2_lib_bulkActions) {
	'use strict';

	const TypesWithoutContext = new Set([im_v2_const.ChatType.comment]);
	const LayoutsWithoutLastOpenedElement = new Set([im_v2_const.Layout.channel.name, im_v2_const.Layout.market.name]);
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _lastOpenedElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastOpenedElement");
	var _onGoToMessageContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onGoToMessageContext");
	var _onDesktopReload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDesktopReload");
	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	var _isSameChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSameChat");
	var _handleLayoutChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleLayoutChange");
	var _handleChatChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleChatChange");
	var _handleSameChatReopen = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSameChatReopen");
	var _closeBulkActionsMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeBulkActionsMode");
	var _closeChannelComments = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeChannelComments");
	var _handleContextAccess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleContextAccess");
	var _getChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChat");
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
	    Object.defineProperty(this, _getChat, {
	      value: _getChat2
	    });
	    Object.defineProperty(this, _handleContextAccess, {
	      value: _handleContextAccess2
	    });
	    Object.defineProperty(this, _closeChannelComments, {
	      value: _closeChannelComments2
	    });
	    Object.defineProperty(this, _closeBulkActionsMode, {
	      value: _closeBulkActionsMode2
	    });
	    Object.defineProperty(this, _handleSameChatReopen, {
	      value: _handleSameChatReopen2
	    });
	    Object.defineProperty(this, _handleChatChange, {
	      value: _handleChatChange2
	    });
	    Object.defineProperty(this, _handleLayoutChange, {
	      value: _handleLayoutChange2
	    });
	    Object.defineProperty(this, _isSameChat, {
	      value: _isSameChat2
	    });
	    Object.defineProperty(this, _sendAnalytics, {
	      value: _sendAnalytics2
	    });
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
	    if (config.contextId) {
	      const hasAccess = await babelHelpers.classPrivateFieldLooseBase(this, _handleContextAccess)[_handleContextAccess](config);
	      if (!hasAccess) {
	        return Promise.resolve();
	      }
	    }
	    if (config.entityId) {
	      this.setLastOpenedElement(config.name, config.entityId);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSameChat)[_isSameChat](config)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleSameChatReopen)[_handleSameChatReopen](config);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleLayoutChange)[_handleLayoutChange]();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics](config);
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
	    if (LayoutsWithoutLastOpenedElement.has(layoutName)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _lastOpenedElement)[_lastOpenedElement][layoutName] = entityId;
	  }
	  clearCurrentLayoutEntityId() {
	    const currentLayoutName = this.getLayout().name;
	    void this.setLayout({
	      name: currentLayoutName
	    });
	    void this.deleteLastOpenedElement(currentLayoutName);
	  }
	  isChatContextAvailable(dialogId) {
	    if (!this.getLayout().contextId) {
	      return false;
	    }
	    const {
	      type
	    } = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](dialogId);
	    return !TypesWithoutContext.has(type);
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.goToMessageContext, babelHelpers.classPrivateFieldLooseBase(this, _onGoToMessageContext)[_onGoToMessageContext]);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.desktop.onReload, babelHelpers.classPrivateFieldLooseBase(this, _onDesktopReload)[_onDesktopReload].bind(this));
	  }
	  deleteLastOpenedElement(layoutName) {
	    if (LayoutsWithoutLastOpenedElement.has(layoutName)) {
	      return;
	    }
	    delete babelHelpers.classPrivateFieldLooseBase(this, _lastOpenedElement)[_lastOpenedElement][layoutName];
	  }
	  deleteLastOpenedElementById(entityId) {
	    Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _lastOpenedElement)[_lastOpenedElement]).forEach(([layoutName, lastOpenedId]) => {
	      if (lastOpenedId === entityId) {
	        delete babelHelpers.classPrivateFieldLooseBase(this, _lastOpenedElement)[_lastOpenedElement][layoutName];
	      }
	    });
	  }
	}
	async function _onGoToMessageContext2(event) {
	  const {
	    dialogId,
	    messageId
	  } = event.getData();
	  if (this.getLayout().entityId === dialogId) {
	    return;
	  }
	  const {
	    type
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](dialogId);
	  if (TypesWithoutContext.has(type)) {
	    return;
	  }
	  const isCopilotLayout = type === im_v2_const.ChatType.copilot;
	  void this.setLayout({
	    name: isCopilotLayout ? im_v2_const.Layout.copilot.name : im_v2_const.Layout.chat.name,
	    entityId: dialogId,
	    contextId: messageId
	  });
	}
	function _onDesktopReload2() {
	  this.saveCurrentLayout();
	}
	function _sendAnalytics2(config) {
	  const currentLayout = this.getLayout();
	  if (currentLayout.name === config.name) {
	    return;
	  }
	  if (config.name === im_v2_const.Layout.copilot.name) {
	    im_v2_lib_analytics.Analytics.getInstance().copilot.onOpenTab();
	  }
	  im_v2_lib_analytics.Analytics.getInstance().onOpenTab(config.name);
	}
	function _isSameChat2(config) {
	  const {
	    name,
	    entityId
	  } = this.getLayout();
	  const sameLayout = name === config.name;
	  const sameEntityId = entityId && entityId === config.entityId;
	  return sameLayout && sameEntityId;
	}
	function _handleLayoutChange2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _closeChannelComments)[_closeChannelComments]();
	  babelHelpers.classPrivateFieldLooseBase(this, _handleChatChange)[_handleChatChange]();
	}
	function _handleChatChange2() {
	  const {
	    name,
	    entityId
	  } = this.getLayout();
	  const CHAT_LAYOUTS = new Set([im_v2_const.ChatType.chat, im_v2_const.ChatType.channel, im_v2_const.ChatType.copilot, im_v2_const.ChatType.lines, im_v2_const.ChatType.openlinesV2, im_v2_const.ChatType.collab]);
	  if (CHAT_LAYOUTS.has(name) && entityId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _closeBulkActionsMode)[_closeBulkActionsMode]();
	  }
	}
	function _handleSameChatReopen2(config) {
	  const {
	    entityId: dialogId,
	    contextId
	  } = config;
	  babelHelpers.classPrivateFieldLooseBase(this, _closeChannelComments)[_closeChannelComments]();
	  if (contextId) {
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.goToMessageContext, {
	      messageId: contextId,
	      dialogId
	    });
	  }
	}
	function _closeBulkActionsMode2() {
	  im_v2_lib_bulkActions.BulkActionsManager.getInstance().disableBulkMode();
	}
	function _closeChannelComments2() {
	  const {
	    entityId: dialogId = ''
	  } = this.getLayout();
	  const isChannelOpened = im_v2_lib_channel.ChannelManager.isChannel(dialogId);
	  if (isChannelOpened) {
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeComments);
	  }
	}
	async function _handleContextAccess2(config) {
	  const {
	    contextId: messageId,
	    entityId: dialogId
	  } = config;
	  if (!messageId) {
	    return Promise.resolve(true);
	  }
	  const {
	    hasAccess,
	    errorCode
	  } = await im_v2_lib_access.AccessManager.checkMessageAccess(messageId);
	  if (!hasAccess && errorCode === im_v2_lib_access.AccessErrorCode.messageAccessDeniedByTariff) {
	    im_v2_lib_analytics.Analytics.getInstance().historyLimit.onGoToContextLimitExceeded({
	      dialogId
	    });
	    im_v2_lib_feature.FeatureManager.chatHistory.openFeatureSlider();
	    return Promise.resolve(false);
	  }
	  return Promise.resolve(true);
	}
	function _getChat2(dialogId) {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	}
	Object.defineProperty(LayoutManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.LayoutManager = LayoutManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=layout.bundle.js.map
