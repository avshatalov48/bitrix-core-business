/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core,im_v2_lib_utils,im_v2_provider_service) {
	'use strict';

	const DAY = 1000 * 60 * 60 * 24;
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _absentList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("absentList");
	var _absentCheckInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("absentCheckInterval");
	var _birthdayLoadInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("birthdayLoadInterval");
	var _setUserBirthdayFlag = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setUserBirthdayFlag");
	var _setUserAbsentFlag = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setUserAbsentFlag");
	var _startAbsentCheckInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startAbsentCheckInterval");
	var _stopAbsentCheckInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stopAbsentCheckInterval");
	var _checkAbsentList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkAbsentList");
	var _startBirthdayLoadInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startBirthdayLoadInterval");
	class UserStatusManager {
	  constructor() {
	    Object.defineProperty(this, _startBirthdayLoadInterval, {
	      value: _startBirthdayLoadInterval2
	    });
	    Object.defineProperty(this, _checkAbsentList, {
	      value: _checkAbsentList2
	    });
	    Object.defineProperty(this, _stopAbsentCheckInterval, {
	      value: _stopAbsentCheckInterval2
	    });
	    Object.defineProperty(this, _startAbsentCheckInterval, {
	      value: _startAbsentCheckInterval2
	    });
	    Object.defineProperty(this, _setUserAbsentFlag, {
	      value: _setUserAbsentFlag2
	    });
	    Object.defineProperty(this, _setUserBirthdayFlag, {
	      value: _setUserBirthdayFlag2
	    });
	    Object.defineProperty(this, _absentList, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _absentCheckInterval, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _birthdayLoadInterval, {
	      writable: true,
	      value: null
	    });
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  onUserUpdate(user) {
	    babelHelpers.classPrivateFieldLooseBase(this, _startBirthdayLoadInterval)[_startBirthdayLoadInterval]();
	    if (user.birthday && im_v2_lib_utils.Utils.user.isBirthdayToday(user.birthday)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setUserBirthdayFlag)[_setUserBirthdayFlag](user.id, true);
	      setTimeout(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setUserBirthdayFlag)[_setUserBirthdayFlag](user.id, false);
	      }, im_v2_lib_utils.Utils.date.getTimeToNextMidnight());
	    }
	    if (main_core.Type.isDate(user.absent)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setUserAbsentFlag)[_setUserAbsentFlag](user.id, true);
	      babelHelpers.classPrivateFieldLooseBase(this, _startAbsentCheckInterval)[_startAbsentCheckInterval](user.id);
	    } else if (user.absent === false && babelHelpers.classPrivateFieldLooseBase(this, _absentList)[_absentList].has(user.id)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setUserAbsentFlag)[_setUserAbsentFlag](user.id, false);
	      babelHelpers.classPrivateFieldLooseBase(this, _stopAbsentCheckInterval)[_stopAbsentCheckInterval](user.id);
	    }
	  }
	  clear() {
	    babelHelpers.classPrivateFieldLooseBase(this, _absentList)[_absentList] = new Set();
	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _absentCheckInterval)[_absentCheckInterval]);
	    babelHelpers.classPrivateFieldLooseBase(this, _absentCheckInterval)[_absentCheckInterval] = null;
	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _birthdayLoadInterval)[_birthdayLoadInterval]);
	    babelHelpers.classPrivateFieldLooseBase(this, _birthdayLoadInterval)[_birthdayLoadInterval] = null;
	  }
	}
	function _setUserBirthdayFlag2(userId, flag) {
	  im_v2_application_core.Core.getStore().dispatch('users/update', {
	    id: userId,
	    fields: {
	      isBirthday: flag
	    }
	  });
	}
	function _setUserAbsentFlag2(userId, flag) {
	  im_v2_application_core.Core.getStore().dispatch('users/update', {
	    id: userId,
	    fields: {
	      isAbsent: flag
	    }
	  });
	}
	function _startAbsentCheckInterval2(userId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _absentList)[_absentList].add(userId);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _absentCheckInterval)[_absentCheckInterval]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _absentCheckInterval)[_absentCheckInterval] = setTimeout(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _checkAbsentList)[_checkAbsentList]();
	    setInterval(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _checkAbsentList)[_checkAbsentList]();
	    }, DAY);
	  }, im_v2_lib_utils.Utils.date.getTimeToNextMidnight());
	}
	function _stopAbsentCheckInterval2(userId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _absentList)[_absentList].delete(userId);
	}
	function _checkAbsentList2() {
	  for (const userId of babelHelpers.classPrivateFieldLooseBase(this, _absentList)[_absentList]) {
	    const user = im_v2_application_core.Core.getStore().getters['users/get'](userId);
	    if (!user || !main_core.Type.isDate(user.absent)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _stopAbsentCheckInterval)[_stopAbsentCheckInterval](userId);
	      return;
	    }
	    const absentEnd = user.absent.getTime();
	    if (absentEnd <= Date.now()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setUserAbsentFlag)[_setUserAbsentFlag](user.id, false);
	      babelHelpers.classPrivateFieldLooseBase(this, _stopAbsentCheckInterval)[_stopAbsentCheckInterval](user.id);
	    }
	  }
	}
	function _startBirthdayLoadInterval2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _birthdayLoadInterval)[_birthdayLoadInterval]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _birthdayLoadInterval)[_birthdayLoadInterval] = setTimeout(() => {
	    im_v2_provider_service.RecentService.getInstance().loadFirstPage();
	    setInterval(() => {
	      im_v2_provider_service.RecentService.getInstance().loadFirstPage();
	    }, DAY);
	  }, im_v2_lib_utils.Utils.date.getTimeToNextMidnight());
	}
	Object.defineProperty(UserStatusManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.UserStatusManager = UserStatusManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service));
//# sourceMappingURL=user-status.bundle.js.map
