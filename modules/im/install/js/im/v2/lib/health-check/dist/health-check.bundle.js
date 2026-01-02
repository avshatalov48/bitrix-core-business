/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports) {
	'use strict';

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _isShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isShown");
	class HealthCheckManager {
	  constructor() {
	    Object.defineProperty(this, _isShown, {
	      writable: true,
	      value: false
	    });
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  setIsShown(status) {
	    babelHelpers.classPrivateFieldLooseBase(this, _isShown)[_isShown] = status;
	  }
	  getIsShown() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isShown)[_isShown];
	  }
	}
	Object.defineProperty(HealthCheckManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.HealthCheckManager = HealthCheckManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {})));
//# sourceMappingURL=health-check.bundle.js.map
