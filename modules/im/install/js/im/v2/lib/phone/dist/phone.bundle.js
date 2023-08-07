/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,voximplant,voximplant_phoneCalls,applayout,im_v2_application_core) {
	'use strict';

	var _controller = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("controller");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _getController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getController");
	var _getCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentUser");
	class PhoneManager {
	  constructor() {
	    Object.defineProperty(this, _getCurrentUser, {
	      value: _getCurrentUser2
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
	  }
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  static init(phoneSettings) {
	    babelHelpers.classPrivateFieldLooseBase(PhoneManager.getInstance(), _init)[_init](phoneSettings);
	  }
	  openKeyPad(params) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].openKeyPad(params);
	  }
	  closeKeyPad() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller].closeKeyPad();
	  }
	}
	function _init2(phoneSettings) {
	  return;
	  babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller] = babelHelpers.classPrivateFieldLooseBase(this, _getController)[_getController](phoneSettings);
	}
	function _getController2(phoneSettings) {
	  return new voximplant_phoneCalls.PhoneCallsController({
	    phoneEnabled: phoneSettings.phoneEnabled,
	    userId: im_v2_application_core.Core.getUserId(),
	    isAdmin: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().isAdmin,
	    restApps: phoneSettings.restApps,
	    canInterceptCall: phoneSettings.canInterceptCall,
	    deviceActive: phoneSettings.deviceActive,
	    defaultLineId: phoneSettings.defaultLineId,
	    availableLines: phoneSettings.availableLines
	  });
	}
	function _getCurrentUser2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  return im_v2_application_core.Core.getStore().getters['users/get'](userId);
	}

	exports.PhoneManager = PhoneManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Voximplant,BX,BX.Messenger.v2.Application));
//# sourceMappingURL=phone.bundle.js.map
