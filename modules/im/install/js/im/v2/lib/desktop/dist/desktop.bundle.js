this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_lib_logger,im_v2_const,main_core) {
	'use strict';

	const DesktopApi = {
	  isApiAvailable() {
	    return main_core.Type.isObject(window.BXDesktopSystem);
	  },
	  getApiVersion() {
	    if (!this.isApiAvailable()) {
	      return 0;
	    }
	    const [majorVersion, minorVersion, buildVersion, apiVersion] = window.BXDesktopSystem.GetProperty('versionParts');
	    return apiVersion;
	  },
	  isFeatureEnabled(code) {
	    var _window$BXDesktopSyst;
	    return !!((_window$BXDesktopSyst = window.BXDesktopSystem) != null && _window$BXDesktopSyst.FeatureEnabled(code));
	  }
	};

	const IMAGE_CHECK_URL = 'http://127.0.0.1:20141';
	const IMAGE_CHECK_TIMEOUT = 500;
	const IMAGE_CLASS = 'bx-im-messenger__out-of-view';
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
	  }
	};

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
	    return DesktopApi.isApiAvailable();
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
	    // init real desktop
	  }

	  isDesktopActive() {
	    if (DesktopApi.isApiAvailable()) {
	      return true;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _desktopIsActive)[_desktopIsActive];
	  }
	  setDesktopActive(flag) {
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopIsActive)[_desktopIsActive] = flag;
	  }
	  getDesktopVersion() {
	    return DesktopApi.getApiVersion();
	  }
	  isFeatureEnabled(code) {
	    return DesktopApi.isFeatureEnabled(code);
	  }
	  isLocationChangedToBx() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _locationChangedToBx)[_locationChangedToBx];
	  }
	  startVideoCall(dialogId = '', withVideo = true) {
	    im_v2_lib_logger.Logger.warn('Desktop: startVideoCall', dialogId, withVideo);
	    const callType = withVideo ? 'video' : 'audio';
	    // TODO: enum for commands
	    babelHelpers.classPrivateFieldLooseBase(this, _goToBx)[_goToBx](`bx://callto/${callType}/${dialogId}`);
	    return Promise.resolve();
	  }
	  checkStatusInDifferentContext() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _desktopIsActive)[_desktopIsActive]) {
	      return Promise.resolve(false);
	    }

	    // TODO: check two-windowed mode
	    if (DesktopApi.isApiAvailable()) {
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
	function _goToBx2(url) {
	  babelHelpers.classPrivateFieldLooseBase(this, _prepareBxUrl)[_prepareBxUrl](url);
	  babelHelpers.classPrivateFieldLooseBase(this, _locationChangedToBx)[_locationChangedToBx] = true;
	  setTimeout(() => {
	    const event = new main_core_events.BaseEvent({
	      compatData: []
	    });
	    main_core_events.EventEmitter.emit(window, 'BXLinkOpened', event);
	    babelHelpers.classPrivateFieldLooseBase(this, _locationChangedToBx)[_locationChangedToBx] = false;
	  }, LOCATION_RESET_TIMEOUT);
	  location.href = url;
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

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=desktop.bundle.js.map
