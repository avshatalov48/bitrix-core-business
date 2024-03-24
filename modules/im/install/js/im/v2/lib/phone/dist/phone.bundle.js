/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,voximplant,voximplant_phoneCalls,main_core,main_core_events,im_v2_application_core,im_v2_lib_logger,im_v2_lib_desktopApi,im_v2_lib_call,im_v2_lib_soundNotification) {
	'use strict';

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _controller = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("controller");
	var _settings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _getController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getController");
	var _onCallCreated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCallCreated");
	var _onCallDestroyed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCallDestroyed");
	var _onDeviceCallStarted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDeviceCallStarted");
	var _onCallConnected = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCallConnected");
	var _getCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentUser");
	var _getUserAvatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserAvatar");
	var _parseStartCallParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parseStartCallParams");
	var _showCallLimitSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showCallLimitSlider");
	class PhoneManager {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  static init() {
	    PhoneManager.getInstance();
	  }
	  constructor() {
	    Object.defineProperty(this, _showCallLimitSlider, {
	      value: _showCallLimitSlider2
	    });
	    Object.defineProperty(this, _parseStartCallParams, {
	      value: _parseStartCallParams2
	    });
	    Object.defineProperty(this, _getUserAvatar, {
	      value: _getUserAvatar2
	    });
	    Object.defineProperty(this, _getCurrentUser, {
	      value: _getCurrentUser2
	    });
	    Object.defineProperty(this, _onCallConnected, {
	      value: _onCallConnected2
	    });
	    Object.defineProperty(this, _onDeviceCallStarted, {
	      value: _onDeviceCallStarted2
	    });
	    Object.defineProperty(this, _onCallDestroyed, {
	      value: _onCallDestroyed2
	    });
	    Object.defineProperty(this, _onCallCreated, {
	      value: _onCallCreated2
	    });
	    Object.defineProperty(this, _getController, {
	      value: _getController2
	    });
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _controller, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      phoneSettings: _phoneSettings
	    } = im_v2_application_core.Core.getApplicationData();
	    im_v2_lib_logger.Logger.warn('PhoneManager: phoneSettings', _phoneSettings);
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init](_phoneSettings);
	  }
	  canCall() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].phoneEnabled && babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].canPerformCallsByUser;
	  }
	  openKeyPad(params) {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]) == null ? void 0 : _babelHelpers$classPr.openKeyPad(params);
	  }
	  closeKeyPad() {
	    var _babelHelpers$classPr2;
	    (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]) == null ? void 0 : _babelHelpers$classPr2.closeKeyPad();
	  }
	  async startCall(number, rawParams = {}) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].canPerformCallsByLimits) {
	      void babelHelpers.classPrivateFieldLooseBase(this, _showCallLimitSlider)[_showCallLimitSlider]();
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]) {
	      return;
	    }
	    let params = rawParams;
	    if (main_core.Type.isStringFilled(params)) {
	      params = babelHelpers.classPrivateFieldLooseBase(this, _parseStartCallParams)[_parseStartCallParams](params);
	    }

	    // await this.#controller.loadPhoneLines();
	    //
	    // const lineId = params.LINE_ID ?? this.#controller.defaultLineId;
	    // if (this.#controller.isRestLine(lineId))
	    // {
	    // 	this.#controller.startCallViaRestApp(number, lineId, params);
	    // }

	    this.closeKeyPad();
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].phoneCall(number, params);
	  }
	  startCallList(rawCallListId, params) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]) {
	      return;
	    }
	    const callListId = Number.parseInt(rawCallListId, 10);
	    if (callListId === 0 || Number.isNaN(callListId)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].startCallList(callListId, params);
	  }
	  toggleDebugFlag(debug) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].debug = debug;
	  }
	}
	function _init2(phoneSettings) {
	  babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = phoneSettings;
	  if (!main_core.Reflection.getClass('BX.Voximplant.PhoneCallsController')) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller] = babelHelpers.classPrivateFieldLooseBase(this, _getController)[_getController](phoneSettings);
	}
	function _getController2(phoneSettings) {
	  const soundManager = im_v2_lib_soundNotification.SoundNotificationManager.getInstance();
	  return new voximplant_phoneCalls.PhoneCallsController({
	    phoneEnabled: phoneSettings.phoneEnabled,
	    userId: im_v2_application_core.Core.getUserId(),
	    isAdmin: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().isAdmin,
	    restApps: phoneSettings.restApps,
	    canInterceptCall: phoneSettings.canInterceptCall,
	    deviceActive: phoneSettings.deviceActive,
	    defaultLineId: phoneSettings.defaultLineId,
	    availableLines: phoneSettings.availableLines,
	    messengerFacade: {
	      isThemeDark: () => false,
	      isDesktop: () => im_v2_lib_desktopApi.DesktopApi.isDesktop(),
	      hasActiveCall: () => im_v2_lib_call.CallManager.getInstance().hasCurrentCall(),
	      repeatSound: (melodyName, time, force) => soundManager.playLoop(melodyName, time, force),
	      stopRepeatSound: melodyName => soundManager.stop(melodyName),
	      playSound: (melodyName, force) => {
	        if (force) {
	          soundManager.forcePlayOnce(melodyName);
	          return;
	        }
	        soundManager.playOnce(melodyName);
	      },
	      setLocalConfig: () => {},
	      getLocalConfig: () => {},
	      getAvatar: userId => babelHelpers.classPrivateFieldLooseBase(this, _getUserAvatar)[_getUserAvatar](userId)
	    },
	    events: {
	      [voximplant_phoneCalls.PhoneCallsController.Events.onCallCreated]: () => babelHelpers.classPrivateFieldLooseBase(this, _onCallCreated)[_onCallCreated](),
	      [voximplant_phoneCalls.PhoneCallsController.Events.onCallConnected]: event => babelHelpers.classPrivateFieldLooseBase(this, _onCallConnected)[_onCallConnected](event),
	      [voximplant_phoneCalls.PhoneCallsController.Events.onCallDestroyed]: () => babelHelpers.classPrivateFieldLooseBase(this, _onCallDestroyed)[_onCallDestroyed](),
	      [voximplant_phoneCalls.PhoneCallsController.Events.onDeviceCallStarted]: () => babelHelpers.classPrivateFieldLooseBase(this, _onDeviceCallStarted)[_onDeviceCallStarted]()
	    }
	  });
	}
	function _onCallCreated2() {
	  if (!im_v2_lib_desktopApi.DesktopApi.isDesktop()) {
	    return;
	  }
	  im_v2_lib_desktopApi.DesktopApi.stopDiskSync();
	}
	function _onCallDestroyed2() {
	  if (!im_v2_lib_desktopApi.DesktopApi.isDesktop()) {
	    return;
	  }
	  im_v2_lib_desktopApi.DesktopApi.startDiskSync();
	}
	function _onDeviceCallStarted2() {
	  var _DesktopApi$findWindo;
	  if (!im_v2_lib_desktopApi.DesktopApi.isDesktop()) {
	    return;
	  }
	  const target = (_DesktopApi$findWindo = im_v2_lib_desktopApi.DesktopApi.findWindow('callWindow')) != null ? _DesktopApi$findWindo : window;
	  im_v2_lib_desktopApi.DesktopApi.activateWindow(target);
	  // close desktop topmost window?
	}
	function _onCallConnected2(event) {
	  var _DesktopApi$findWindo2;
	  const {
	    isIncoming,
	    isDeviceCall
	  } = event.getData();
	  if (!im_v2_lib_desktopApi.DesktopApi.isDesktop() || isIncoming || isDeviceCall) {
	    return;
	  }
	  const target = (_DesktopApi$findWindo2 = im_v2_lib_desktopApi.DesktopApi.findWindow('callWindow')) != null ? _DesktopApi$findWindo2 : window;
	  im_v2_lib_desktopApi.DesktopApi.activateWindow(target);
	}
	function _getCurrentUser2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  return im_v2_application_core.Core.getStore().getters['users/get'](userId);
	}
	function _getUserAvatar2(userId) {
	  const user = im_v2_application_core.Core.getStore().getters['users/get'](userId, true);
	  return user.avatar;
	}
	function _parseStartCallParams2(jsonParams) {
	  let params = jsonParams;
	  try {
	    params = JSON.parse(params);
	  } catch {
	    params = {};
	  }
	  return params;
	}
	async function _showCallLimitSlider2() {
	  const SLIDER_EXTENSION = 'voximplant.common';
	  await main_core.Runtime.loadExtension(SLIDER_EXTENSION);
	  BX.Voximplant.openLimitSlider();
	}
	Object.defineProperty(PhoneManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.PhoneManager = PhoneManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Voximplant,BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=phone.bundle.js.map
