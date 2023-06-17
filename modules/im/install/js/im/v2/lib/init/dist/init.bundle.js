this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_lib_call,im_v2_lib_smileManager,im_v2_lib_user,im_v2_lib_counter,im_v2_lib_logger,im_v2_lib_notifier,im_v2_const,im_v2_lib_market,im_v2_lib_desktop) {
	'use strict';

	var _started = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("started");
	var _initCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initCurrentUser");
	var _initLogger = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initLogger");
	var _initChatRestrictions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initChatRestrictions");
	var _initCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initCounters");
	var _initMarket = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initMarket");
	var _initSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initSettings");
	class InitManager {
	  static start() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _started)[_started]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initLogger)[_initLogger]();
	    im_v2_lib_logger.Logger.warn('InitManager: start');
	    babelHelpers.classPrivateFieldLooseBase(this, _initCurrentUser)[_initCurrentUser]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initChatRestrictions)[_initChatRestrictions]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initCounters)[_initCounters]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initMarket)[_initMarket]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initSettings)[_initSettings]();
	    im_v2_lib_call.CallManager.init();
	    im_v2_lib_smileManager.SmileManager.init();
	    im_v2_lib_notifier.NotifierManager.init();
	    im_v2_lib_desktop.DesktopManager.init();
	    babelHelpers.classPrivateFieldLooseBase(this, _started)[_started] = true;
	  }
	}
	function _initCurrentUser2() {
	  const {
	    currentUser
	  } = im_v2_application_core.Core.getApplicationData(im_v2_const.ApplicationName.quickAccess);
	  if (!currentUser) {
	    return false;
	  }
	  new im_v2_lib_user.UserManager().setUsersToModel([currentUser]);
	}
	function _initLogger2() {
	  const {
	    loggerConfig
	  } = im_v2_application_core.Core.getApplicationData(im_v2_const.ApplicationName.quickAccess);
	  if (!loggerConfig) {
	    return false;
	  }
	  im_v2_lib_logger.Logger.setConfig(loggerConfig);
	}
	function _initChatRestrictions2() {
	  const {
	    chatOptions
	  } = im_v2_application_core.Core.getApplicationData(im_v2_const.ApplicationName.quickAccess);
	  if (!chatOptions) {
	    return false;
	  }
	  im_v2_application_core.Core.getStore().dispatch('dialogues/setChatOptions', chatOptions);
	}
	function _initCounters2() {
	  const {
	    counters
	  } = im_v2_application_core.Core.getApplicationData(im_v2_const.ApplicationName.quickAccess);
	  if (!counters) {
	    return false;
	  }
	  im_v2_lib_logger.Logger.warn('InitManager: counters', counters);
	  im_v2_lib_counter.CounterManager.init(counters);
	}
	function _initMarket2() {
	  const {
	    marketApps
	  } = im_v2_application_core.Core.getApplicationData(im_v2_const.ApplicationName.quickAccess);
	  if (!marketApps) {
	    return;
	  }
	  im_v2_lib_logger.Logger.warn('InitManager: marketApps', marketApps);
	  im_v2_lib_market.MarketManager.init(marketApps);
	}
	function _initSettings2() {
	  const {
	    settings
	  } = im_v2_application_core.Core.getApplicationData(im_v2_const.ApplicationName.quickAccess);
	  if (!settings) {
	    return;
	  }
	  im_v2_lib_logger.Logger.warn('InitManager: settings', settings);
	  im_v2_application_core.Core.getStore().dispatch('application/settings/set', settings);
	}
	Object.defineProperty(InitManager, _initSettings, {
	  value: _initSettings2
	});
	Object.defineProperty(InitManager, _initMarket, {
	  value: _initMarket2
	});
	Object.defineProperty(InitManager, _initCounters, {
	  value: _initCounters2
	});
	Object.defineProperty(InitManager, _initChatRestrictions, {
	  value: _initChatRestrictions2
	});
	Object.defineProperty(InitManager, _initLogger, {
	  value: _initLogger2
	});
	Object.defineProperty(InitManager, _initCurrentUser, {
	  value: _initCurrentUser2
	});
	Object.defineProperty(InitManager, _started, {
	  writable: true,
	  value: false
	});

	exports.InitManager = InitManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=init.bundle.js.map
