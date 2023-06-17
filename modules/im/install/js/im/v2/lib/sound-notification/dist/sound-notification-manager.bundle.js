this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_vue3_vuex,im_v2_application_core,im_v2_lib_desktop,im_v2_lib_call,main_core_events,im_v2_const) {
	'use strict';

	const SoundFile = {
	  [im_v2_const.SoundType.reminder]: '/bitrix/js/im/audio/reminder.mp3',
	  [im_v2_const.SoundType.newMessage1]: '/bitrix/js/im/audio/new-message-1.mp3',
	  [im_v2_const.SoundType.newMessage2]: '/bitrix/js/im/audio/new-message-2.mp3',
	  [im_v2_const.SoundType.send]: '/bitrix/js/im/audio/send.mp3',
	  [im_v2_const.SoundType.dialtone]: '/bitrix/js/im/audio/video-dialtone.mp3',
	  [im_v2_const.SoundType.ringtone]: '/bitrix/js/im/audio/video-ringtone.mp3',
	  [im_v2_const.SoundType.start]: '/bitrix/js/im/audio/video-start.mp3',
	  [im_v2_const.SoundType.stop]: '/bitrix/js/im/audio/video-stop.mp3',
	  [im_v2_const.SoundType.error]: '/bitrix/js/im/audio/video-error.mp3'
	};
	var _isPlayingLoop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPlayingLoop");
	var _currentPlayingSound = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentPlayingSound");
	var _loopTimers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loopTimers");
	var _notifyOtherTabs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("notifyOtherTabs");
	class SoundPlayer {
	  constructor() {
	    Object.defineProperty(this, _notifyOtherTabs, {
	      value: _notifyOtherTabs2
	    });
	    Object.defineProperty(this, _isPlayingLoop, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _currentPlayingSound, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loopTimers, {
	      writable: true,
	      value: {}
	    });
	    main_core_events.EventEmitter.subscribe('onLocalStorageSet', event => {
	      const [changedLocalStorageData] = event.getData();
	      if (changedLocalStorageData.key !== SoundPlayer.syncEvent) {
	        return;
	      }
	      this.stop(changedLocalStorageData.value.soundType, true);
	    });
	  }
	  playSingle(type) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isPlayingLoop)[_isPlayingLoop]) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound]) {
	      this.stop(type);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _notifyOtherTabs)[_notifyOtherTabs](type);
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound] = new Audio(SoundFile[type]);
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound].play().catch(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound] = null;
	    });
	  }
	  playLoop(type, timeout = 5000) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound]) {
	      this.stop(type);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _isPlayingLoop)[_isPlayingLoop] = false;
	    this.playSingle(type);
	    babelHelpers.classPrivateFieldLooseBase(this, _isPlayingLoop)[_isPlayingLoop] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _loopTimers)[_loopTimers][type] = setTimeout(() => {
	      this.playLoop(type, timeout);
	    }, timeout);
	  }
	  stop(type, skip = false) {
	    if (!skip) {
	      babelHelpers.classPrivateFieldLooseBase(this, _notifyOtherTabs)[_notifyOtherTabs](type);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _loopTimers)[_loopTimers][type]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _isPlayingLoop)[_isPlayingLoop] = false;
	      clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _loopTimers)[_loopTimers][type]);
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound].pause();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound].currentTime = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound] = null;
	  }
	}
	function _notifyOtherTabs2(soundType) {
	  const localStorageTtl = 1;
	  BX.localStorage.set(SoundPlayer.syncEvent, {
	    soundType
	  }, localStorageTtl);
	}
	SoundPlayer.syncEvent = 'im-sound-stop';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _desktopManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopManager");
	var _soundPlayer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("soundPlayer");
	var _canPlayInContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canPlayInContext");
	var _canPlayForUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canPlayForUser");
	var _isPrioritySoundType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPrioritySoundType");
	var _hasActiveCall = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasActiveCall");
	var _isSoundEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSoundEnabled");
	class SoundNotificationManager {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _isSoundEnabled, {
	      value: _isSoundEnabled2
	    });
	    Object.defineProperty(this, _hasActiveCall, {
	      value: _hasActiveCall2
	    });
	    Object.defineProperty(this, _isPrioritySoundType, {
	      value: _isPrioritySoundType2
	    });
	    Object.defineProperty(this, _canPlayForUser, {
	      value: _canPlayForUser2
	    });
	    Object.defineProperty(this, _canPlayInContext, {
	      value: _canPlayInContext2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _desktopManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _soundPlayer, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopManager)[_desktopManager] = new im_v2_lib_desktop.DesktopManager();
	    babelHelpers.classPrivateFieldLooseBase(this, _soundPlayer)[_soundPlayer] = new SoundPlayer();
	  }
	  playOnce(type) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasActiveCall)[_hasActiveCall]() || !babelHelpers.classPrivateFieldLooseBase(this, _canPlayInContext)[_canPlayInContext]()) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canPlayForUser)[_canPlayForUser](type)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _soundPlayer)[_soundPlayer].playSingle(type);
	  }
	  playLoop(type, timeout = 5000, force = false) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasActiveCall)[_hasActiveCall]() && !force) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canPlayInContext)[_canPlayInContext]() || !babelHelpers.classPrivateFieldLooseBase(this, _canPlayForUser)[_canPlayForUser](type)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _soundPlayer)[_soundPlayer].playLoop(type, timeout);
	  }
	  stop(type) {
	    babelHelpers.classPrivateFieldLooseBase(this, _soundPlayer)[_soundPlayer].stop(type);
	  }
	}
	function _canPlayInContext2() {
	  return im_v2_lib_desktop.DesktopManager.isDesktop() || !babelHelpers.classPrivateFieldLooseBase(this, _desktopManager)[_desktopManager].isDesktopActive();
	}
	function _canPlayForUser2(type) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isPrioritySoundType)[_isPrioritySoundType](type)) {
	    return true;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isSoundEnabled)[_isSoundEnabled]()) {
	    return false;
	  }
	  const status = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/getStatus'](im_v2_application_core.Core.getUserId());
	  return status !== im_v2_const.UserStatus.dnd;
	}
	function _isPrioritySoundType2(type) {
	  return [im_v2_const.SoundType.start, im_v2_const.SoundType.dialtone, im_v2_const.SoundType.ringtone].includes(type);
	}
	function _hasActiveCall2() {
	  return im_v2_lib_call.CallManager.getInstance().hasCurrentCall();
	}
	function _isSoundEnabled2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['application/settings/get'](im_v2_const.Settings.application.enableSound);
	}
	SoundNotificationManager.instance = null;

	exports.SoundNotificationManager = SoundNotificationManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Vue3.Vuex,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Const));
//# sourceMappingURL=sound-notification-manager.bundle.js.map
