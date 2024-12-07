/* eslint-disable */
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
	  [im_v2_const.SoundType.ringtoneModern]: '/bitrix/js/im/audio/video-ringtone-modern.mp3?v2',
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _currentPlayingSound)[_currentPlayingSound].src.endsWith(SoundFile[type])) {
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

	var _canPlayInContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canPlayInContext");
	var _isUserDnd = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserDnd");
	var _hasActiveCall = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasActiveCall");
	var _isSoundEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSoundEnabled");
	class SoundNotificationManager {
	  static getInstance() {
	    if (!this.instance) {
	      const store = im_v2_application_core.Core.getStore();
	      const desktopManager = im_v2_lib_desktop.DesktopManager.getInstance();
	      const callManager = im_v2_lib_call.CallManager.getInstance();
	      const soundPlayer = new SoundPlayer();
	      this.instance = new this(store, desktopManager, callManager, soundPlayer);
	    }
	    return this.instance;
	  }
	  constructor(store, desktopManager, callManager, soundPlayer) {
	    Object.defineProperty(this, _isSoundEnabled, {
	      value: _isSoundEnabled2
	    });
	    Object.defineProperty(this, _hasActiveCall, {
	      value: _hasActiveCall2
	    });
	    Object.defineProperty(this, _isUserDnd, {
	      value: _isUserDnd2
	    });
	    Object.defineProperty(this, _canPlayInContext, {
	      value: _canPlayInContext2
	    });
	    this.store = store;
	    this.desktopManager = desktopManager;
	    this.soundPlayer = soundPlayer;
	    this.callManager = callManager;
	  }
	  playOnce(type) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasActiveCall)[_hasActiveCall]() || !babelHelpers.classPrivateFieldLooseBase(this, _canPlayInContext)[_canPlayInContext]()) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isSoundEnabled)[_isSoundEnabled]() || babelHelpers.classPrivateFieldLooseBase(this, _isUserDnd)[_isUserDnd]()) {
	      return;
	    }
	    this.soundPlayer.playSingle(type);
	  }
	  forcePlayOnce(type) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canPlayInContext)[_canPlayInContext]()) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isSoundEnabled)[_isSoundEnabled]()) {
	      return;
	    }
	    this.soundPlayer.playSingle(type);
	  }
	  playLoop(type, timeout = 5000, force = false) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasActiveCall)[_hasActiveCall]() && !force) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canPlayInContext)[_canPlayInContext]()) {
	      return;
	    }
	    if (force) {
	      this.soundPlayer.playLoop(type, timeout);
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isUserDnd)[_isUserDnd]() || !babelHelpers.classPrivateFieldLooseBase(this, _isSoundEnabled)[_isSoundEnabled]()) {
	      return;
	    }
	    this.soundPlayer.playLoop(type, timeout);
	  }
	  stop(type) {
	    this.soundPlayer.stop(type);
	  }
	}
	function _canPlayInContext2() {
	  return im_v2_lib_desktop.DesktopManager.isDesktop() || !this.desktopManager.isDesktopActive();
	}
	function _isUserDnd2() {
	  const status = this.store.getters['application/settings/get'](im_v2_const.Settings.user.status);
	  return status === im_v2_const.UserStatus.dnd;
	}
	function _hasActiveCall2() {
	  return this.callManager.hasCurrentCall();
	}
	function _isSoundEnabled2() {
	  return this.store.getters['application/settings/get'](im_v2_const.Settings.notification.enableSound);
	}
	SoundNotificationManager.instance = null;

	exports.SoundNotificationManager = SoundNotificationManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Vue3.Vuex,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Const));
//# sourceMappingURL=sound-notification-manager.bundle.js.map
