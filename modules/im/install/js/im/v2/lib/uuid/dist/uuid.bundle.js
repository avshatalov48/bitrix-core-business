this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_utils) {
	'use strict';

	var _actionIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("actionIds");
	class UuidManager {
	  constructor() {
	    Object.defineProperty(this, _actionIds, {
	      writable: true,
	      value: new Set()
	    });
	  }
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  getActionUuid() {
	    const uuid = im_v2_lib_utils.Utils.text.getUuidV4();
	    babelHelpers.classPrivateFieldLooseBase(this, _actionIds)[_actionIds].add(uuid);
	    return uuid;
	  }
	  hasActionUuid(uuid) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _actionIds)[_actionIds].has(uuid);
	  }
	  removeActionUuid(uuid) {
	    babelHelpers.classPrivateFieldLooseBase(this, _actionIds)[_actionIds].delete(uuid);
	  }
	}

	exports.UuidManager = UuidManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib));
//# sourceMappingURL=uuid.bundle.js.map
