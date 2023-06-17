this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_const,im_v2_lib_logger,main_core) {
	'use strict';

	const DesktopApi = {
	  getApiVersion() {
	    if (!this.isApiAvailable()) {
	      return null;
	    }
	    const [,,, version] = window.BXDesktopSystem.GetProperty('versionParts');
	    return version;
	  },
	  isApiAvailable() {
	    return main_core.Type.isObject(window.BXDesktopSystem);
	  },
	  isFeatureEnabled(code) {
	    var _window$BXDesktopSyst;
	    return !!((_window$BXDesktopSyst = window.BXDesktopSystem) != null && _window$BXDesktopSyst.FeatureEnabled(code));
	  }
	};

	let locationChangedToBx = false;
	const checkTimeoutList = {};
	const CHECK_IMAGE_URL = 'http://127.0.0.1:20141';
	const DESKTOP_PROTOCOL_VERSION = 2;
	const DesktopUtils = {
	  checkRunStatus(successCallback, failureCallback) {
	    if (!main_core.Type.isFunction(failureCallback)) {
	      failureCallback = () => {};
	    }

	    // this.settings.openDesktopFromPanel -> false

	    if (!main_core.Type.isFunction(successCallback)) {
	      failureCallback();
	      return false;
	    }
	    const dateCheck = Date.now();
	    const Desktop = DesktopManager.getInstance();
	    if (!Desktop.isDesktopActive()) {
	      failureCallback(false, dateCheck);
	      return true;
	    }
	    if (DesktopApi.isApiAvailable()) {
	      failureCallback(false, dateCheck);
	      return false;
	    }
	    let alreadyExecuteFailureCallback = false;
	    const imageForCheck = main_core.Dom.create({
	      tag: 'img',
	      attrs: {
	        src: `${CHECK_IMAGE_URL}/icon.png?${dateCheck}`,
	        'data-id': dateCheck
	      },
	      props: {
	        className: 'bx-im-messenger__out-of-view'
	      },
	      events: {
	        error: function () {
	          if (alreadyExecuteFailureCallback) {
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
	      alreadyExecuteFailureCallback = true;
	      failureCallback(false, dateCheck);
	      main_core.Dom.remove(imageForCheck);
	    }, 500);
	    return true;
	  },
	  goToBx(url) {
	    if (!/^bx:\/\/v(\d)\//.test(url)) {
	      url = url.replace('bx://', `bx://v${DESKTOP_PROTOCOL_VERSION}/${location.hostname}/`);
	    }
	    locationChangedToBx = true;
	    setTimeout(() => {
	      // eslint-disable-next-line bitrix-rules/no-bx
	      BX.onCustomEvent('BXLinkOpened', []);
	      locationChangedToBx = false;
	    }, 1000);
	    location.href = url;
	  },
	  isLocationChangedToBx() {
	    return locationChangedToBx;
	  },
	  encodeParams(params) {
	    if (!main_core.Type.isPlainObject(params)) {
	      return '';
	    }
	    let stringParams = '';
	    let first = true;
	    for (const i in params) {
	      stringParams = stringParams + (first ? '' : '!!') + i + '!!' + params[i];
	      first = false;
	    }
	    return stringParams;
	  },
	  decodeParams(encodedParams) {
	    const result = {};
	    if (!main_core.Type.isStringFilled(encodedParams)) {
	      return result;
	    }
	    const chunks = encodedParams.split('!!');
	    for (let i = 0; i < chunks.length; i += 2) {
	      result[chunks[i]] = chunks[i + 1];
	    }
	    return result;
	  },
	  encodeParamsJson(params) {
	    if (!main_core.Type.isPlainObject(params)) {
	      return '{}';
	    }
	    let result;
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

	var _desktopIsActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopIsActive");
	var _desktopVersion = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopVersion");
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
	    Object.defineProperty(this, _desktopIsActive, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _desktopVersion, {
	      writable: true,
	      value: 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initDesktopStatus)[_initDesktopStatus]();
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
	    if (DesktopApi.isApiAvailable()) {
	      return DesktopApi.getApiVersion();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _desktopVersion)[_desktopVersion];
	  }
	  setDesktopVersion(version) {
	    if (DesktopApi.isApiAvailable()) {
	      return DesktopApi.getApiVersion();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopVersion)[_desktopVersion] = version;
	  }
	  isDesktopFeatureEnabled(code) {
	    if (!DesktopApi.isApiAvailable()) {
	      return false;
	    }
	    return DesktopApi.isFeatureEnabled(code);
	  }
	  startVideoCall(dialogId = '', withVideo = true) {
	    im_v2_lib_logger.Logger.warn('Desktop: onStartVideoCall', dialogId, withVideo);
	    const callType = withVideo ? 'video' : 'audio';
	    DesktopUtils.goToBx(`bx://callto/${callType}/${dialogId}`);
	    return new Promise(resolve => {
	      resolve();
	    });
	  }
	  checkRunStatus() {
	    return new Promise((resolve, failure) => {
	      DesktopUtils.checkRunStatus(resolve, failure);
	    });
	  }
	}
	function _initDesktopStatus2() {
	  const settings = main_core.Extension.getSettings('im.v2.lib.desktop');
	  this.setDesktopActive(settings.get('desktopIsActive'));
	  this.setDesktopVersion(settings.get('desktopVersion'));
	}

	exports.DesktopManager = DesktopManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX));
//# sourceMappingURL=desktop.bundle.js.map
