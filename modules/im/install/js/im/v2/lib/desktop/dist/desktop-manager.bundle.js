/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_public,main_core_events,main_core,im_v2_lib_utils,im_v2_lib_desktopApi,im_v2_application_core,im_v2_lib_logger,im_v2_lib_rest,im_v2_const) {
	'use strict';

	const IMAGE_CHECK_URL = 'http://127.0.0.1:20141';
	const IMAGE_CHECK_TIMEOUT = 500;
	const IMAGE_CLASS = 'bx-im-messenger__out-of-view';
	const INTERNET_CHECK_URL = '//www.bitrixsoft.com/200.ok';
	const checkTimeoutList = {};
	const Checker = {
	  testImageUpload(successCallback, failureCallback) {
	    const dateCheck = Date.now();
	    let failureCallbackCalled = false;
	    const imageForCheck = main_core.Dom.create({
	      tag: 'img',
	      attrs: {
	        src: `${IMAGE_CHECK_URL}/icon.png?${dateCheck}`,
	        'data-id': dateCheck
	      },
	      props: {
	        className: IMAGE_CLASS
	      },
	      events: {
	        error: function () {
	          if (failureCallbackCalled) {
	            return;
	          }
	          const checkId = this.dataset.id;
	          failureCallback(false, checkId);
	          clearTimeout(checkTimeoutList[checkId]);
	          main_core.Dom.remove(this);
	        },
	        load: function () {
	          const checkId = this.dataset.id;
	          successCallback(true, checkId);
	          clearTimeout(checkTimeoutList[checkId]);
	          main_core.Dom.remove(this);
	        }
	      }
	    });
	    document.body.append(imageForCheck);
	    checkTimeoutList[dateCheck] = setTimeout(() => {
	      failureCallbackCalled = true;
	      failureCallback(false, dateCheck);
	      main_core.Dom.remove(imageForCheck);
	    }, IMAGE_CHECK_TIMEOUT);
	  },
	  testInternetConnection() {
	    const currentTimestamp = Date.now();
	    return new Promise(resolve => {
	      fetch(`${INTERNET_CHECK_URL}.${currentTimestamp}`).then(response => {
	        if (response.status === 200) {
	          return resolve(true);
	        }
	        resolve(false);
	      }).catch(() => {
	        resolve(false);
	      });
	    });
	  }
	};

	let conferenceList = [];
	let conferenceIndex = 0;
	const Conference = {
	  openConference(code) {
	    if (!im_v2_lib_utils.Utils.conference.isValidCode(code)) {
	      return false;
	    }
	    if (!im_v2_lib_desktopApi.DesktopApi.isDesktop()) {
	      return false;
	    }
	    let windowSize = null;
	    const sizes = [{
	      width: 2560,
	      height: 1440
	    }, {
	      width: 2048,
	      height: 1152
	    }, {
	      width: 1920,
	      height: 1080
	    }, {
	      width: 1600,
	      height: 900
	    }, {
	      width: 1366,
	      height: 768
	    }, {
	      width: 1024,
	      height: 576
	    }];
	    for (const size of sizes) {
	      windowSize = size;
	      if (screen.width > size.width && screen.height > size.height) {
	        break;
	      }
	    }
	    conferenceList = conferenceList.filter(name => {
	      return Boolean(im_v2_lib_desktopApi.DesktopApi.findWindow(name));
	    });
	    conferenceList.push(im_v2_lib_utils.Utils.conference.getWindowNameByCode(code));
	    im_v2_lib_desktopApi.DesktopApi.createWindow(im_v2_lib_utils.Utils.conference.getWindowNameByCode(code), controller => {
	      controller.SetProperty('title', main_core.Loc.getMessage('IM_LIB_DESKTOP_CONFERENCE_TITLE'));
	      controller.SetProperty('clientSize', {
	        Width: windowSize.width,
	        Height: windowSize.height
	      });
	      controller.SetProperty('minClientSize', {
	        Width: 940,
	        Height: 400
	      });
	      controller.SetProperty('backgroundColor', '#2B3038');
	      controller.ExecuteCommand('html.load', `<script>location.href="/desktop_app/router.php?alias=${code}&videoconf";</script>`);
	      controller.ExecuteCommand('show');
	    });
	    return true;
	  },
	  toggleConference() {
	    if (conferenceIndex > conferenceList.length - 1) {
	      conferenceIndex = 0;

	      // TODO temporary workaround for life with two cores of IM
	      const CallManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.CallManager');
	      if (CallManager && CallManager.getInstance().hasCurrentCall()) {
	        CallManager.getInstance().foldCurrentCall();
	      }
	      im_v2_lib_desktopApi.DesktopApi.showWindow();
	      return true;
	    }
	    conferenceList = conferenceList.filter(name => {
	      return Boolean(im_v2_lib_desktopApi.DesktopApi.findWindow(name));
	    });
	    for (let index = conferenceIndex; index < conferenceList.length; index++) {
	      conferenceIndex++;
	      const target = im_v2_lib_desktopApi.DesktopApi.findWindow(conferenceList[index]);
	      if (target) {
	        im_v2_lib_desktopApi.DesktopApi.activateWindow(target);
	        break;
	      }
	    }
	    return true;
	  }
	};

	const ENCODE_SEPARATOR = '!!';
	const Encoder = {
	  encodeParams(params) {
	    if (!main_core.Type.isPlainObject(params)) {
	      return '';
	    }
	    let result = '';
	    Object.entries(params).forEach(([key, value]) => {
	      const prefix = '';
	      result += `${prefix}${key}${ENCODE_SEPARATOR}${value}`;
	    });
	    return result;
	  },
	  decodeParams(encodedParams) {
	    const result = {};
	    if (!main_core.Type.isStringFilled(encodedParams)) {
	      return result;
	    }
	    const chunks = encodedParams.split(ENCODE_SEPARATOR);
	    for (let i = 0; i < chunks.length; i += 2) {
	      const key = chunks[i];
	      const value = chunks[i + 1];
	      result[key] = value;
	    }
	    return result;
	  },
	  encodeParamsJson(params) {
	    if (!main_core.Type.isPlainObject(params)) {
	      return '{}';
	    }
	    let result = '';
	    try {
	      result = encodeURIComponent(JSON.stringify(params));
	    } catch (error) {
	      console.error('DesktopUtils: could not encode params.', error);
	      result = '{}';
	    }
	    return result;
	  },
	  decodeParamsJson(encodedParams) {
	    let result = {};
	    if (!main_core.Type.isStringFilled(encodedParams)) {
	      return result;
	    }
	    try {
	      result = JSON.parse(decodeURIComponent(encodedParams));
	    } catch (error) {
	      console.error('DesktopUtils: could not decode encoded params.', error);
	    }
	    return result;
	  }
	};

	/* eslint-disable unicorn/prefer-switch */
	var _subscribeToBxProtocolEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToBxProtocolEvent");
	var _subscribeToLegacyBxProtocolEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToLegacyBxProtocolEvent");
	class BxLinkHandler {
	  static init() {
	    return new BxLinkHandler();
	  }
	  constructor() {
	    Object.defineProperty(this, _subscribeToLegacyBxProtocolEvent, {
	      value: _subscribeToLegacyBxProtocolEvent2
	    });
	    Object.defineProperty(this, _subscribeToBxProtocolEvent, {
	      value: _subscribeToBxProtocolEvent2
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToBxProtocolEvent)[_subscribeToBxProtocolEvent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToLegacyBxProtocolEvent)[_subscribeToLegacyBxProtocolEvent]();
	  }
	}
	function _subscribeToBxProtocolEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onBxLink, (command, rawParams) => {
	    const params = rawParams != null ? rawParams : {};
	    Object.entries(params).forEach(([key, value]) => {
	      params[key] = decodeURIComponent(value);
	    });
	    im_v2_lib_desktopApi.DesktopApi.activateWindow();
	    if (command === im_v2_const.DesktopBxLink.chat) {
	      void im_public.Messenger.openChat(params.dialogId);
	    } else if (command === im_v2_const.DesktopBxLink.lines) {
	      void im_public.Messenger.openLines(params.dialogId);
	    } else if (command === im_v2_const.DesktopBxLink.conference) {
	      void DesktopManager.getInstance().openConference(params.code);
	    } else if (command === im_v2_const.DesktopBxLink.call) {
	      const withVideo = params.withVideo !== 'N';
	      void im_public.Messenger.startVideoCall(params.dialogId, withVideo);
	    } else if (command === im_v2_const.DesktopBxLink.phone) {
	      const decodedParams = Encoder.decodeParamsJson(params.phoneParams);
	      void im_public.Messenger.startPhoneCall(params.number, decodedParams);
	    } else if (command === im_v2_const.DesktopBxLink.callList) {
	      const decodedParams = Encoder.decodeParamsJson(params.callListParams);
	      void im_public.Messenger.startCallList(params.callListId, decodedParams);
	    } else if (command === im_v2_const.DesktopBxLink.notifications) {
	      void im_public.Messenger.openNotifications();
	    } else if (command === im_v2_const.DesktopBxLink.recentSearch) {
	      void im_public.Messenger.openRecentSearch();
	    } else if (command === im_v2_const.DesktopBxLink.timeManager) {
	      var _BX$Timeman, _BX$Timeman$Monitor;
	      (_BX$Timeman = BX.Timeman) == null ? void 0 : (_BX$Timeman$Monitor = _BX$Timeman.Monitor) == null ? void 0 : _BX$Timeman$Monitor.openReport();
	    }
	  });
	}
	function _subscribeToLegacyBxProtocolEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onBxLink, (command, rawParams) => {
	    const params = rawParams != null ? rawParams : {};
	    Object.entries(params).forEach(([key, value]) => {
	      params[key] = decodeURIComponent(value);
	    });
	    im_v2_lib_desktopApi.DesktopApi.activateWindow();
	    if (command === im_v2_const.LegacyDesktopBxLink.messenger) {
	      if (params.dialog) {
	        void im_public.Messenger.openChat(params.dialog);
	      } else if (params.chat) {
	        void im_public.Messenger.openChat(`chat${params.chat}`);
	      } else {
	        void im_public.Messenger.openChat();
	      }
	    } else if (command === im_v2_const.LegacyDesktopBxLink.chat && params.id) {
	      void im_public.Messenger.openChat(`chat${params.id}`);
	    } else if (command === im_v2_const.LegacyDesktopBxLink.notify) {
	      void im_public.Messenger.openNotifications();
	    } else if (command === im_v2_const.LegacyDesktopBxLink.callTo) {
	      if (params.video) {
	        void im_public.Messenger.startVideoCall(params.video);
	      } else if (params.audio) {
	        void im_public.Messenger.startVideoCall(params.audio, false);
	      } else if (params.phone) {
	        void im_public.Messenger.startPhoneCall(params.phone);
	      }
	    } else if (command === im_v2_const.LegacyDesktopBxLink.callList) {
	      void im_public.Messenger.openRecentSearch();
	    }
	  });
	}

	var _subscribeToLogoutEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToLogoutEvent");
	var _onExit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onExit");
	class AuthHandler {
	  static init() {
	    return new AuthHandler();
	  }
	  constructor() {
	    Object.defineProperty(this, _onExit, {
	      value: _onExit2
	    });
	    Object.defineProperty(this, _subscribeToLogoutEvent, {
	      value: _subscribeToLogoutEvent2
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToLogoutEvent)[_subscribeToLogoutEvent]();
	  }
	}
	function _subscribeToLogoutEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onExit, babelHelpers.classPrivateFieldLooseBase(this, _onExit)[_onExit].bind(this));
	}
	function _onExit2() {
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2DesktopLogout).finally(() => {
	    im_v2_lib_desktopApi.DesktopApi.shutdown();
	  });
	}

	var _initDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initDate");
	var _subscribeToWakeUpEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToWakeUpEvent");
	var _onWakeUp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWakeUp");
	var _subscribeToIconClickEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToIconClickEvent");
	var _onIconClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onIconClick");
	var _subscribeToAwayEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToAwayEvent");
	var _onUserAway = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onUserAway");
	var _subscribeToFocusEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToFocusEvent");
	var _subscribeToBlurEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToBlurEvent");
	var _removeNativeNotifications = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeNativeNotifications");
	var _setInitialStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setInitialStatus");
	var _subscribeToStatusChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToStatusChange");
	class StatusHandler {
	  static init() {
	    return new StatusHandler();
	  }
	  constructor() {
	    Object.defineProperty(this, _subscribeToStatusChange, {
	      value: _subscribeToStatusChange2
	    });
	    Object.defineProperty(this, _setInitialStatus, {
	      value: _setInitialStatus2
	    });
	    Object.defineProperty(this, _removeNativeNotifications, {
	      value: _removeNativeNotifications2
	    });
	    Object.defineProperty(this, _subscribeToBlurEvent, {
	      value: _subscribeToBlurEvent2
	    });
	    Object.defineProperty(this, _subscribeToFocusEvent, {
	      value: _subscribeToFocusEvent2
	    });
	    Object.defineProperty(this, _onUserAway, {
	      value: _onUserAway2
	    });
	    Object.defineProperty(this, _subscribeToAwayEvent, {
	      value: _subscribeToAwayEvent2
	    });
	    Object.defineProperty(this, _onIconClick, {
	      value: _onIconClick2
	    });
	    Object.defineProperty(this, _subscribeToIconClickEvent, {
	      value: _subscribeToIconClickEvent2
	    });
	    Object.defineProperty(this, _onWakeUp, {
	      value: _onWakeUp2
	    });
	    Object.defineProperty(this, _subscribeToWakeUpEvent, {
	      value: _subscribeToWakeUpEvent2
	    });
	    Object.defineProperty(this, _initDate, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initDate)[_initDate] = new Date();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToWakeUpEvent)[_subscribeToWakeUpEvent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToAwayEvent)[_subscribeToAwayEvent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToFocusEvent)[_subscribeToFocusEvent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToBlurEvent)[_subscribeToBlurEvent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToIconClickEvent)[_subscribeToIconClickEvent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _setInitialStatus)[_setInitialStatus]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToStatusChange)[_subscribeToStatusChange]();
	  }

	  // region wake up

	  // endregion user status
	}
	function _subscribeToWakeUpEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onWakeUp, babelHelpers.classPrivateFieldLooseBase(this, _onWakeUp)[_onWakeUp].bind(this));
	}
	async function _onWakeUp2() {
	  const hasConnection = await Checker.testInternetConnection();
	  if (!hasConnection) {
	    console.error('NO INTERNET CONNECTION!');
	    return;
	  }
	  if (im_v2_lib_utils.Utils.date.isSameDay(new Date(), babelHelpers.classPrivateFieldLooseBase(this, _initDate)[_initDate])) {
	    im_v2_application_core.Core.getPullClient().restart();
	  } else {
	    im_v2_lib_desktopApi.DesktopApi.reloadWindow();
	  }
	}
	function _subscribeToIconClickEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onIconClick, babelHelpers.classPrivateFieldLooseBase(this, _onIconClick)[_onIconClick].bind(this));
	}
	function _onIconClick2() {
	  DesktopManager.getInstance().toggleConference();
	}
	function _subscribeToAwayEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onUserAway, babelHelpers.classPrivateFieldLooseBase(this, _onUserAway)[_onUserAway].bind(this));
	}
	function _onUserAway2(away) {
	  const method = away ? im_v2_const.RestMethod.imUserStatusIdleStart : im_v2_const.RestMethod.imUserStatusIdleEnd;
	  im_v2_application_core.Core.getRestClient().callMethod(method).catch(error => {
	    console.error(`Desktop: error in ${method}  - ${error}`);
	  });
	}
	function _subscribeToFocusEvent2() {
	  main_core.Event.bind(window, 'focus', babelHelpers.classPrivateFieldLooseBase(this, _removeNativeNotifications)[_removeNativeNotifications].bind(this));
	}
	function _subscribeToBlurEvent2() {
	  // TODO remove this after refactor notification balloons
	  main_core.Event.bind(window, 'blur', babelHelpers.classPrivateFieldLooseBase(this, _removeNativeNotifications)[_removeNativeNotifications].bind(this));
	}
	function _removeNativeNotifications2() {
	  if (!main_core.Browser.isWin() || !im_v2_lib_desktopApi.DesktopApi.isChatWindow()) {
	    return;
	  }
	  im_v2_lib_desktopApi.DesktopApi.removeNativeNotifications();
	}
	function _setInitialStatus2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  const user = im_v2_application_core.Core.getStore().getters['users/get'](userId);
	  if (!user) {
	    return;
	  }
	  im_v2_lib_desktopApi.DesktopApi.setIconStatus(user.status);
	}
	function _subscribeToStatusChange2() {
	  const statusWatcher = (state, getters) => {
	    const userId = im_v2_application_core.Core.getUserId();
	    const user = getters['users/get'](userId);
	    return user == null ? void 0 : user.status;
	  };
	  im_v2_application_core.Core.getStore().watch(statusWatcher, newStatus => {
	    im_v2_lib_desktopApi.DesktopApi.setIconStatus(newStatus);
	  });
	}

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _subscribeToCountersChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToCountersChange");
	var _onCounterChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCounterChange");
	class CounterHandler {
	  static init() {
	    return new CounterHandler();
	  }
	  constructor() {
	    Object.defineProperty(this, _onCounterChange, {
	      value: _onCounterChange2
	    });
	    Object.defineProperty(this, _subscribeToCountersChange, {
	      value: _subscribeToCountersChange2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToCountersChange)[_subscribeToCountersChange]();
	  }
	}
	function _subscribeToCountersChange2() {
	  main_core_events.EventEmitter.subscribe(im_v2_const.EventType.counter.onNotificationCounterChange, babelHelpers.classPrivateFieldLooseBase(this, _onCounterChange)[_onCounterChange].bind(this));
	  main_core_events.EventEmitter.subscribe(im_v2_const.EventType.counter.onChatCounterChange, babelHelpers.classPrivateFieldLooseBase(this, _onCounterChange)[_onCounterChange].bind(this));
	}
	function _onCounterChange2() {
	  const chatCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['recent/getTotalChatCounter'];
	  const notificationCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['notifications/getCounter'];
	  const isImportant = chatCounter > 0;
	  im_v2_lib_desktopApi.DesktopApi.setCounter(chatCounter + notificationCounter, isImportant);
	}

	var _bindHotkeys = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindHotkeys");
	class HotkeyHandler {
	  static init() {
	    return new HotkeyHandler();
	  }
	  constructor() {
	    Object.defineProperty(this, _bindHotkeys, {
	      value: _bindHotkeys2
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _bindHotkeys)[_bindHotkeys]();
	  }
	}
	function _bindHotkeys2() {
	  main_core.Event.bind(window, 'keydown', event => {
	    const reloadCombination = im_v2_lib_utils.Utils.key.isCombination(event, 'Ctrl+R');
	    if (reloadCombination) {
	      im_v2_lib_desktopApi.DesktopApi.reloadWindow();
	    }
	  });
	}

	var _sessionTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sessionTime");
	var _startUpdateInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startUpdateInterval");
	var _requestUpdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestUpdate");
	class UpdateStateManager {
	  static init() {
	    return new UpdateStateManager();
	  }
	  constructor() {
	    Object.defineProperty(this, _requestUpdate, {
	      value: _requestUpdate2
	    });
	    Object.defineProperty(this, _startUpdateInterval, {
	      value: _startUpdateInterval2
	    });
	    Object.defineProperty(this, _sessionTime, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      sessionTime
	    } = im_v2_application_core.Core.getApplicationData();
	    babelHelpers.classPrivateFieldLooseBase(this, _sessionTime)[_sessionTime] = sessionTime * 1000;
	    babelHelpers.classPrivateFieldLooseBase(this, _startUpdateInterval)[_startUpdateInterval]();
	  }
	}
	function _startUpdateInterval2() {
	  setInterval(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _requestUpdate)[_requestUpdate]();
	  }, babelHelpers.classPrivateFieldLooseBase(this, _sessionTime)[_sessionTime]);
	}
	function _requestUpdate2() {
	  im_v2_lib_logger.Logger.warn('Desktop: updateStateManager: requesting update');
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2UpdateState).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('Desktop: updateStateManager: error updating state', error);
	  });
	}

	var _minWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("minWidth");
	var _minHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("minHeight");
	var _setDefaults = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDefaults");
	var _sendInitEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendInitEvent");
	var _initSliderBindings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initSliderBindings");
	var _getSliderBindingsStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSliderBindingsStatus");
	var _initComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initComplete");
	class Desktop {
	  static init() {
	    return new Desktop();
	  }
	  constructor() {
	    Object.defineProperty(this, _initComplete, {
	      value: _initComplete2
	    });
	    Object.defineProperty(this, _getSliderBindingsStatus, {
	      value: _getSliderBindingsStatus2
	    });
	    Object.defineProperty(this, _initSliderBindings, {
	      value: _initSliderBindings2
	    });
	    Object.defineProperty(this, _sendInitEvent, {
	      value: _sendInitEvent2
	    });
	    Object.defineProperty(this, _setDefaults, {
	      value: _setDefaults2
	    });
	    Object.defineProperty(this, _minWidth, {
	      writable: true,
	      value: 1280
	    });
	    Object.defineProperty(this, _minHeight, {
	      writable: true,
	      value: 720
	    });
	    StatusHandler.init();
	    AuthHandler.init();
	    BxLinkHandler.init();
	    CounterHandler.init();
	    HotkeyHandler.init();
	    UpdateStateManager.init();
	    babelHelpers.classPrivateFieldLooseBase(this, _setDefaults)[_setDefaults]();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendInitEvent)[_sendInitEvent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initSliderBindings)[_initSliderBindings]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initComplete)[_initComplete]();
	  }
	}
	function _setDefaults2() {
	  // DesktopApi.setMinimumWindowSize(this.#minWidth, this.#minHeight);
	}
	function _sendInitEvent2() {
	  const {
	    currentUser
	  } = im_v2_application_core.Core.getApplicationData();
	  im_v2_lib_desktopApi.DesktopApi.emit(im_v2_const.EventType.desktop.onInit, [{
	    userInfo: currentUser != null ? currentUser : {}
	  }]);
	}
	function _initSliderBindings2() {
	  const sliderBindingStatus = babelHelpers.classPrivateFieldLooseBase(this, _getSliderBindingsStatus)[_getSliderBindingsStatus]();
	  if (sliderBindingStatus) {
	    BX.SidePanel.Instance.enableAnchorBinding();
	    return;
	  }
	  BX.SidePanel.Instance.disableAnchorBinding();
	}
	function _getSliderBindingsStatus2() {
	  const result = im_v2_lib_desktopApi.DesktopApi.getCustomSetting(im_v2_lib_desktopApi.DesktopSettingsKey.sliderBindingsStatus, '1');
	  return result === '1';
	}
	function _initComplete2() {
	  BXDesktopSystem.LogInfo = function (...params) {
	    im_v2_lib_logger.Logger.desktop(...params);
	  };
	  im_v2_lib_desktopApi.DesktopApi.printWelcomePrompt();
	  window.BX.debugEnable(true);
	}

	const DESKTOP_PROTOCOL_VERSION = 2;
	const LOCATION_RESET_TIMEOUT = 1000;
	var _desktopIsActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopIsActive");
	var _locationChangedToBx = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("locationChangedToBx");
	var _enableRedirectCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableRedirectCounter");
	var _prepareBxUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareBxUrl");
	var _initDesktopStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initDesktopStatus");
	class DesktopManager {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  static init() {
	    DesktopManager.getInstance();
	  }
	  static isDesktop() {
	    return im_v2_lib_desktopApi.DesktopApi.isDesktop();
	  }
	  static isChatWindow() {
	    return im_v2_lib_desktopApi.DesktopApi.isChatWindow();
	  }
	  constructor() {
	    Object.defineProperty(this, _initDesktopStatus, {
	      value: _initDesktopStatus2
	    });
	    Object.defineProperty(this, _prepareBxUrl, {
	      value: _prepareBxUrl2
	    });
	    Object.defineProperty(this, _desktopIsActive, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _locationChangedToBx, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _enableRedirectCounter, {
	      writable: true,
	      value: 1
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initDesktopStatus)[_initDesktopStatus]();
	    if (DesktopManager.isDesktop() && im_v2_lib_desktopApi.DesktopApi.isChatWindow()) {
	      Desktop.init();
	    }
	  }
	  isDesktopActive() {
	    if (DesktopManager.isDesktop()) {
	      return true;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _desktopIsActive)[_desktopIsActive];
	  }
	  setDesktopActive(flag) {
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopIsActive)[_desktopIsActive] = flag;
	  }
	  isLocationChangedToBx() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _locationChangedToBx)[_locationChangedToBx];
	  }
	  redirectToChat(dialogId = '') {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToChat', dialogId);
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.chat}/dialogId/${dialogId}`);
	    return Promise.resolve();
	  }
	  redirectToLines(dialogId = '') {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToLines', dialogId);
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.lines}/dialogId/${dialogId}`);
	    return Promise.resolve();
	  }
	  redirectToNotifications() {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToNotifications');
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.notifications}`);
	    return Promise.resolve();
	  }
	  redirectToRecentSearch() {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToRecentSearch');
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.recentSearch}`);
	    return Promise.resolve();
	  }
	  redirectToConference(code) {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToConference', code);
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.conference}/code/${code}`);
	    return Promise.resolve();
	  }
	  openConference(code) {
	    im_v2_lib_logger.Logger.warn('Desktop: openConference', code);
	    const result = Conference.openConference(code);
	    if (!result) {
	      return Promise.resolve(false);
	    }
	    return Promise.resolve(true);
	  }
	  toggleConference() {
	    im_v2_lib_logger.Logger.warn('Desktop: toggleConference');
	    Conference.toggleConference();
	  }
	  redirectToVideoCall(dialogId = '', withVideo = true) {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToVideoCall', dialogId, withVideo);
	    const withVideoParam = withVideo ? 'Y' : 'N';
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.call}/dialogId/${dialogId}/withVideo/${withVideoParam}`);
	    return Promise.resolve();
	  }
	  redirectToPhoneCall(number, params) {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToPhoneCall', number, params);
	    const encodedParams = Encoder.encodeParamsJson(params);
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.phone}/number/${number}/phoneParams/${encodedParams}`);
	    return Promise.resolve();
	  }
	  redirectToCallList(callListId, params) {
	    im_v2_lib_logger.Logger.warn('Desktop: redirectToCallList', callListId, params);
	    const encodedParams = Encoder.encodeParamsJson(params);
	    this.openBxLink(`bx://${im_v2_const.DesktopBxLink.callList}/callListId/${callListId}/callListParams/${encodedParams}`);
	    return Promise.resolve();
	  }
	  checkStatusInDifferentContext() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _desktopIsActive)[_desktopIsActive]) {
	      return Promise.resolve(false);
	    }
	    if (im_v2_lib_desktopApi.DesktopApi.isChatWindow()) {
	      return Promise.resolve(false);
	    }
	    return new Promise(resolve => {
	      Checker.testImageUpload(() => {
	        resolve(true);
	      }, () => {
	        resolve(false);
	      });
	    });
	  }
	  checkForRedirect() {
	    if (!this.isRedirectEnabled() || !this.isRedirectOptionEnabled()) {
	      return Promise.resolve(false);
	    }
	    return this.checkStatusInDifferentContext();
	  }
	  isRedirectEnabled() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _enableRedirectCounter)[_enableRedirectCounter] > 0;
	  }
	  enableRedirect() {
	    babelHelpers.classPrivateFieldLooseBase(this, _enableRedirectCounter)[_enableRedirectCounter]++;
	  }
	  disableRedirect() {
	    babelHelpers.classPrivateFieldLooseBase(this, _enableRedirectCounter)[_enableRedirectCounter]--;
	  }
	  isRedirectOptionEnabled() {
	    if (im_v2_lib_desktopApi.DesktopApi.isDesktop() && !im_v2_lib_desktopApi.DesktopApi.isChatWindow()) {
	      return true;
	    }
	    return im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.desktop.enableRedirect);
	  }
	  openBxLink(rawUrl) {
	    const preparedUrl = babelHelpers.classPrivateFieldLooseBase(this, _prepareBxUrl)[_prepareBxUrl](rawUrl);
	    babelHelpers.classPrivateFieldLooseBase(this, _locationChangedToBx)[_locationChangedToBx] = true;
	    setTimeout(() => {
	      const event = new main_core_events.BaseEvent({
	        compatData: []
	      });
	      main_core_events.EventEmitter.emit(window, 'BXLinkOpened', event);
	      babelHelpers.classPrivateFieldLooseBase(this, _locationChangedToBx)[_locationChangedToBx] = false;
	    }, LOCATION_RESET_TIMEOUT);
	    location.href = preparedUrl;
	  }
	}
	function _prepareBxUrl2(url) {
	  if (/^bx:\/\/v(\d)\//.test(url)) {
	    return url;
	  }
	  return url.replace('bx://', `bx://v${DESKTOP_PROTOCOL_VERSION}/${location.hostname}/`);
	}
	function _initDesktopStatus2() {
	  const settings = main_core.Extension.getSettings('im.v2.lib.desktop');
	  this.setDesktopActive(settings.get('desktopIsActive'));
	}

	exports.DesktopManager = DesktopManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Event,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=desktop-manager.bundle.js.map
