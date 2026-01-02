/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,main_popup,main_sidepanel,im_v2_lib_utils,im_v2_lib_call,im_v2_application_core,im_v2_const,im_v2_lib_layout,im_v2_lib_slider,im_v2_lib_desktop,im_v2_lib_desktopApi) {
	'use strict';

	const MESSENGER_CONTAINER_SELECTOR = '.bx-im-messenger__container';
	const EscEventAction = Object.freeze({
	  handled: 'handled',
	  ignored: 'ignored'
	});
	var _messengerContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messengerContainer");
	var _wasKeyDownHandled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wasKeyDownHandled");
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _shouldIgnoreEscape = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldIgnoreEscape");
	var _isHandledBySubscriber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isHandledBySubscriber");
	var _handleActiveInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleActiveInput");
	var _handleLayoutClear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleLayoutClear");
	var _handleMessengerSliderClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMessengerSliderClose");
	var _handleDesktopAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDesktopAction");
	var _onKeyUp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onKeyUp");
	var _onKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onKeyDown");
	var _getMessengerContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessengerContainer");
	var _switchToChatLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switchToChatLayout");
	var _isExternalSliderOpened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExternalSliderOpened");
	var _handleChannelComments = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleChannelComments");
	class EscManager {
	  constructor() {
	    Object.defineProperty(this, _handleChannelComments, {
	      value: _handleChannelComments2
	    });
	    Object.defineProperty(this, _isExternalSliderOpened, {
	      value: _isExternalSliderOpened2
	    });
	    Object.defineProperty(this, _switchToChatLayout, {
	      value: _switchToChatLayout2
	    });
	    Object.defineProperty(this, _getMessengerContainer, {
	      value: _getMessengerContainer2
	    });
	    Object.defineProperty(this, _onKeyDown, {
	      value: _onKeyDown2
	    });
	    Object.defineProperty(this, _onKeyUp, {
	      value: _onKeyUp2
	    });
	    Object.defineProperty(this, _handleDesktopAction, {
	      value: _handleDesktopAction2
	    });
	    Object.defineProperty(this, _handleMessengerSliderClose, {
	      value: _handleMessengerSliderClose2
	    });
	    Object.defineProperty(this, _handleLayoutClear, {
	      value: _handleLayoutClear2
	    });
	    Object.defineProperty(this, _handleActiveInput, {
	      value: _handleActiveInput2
	    });
	    Object.defineProperty(this, _isHandledBySubscriber, {
	      value: _isHandledBySubscriber2
	    });
	    Object.defineProperty(this, _shouldIgnoreEscape, {
	      value: _shouldIgnoreEscape2
	    });
	    Object.defineProperty(this, _messengerContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _wasKeyDownHandled, {
	      writable: true,
	      value: false
	    });
	    this.keyUpEventHandler = babelHelpers.classPrivateFieldLooseBase(this, _onKeyUp)[_onKeyUp].bind(this);
	    this.keyDownEventHandler = babelHelpers.classPrivateFieldLooseBase(this, _onKeyDown)[_onKeyDown].bind(this);
	  }
	  static getInstance() {
	    var _babelHelpers$classPr;
	    babelHelpers.classPrivateFieldLooseBase(EscManager, _instance)[_instance] = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(EscManager, _instance)[_instance]) != null ? _babelHelpers$classPr : new EscManager();
	    return babelHelpers.classPrivateFieldLooseBase(EscManager, _instance)[_instance];
	  }
	  register() {
	    babelHelpers.classPrivateFieldLooseBase(this, _messengerContainer)[_messengerContainer] = babelHelpers.classPrivateFieldLooseBase(this, _getMessengerContainer)[_getMessengerContainer]();
	    main_core.Event.bind(document, 'keyup', this.keyUpEventHandler);
	    main_core.Event.bind(document, 'keydown', this.keyDownEventHandler);
	  }
	  unregister() {
	    babelHelpers.classPrivateFieldLooseBase(this, _messengerContainer)[_messengerContainer] = babelHelpers.classPrivateFieldLooseBase(this, _getMessengerContainer)[_getMessengerContainer]();
	    main_core.Event.unbind(document, 'keyup', this.keyUpEventHandler);
	    main_core.Event.unbind(document, 'keydown', this.keyDownEventHandler);
	  }
	  async handleEsc() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _wasKeyDownHandled)[_wasKeyDownHandled]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _wasKeyDownHandled)[_wasKeyDownHandled] = false;
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _shouldIgnoreEscape)[_shouldIgnoreEscape]()) {
	      return;
	    }
	    if (await babelHelpers.classPrivateFieldLooseBase(this, _isHandledBySubscriber)[_isHandledBySubscriber]()) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _handleActiveInput)[_handleActiveInput]()) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _handleChannelComments)[_handleChannelComments]()) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _handleLayoutClear)[_handleLayoutClear]()) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _handleMessengerSliderClose)[_handleMessengerSliderClose]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _handleDesktopAction)[_handleDesktopAction]();
	  }
	}
	function _shouldIgnoreEscape2() {
	  const hasVisibleCall = im_v2_lib_call.CallManager.getInstance().hasVisibleCall();
	  // popups have their own escape handling
	  const isAnyPopupShown = main_popup.PopupWindowManager.isAnyPopupShown();
	  return hasVisibleCall || isAnyPopupShown || babelHelpers.classPrivateFieldLooseBase(this, _isExternalSliderOpened)[_isExternalSliderOpened]();
	}
	async function _isHandledBySubscriber2() {
	  const eventResult = await main_core_events.EventEmitter.emitAsync(im_v2_const.EventType.key.onBeforeEscape);
	  return eventResult.includes(EscEventAction.handled);
	}
	function _handleActiveInput2() {
	  const {
	    activeElement
	  } = document;
	  if (!activeElement) {
	    return false;
	  }
	  const isMessengerFocused = babelHelpers.classPrivateFieldLooseBase(this, _messengerContainer)[_messengerContainer].contains(activeElement);
	  const isInputFocused = activeElement.matches('input');
	  if (isMessengerFocused && isInputFocused) {
	    activeElement.blur();
	    return true;
	  }
	  return false;
	}
	function _handleLayoutClear2() {
	  const layoutManager = im_v2_lib_layout.LayoutManager.getInstance();
	  const currentLayout = layoutManager.getLayout();
	  const isChatLayout = layoutManager.isChatLayout(currentLayout.name);
	  const isChatLayoutEmptyState = isChatLayout && currentLayout.entityId === '';
	  if (isChatLayoutEmptyState) {
	    return false;
	  }
	  if (isChatLayout) {
	    layoutManager.clearCurrentLayoutEntityId();
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _switchToChatLayout)[_switchToChatLayout]();
	  }
	  return true;
	}
	function _handleMessengerSliderClose2() {
	  const slider = im_v2_lib_slider.MessengerSlider.getInstance();
	  if (slider.getCurrent() && slider.isFocused()) {
	    slider.getCurrent().close();
	    return true;
	  }
	  return false;
	}
	function _handleDesktopAction2() {
	  if (!im_v2_lib_desktop.DesktopManager.isDesktop()) {
	    return false;
	  }
	  im_v2_lib_desktopApi.DesktopApi.hideWindow();
	  return true;
	}
	function _onKeyUp2(event) {
	  if (!im_v2_lib_utils.Utils.key.isCombination(event, 'Escape')) {
	    return;
	  }
	  void this.handleEsc();
	}
	function _onKeyDown2() {
	  // Viewer has its own ESC keydown handler, so we need to check if it is opened
	  babelHelpers.classPrivateFieldLooseBase(this, _wasKeyDownHandled)[_wasKeyDownHandled] = BX.UI.Viewer.Instance.isOpen();
	}
	function _getMessengerContainer2() {
	  return document.querySelector(MESSENGER_CONTAINER_SELECTOR);
	}
	function _switchToChatLayout2() {
	  void im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	    name: im_v2_const.Layout.chat,
	    entityId: ''
	  });
	}
	function _isExternalSliderOpened2() {
	  const isEmbeddedMode = im_v2_lib_layout.LayoutManager.getInstance().isEmbeddedMode();
	  if (isEmbeddedMode) {
	    return main_sidepanel.SidePanel.Instance.getOpenSlidersCount() > 0;
	  }
	  return !im_v2_lib_slider.MessengerSlider.getInstance().isFocused();
	}
	function _handleChannelComments2() {
	  const areCommentsOpened = im_v2_application_core.Core.getStore().getters['messages/comments/areOpened'];
	  if (areCommentsOpened) {
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeComments);
	  }
	  return areCommentsOpened;
	}
	Object.defineProperty(EscManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.EscEventAction = EscEventAction;
	exports.EscManager = EscManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Main,BX.SidePanel,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=esc-manager.bundle.js.map
