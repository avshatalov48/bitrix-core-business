this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,ui_vue3_vuex,im_call,im_public,im_v2_application_core,im_v2_lib_slider,im_v2_const,im_v2_lib_logger,im_v2_lib_soundNotification) {
	'use strict';

	var _controller = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("controller");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _getController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getController");
	var _subscribeToEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToEvents");
	var _onCallCreated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCallCreated");
	var _onCallJoin = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCallJoin");
	var _onCallLeave = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCallLeave");
	var _onCallDestroy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCallDestroy");
	var _onOpenChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onOpenChat");
	var _checkCallSupport = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkCallSupport");
	var _checkUserCallSupport = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkUserCallSupport");
	var _checkChatCallSupport = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkChatCallSupport");
	var _pushServerIsActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pushServerIsActive");
	var _getCurrentDialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentDialogId");
	class CallManager {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  static init() {
	    CallManager.getInstance();
	  }
	  constructor() {
	    Object.defineProperty(this, _getCurrentDialogId, {
	      value: _getCurrentDialogId2
	    });
	    Object.defineProperty(this, _pushServerIsActive, {
	      value: _pushServerIsActive2
	    });
	    Object.defineProperty(this, _checkChatCallSupport, {
	      value: _checkChatCallSupport2
	    });
	    Object.defineProperty(this, _checkUserCallSupport, {
	      value: _checkUserCallSupport2
	    });
	    Object.defineProperty(this, _checkCallSupport, {
	      value: _checkCallSupport2
	    });
	    Object.defineProperty(this, _onOpenChat, {
	      value: _onOpenChat2
	    });
	    Object.defineProperty(this, _onCallDestroy, {
	      value: _onCallDestroy2
	    });
	    Object.defineProperty(this, _onCallLeave, {
	      value: _onCallLeave2
	    });
	    Object.defineProperty(this, _onCallJoin, {
	      value: _onCallJoin2
	    });
	    Object.defineProperty(this, _onCallCreated, {
	      value: _onCallCreated2
	    });
	    Object.defineProperty(this, _subscribeToEvents, {
	      value: _subscribeToEvents2
	    });
	    Object.defineProperty(this, _getController, {
	      value: _getController2
	    });
	    Object.defineProperty(this, _controller, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller] = babelHelpers.classPrivateFieldLooseBase(this, _getController)[_getController]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToEvents)[_subscribeToEvents]();
	  }
	  startCall(dialogId, withVideo = true) {
	    im_v2_lib_logger.Logger.warn('CallManager: startCall', dialogId, withVideo);
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].startCall(dialogId, withVideo);
	  }
	  joinCall(callId, withVideo = true) {
	    im_v2_lib_logger.Logger.warn('CallManager: joinCall', callId, withVideo);
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].joinCall(callId, withVideo);
	  }
	  leaveCurrentCall() {
	    im_v2_lib_logger.Logger.warn('CallManager: leaveCurrentCall');
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].leaveCurrentCall();
	  }
	  foldCurrentCall() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].hasActiveCall()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].fold();
	  }
	  unfoldCurrentCall() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].hasActiveCall()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].unfold();
	  }
	  getCurrentCallDialogId() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].hasActiveCall()) {
	      return '';
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].currentCall.associatedEntity.id;
	  }
	  hasCurrentCall() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].hasActiveCall();
	  }
	  hasCurrentScreenSharing() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].hasActiveCall()) {
	      return false;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].currentCall.isScreenSharingStarted();
	  }
	  hasVisibleCall() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].hasActiveCall()) {
	      return false;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].hasVisibleCall();
	  }
	  startTest() {
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].test();
	  }
	  chatCanBeCalled(dialogId) {
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['dialogues/get'](dialogId);
	    if (!dialog) {
	      return false;
	    }
	    const isChat = dialog.type !== im_v2_const.DialogType.user;
	    const callAllowed = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['dialogues/getChatOption'](dialog.type, im_v2_const.ChatOption.call);
	    if (isChat && !callAllowed) {
	      return false;
	    }
	    const callSupported = babelHelpers.classPrivateFieldLooseBase(this, _checkCallSupport)[_checkCallSupport](dialogId);
	    const isAnnouncement = dialog.type === im_v2_const.DialogType.announcement;
	    const isExternalTelephonyCall = dialog.type === im_v2_const.DialogType.call;
	    const hasCurrentCall = this.hasCurrentCall();
	    return callSupported && !isAnnouncement && !isExternalTelephonyCall && !hasCurrentCall;
	  }

	  // endregion call events
	}
	function _getController2() {
	  return new im_call.Controller({
	    init: true,
	    language: im_v2_application_core.Core.getLanguageId(),
	    messengerFacade: {
	      getDefaultZIndex: () => im_v2_lib_slider.MessengerSlider.getInstance().getZIndex(),
	      isMessengerOpen: () => im_v2_lib_slider.MessengerSlider.getInstance().isOpened(),
	      isSliderFocused: () => im_v2_lib_slider.MessengerSlider.getInstance().isFocused(),
	      isThemeDark: () => false,
	      openMessenger: dialogId => {
	        return im_public.Messenger.openChat(dialogId);
	      },
	      openHistory: () => {},
	      openSettings: () => {},
	      // TODO
	      openHelpArticle: () => {},
	      // TODO
	      getContainer: () => document.querySelector(`.${CallManager.viewContainerClass}`),
	      getMessageCount: () => babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['recent/getTotalCounter'],
	      getCurrentDialogId: () => babelHelpers.classPrivateFieldLooseBase(this, _getCurrentDialogId)[_getCurrentDialogId](),
	      isPromoRequired: () => false,
	      repeatSound: (soundType, timeout, force) => {
	        im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playLoop(soundType, timeout, force);
	      },
	      stopRepeatSound: soundType => {
	        im_v2_lib_soundNotification.SoundNotificationManager.getInstance().stop(soundType);
	      }
	    },
	    events: {}
	  });
	}
	function _subscribeToEvents2() {
	  main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onOpenChat, babelHelpers.classPrivateFieldLooseBase(this, _onOpenChat)[_onOpenChat].bind(this));
	  main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onOpenNotifications, this.foldCurrentCall.bind(this));
	  main_core_events.EventEmitter.subscribe('CallEvents::callCreated', babelHelpers.classPrivateFieldLooseBase(this, _onCallCreated)[_onCallCreated].bind(this));
	}
	function _onCallCreated2(event) {
	  const {
	    call
	  } = event.getData()[0];
	  call.addEventListener(BX.Call.Event.onJoin, babelHelpers.classPrivateFieldLooseBase(this, _onCallJoin)[_onCallJoin].bind(this));
	  call.addEventListener(BX.Call.Event.onLeave, babelHelpers.classPrivateFieldLooseBase(this, _onCallLeave)[_onCallLeave].bind(this));
	  call.addEventListener(BX.Call.Event.onDestroy, babelHelpers.classPrivateFieldLooseBase(this, _onCallDestroy)[_onCallDestroy].bind(this));
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/calls/addActiveCall', {
	    dialogId: call.associatedEntity.id,
	    name: call.associatedEntity.name,
	    call: call,
	    state: im_v2_const.RecentCallStatus.waiting
	  });
	}
	function _onCallJoin2(event) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/calls/updateActiveCall', {
	    dialogId: event.call.associatedEntity.id,
	    fields: {
	      state: im_v2_const.RecentCallStatus.joined
	    }
	  });
	}
	function _onCallLeave2(event) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/calls/updateActiveCall', {
	    dialogId: event.call.associatedEntity.id,
	    fields: {
	      state: im_v2_const.RecentCallStatus.waiting
	    }
	  });
	}
	function _onCallDestroy2(event) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/calls/deleteActiveCall', {
	    dialogId: event.call.associatedEntity.id
	  });
	}
	function _onOpenChat2(event) {
	  const callDialogId = this.getCurrentCallDialogId();
	  const openedChat = event.getData().dialogId;
	  if (callDialogId === openedChat) {
	    return;
	  }
	  this.foldCurrentCall();
	}
	function _checkCallSupport2(dialogId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _pushServerIsActive)[_pushServerIsActive]() || !BX.Call.Util.isWebRTCSupported()) {
	    return false;
	  }
	  const userId = Number.parseInt(dialogId, 10);
	  return userId > 0 ? babelHelpers.classPrivateFieldLooseBase(this, _checkUserCallSupport)[_checkUserCallSupport](userId) : babelHelpers.classPrivateFieldLooseBase(this, _checkChatCallSupport)[_checkChatCallSupport](dialogId);
	}
	function _checkUserCallSupport2(userId) {
	  const user = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/get'](userId);
	  return user && user.status !== 'guest' && !user.bot && !user.network && user.id !== im_v2_application_core.Core.getUserId() && !!user.lastActivityDate;
	}
	function _checkChatCallSupport2(dialogId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['dialogues/get'](dialogId);
	  if (!dialog) {
	    return false;
	  }
	  const {
	    userCounter
	  } = dialog;
	  return userCounter > 1 && userCounter <= BX.Call.Util.getUserLimit();
	}
	function _pushServerIsActive2() {
	  return true;
	}
	function _getCurrentDialogId2() {
	  const layout = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['application/getLayout'];
	  if (layout.name !== im_v2_const.Layout.chat.name) {
	    return '';
	  }
	  return layout.entityId;
	}
	CallManager.viewContainerClass = 'bx-im-messenger__call_container';

	exports.CallManager = CallManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Vue3.Vuex,BX.Call,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=call.bundle.js.map
