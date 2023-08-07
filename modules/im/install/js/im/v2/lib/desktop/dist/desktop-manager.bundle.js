/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_logger,timeman_monitor,im_public,im_v2_lib_rest,im_v2_application_core,im_v2_const,main_core_events,main_core,im_v2_lib_utils,im_v2_lib_desktopApi) {
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

	var _subscribeToBxProtocolEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToBxProtocolEvent");
	class BxLinkHandler {
	  static init() {
	    return new BxLinkHandler();
	  }
	  constructor() {
	    Object.defineProperty(this, _subscribeToBxProtocolEvent, {
	      value: _subscribeToBxProtocolEvent2
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToBxProtocolEvent)[_subscribeToBxProtocolEvent]();
	  }
	}
	function _subscribeToBxProtocolEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onBxLink, (command, rawParams) => {
	    const params = rawParams != null ? rawParams : {};
	    Object.entries(params).forEach(([key, value]) => {
	      params[key] = decodeURIComponent(value);
	    });
	    im_v2_lib_desktopApi.DesktopApi.showWindow();
	    if (command === im_v2_const.DesktopBxLink.chat) {
	      im_public.Messenger.openChat(params.dialogId);
	    } else if (command === im_v2_const.DesktopBxLink.call) {
	      im_public.Messenger.startVideoCall(params.dialogId);
	    } else if (command === im_v2_const.DesktopBxLink.notifications) {
	      im_public.Messenger.openNotifications();
	    } else if (command === im_v2_const.DesktopBxLink.recentSearch) {
	      im_public.Messenger.openRecentSearch();
	    } else if (command === im_v2_const.DesktopBxLink.timeManager) {
	      im_v2_lib_desktopApi.DesktopApi.showWindow();
	      timeman_monitor.Monitor == null ? void 0 : timeman_monitor.Monitor.openReport();
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
	    im_v2_lib_desktopApi.DesktopApi.exit();
	  });
	}

	var _initDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initDate");
	var _subscribeToWakeUpEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToWakeUpEvent");
	var _onWakeUp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWakeUp");
	var _subscribeToAwayEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToAwayEvent");
	var _onUserAway = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onUserAway");
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
	    Object.defineProperty(this, _onUserAway, {
	      value: _onUserAway2
	    });
	    Object.defineProperty(this, _subscribeToAwayEvent, {
	      value: _subscribeToAwayEvent2
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
	    babelHelpers.classPrivateFieldLooseBase(this, _setInitialStatus)[_setInitialStatus]();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToStatusChange)[_subscribeToStatusChange]();
	  }

	  // wake up

	  // user status
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
	function _subscribeToAwayEvent2() {
	  im_v2_lib_desktopApi.DesktopApi.subscribe(im_v2_const.EventType.desktop.onUserAway, babelHelpers.classPrivateFieldLooseBase(this, _onUserAway)[_onUserAway].bind(this));
	}
	function _onUserAway2(away) {
	  const method = away ? im_v2_const.RestMethod.imUserStatusIdleStart : im_v2_const.RestMethod.imUserStatusIdleEnd;
	  im_v2_application_core.Core.getRestClient().callMethod(method).catch(error => {
	    console.error(`Desktop: error in ${method}  - ${error}`);
	  });
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
	  const chatCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['recent/getTotalCounter'];
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

	var _sendInitEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendInitEvent");
	class Desktop {
	  static init() {
	    return new Desktop();
	  }
	  constructor() {
	    Object.defineProperty(this, _sendInitEvent, {
	      value: _sendInitEvent2
	    });
	    StatusHandler.init();
	    AuthHandler.init();
	    BxLinkHandler.init();
	    CounterHandler.init();
	    HotkeyHandler.init();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendInitEvent)[_sendInitEvent]();
	  }
	}
	function _sendInitEvent2() {
	  const {
	    currentUser
	  } = im_v2_application_core.Core.getApplicationData();
	  im_v2_lib_desktopApi.DesktopApi.emit(im_v2_const.EventType.desktop.onInit, [{
	    userInfo: currentUser != null ? currentUser : {}
	  }]);
	}

	const DESKTOP_PROTOCOL_VERSION = 2;
	const LOCATION_RESET_TIMEOUT = 1000;
	var _desktopIsActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopIsActive");
	var _locationChangedToBx = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("locationChangedToBx");
	var _goToBx = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("goToBx");
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
	    Object.defineProperty(this, _goToBx, {
	      value: _goToBx2
	    });
	    Object.defineProperty(this, _desktopIsActive, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _locationChangedToBx, {
	      writable: true,
	      value: false
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
	  openChat(dialogId = '') {
	    im_v2_lib_logger.Logger.warn('Desktop: openChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _goToBx)[_goToBx](`bx://${im_v2_const.DesktopBxLink.chat}/dialogId/${dialogId}`);
	    return Promise.resolve();
	  }
	  openNotifications() {
	    im_v2_lib_logger.Logger.warn('Desktop: openNotifications');
	    babelHelpers.classPrivateFieldLooseBase(this, _goToBx)[_goToBx](`bx://${im_v2_const.DesktopBxLink.notifications}`);
	    return Promise.resolve();
	  }
	  openRecentSearch() {
	    im_v2_lib_logger.Logger.warn('Desktop: openRecentSearch');
	    babelHelpers.classPrivateFieldLooseBase(this, _goToBx)[_goToBx](`bx://${im_v2_const.DesktopBxLink.recentSearch}`);
	    return Promise.resolve();
	  }
	  startVideoCall(dialogId = '', withVideo = true) {
	    im_v2_lib_logger.Logger.warn('Desktop: startVideoCall', dialogId, withVideo);
	    babelHelpers.classPrivateFieldLooseBase(this, _goToBx)[_goToBx](`bx://${im_v2_const.DesktopBxLink.call}/dialogId/${dialogId}`);
	    return Promise.resolve();
	  }
	  checkStatusInDifferentContext() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _desktopIsActive)[_desktopIsActive]) {
	      return Promise.resolve(false);
	    }
	    if (DesktopManager.isDesktop() && im_v2_lib_desktopApi.DesktopApi.isChatWindow()) {
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
	}
	function _goToBx2(rawUrl) {
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

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Timeman,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Event,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=desktop-manager.bundle.js.map
