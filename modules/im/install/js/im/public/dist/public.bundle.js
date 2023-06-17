this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core) {
	'use strict';

	const legacyMessenger = {};
	legacyMessenger.openMessenger = function () {
	  console.error("Developer: method BXIM.openMessenger is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openChat(...arguments);
	};
	legacyMessenger.openNotify = function () {
	  console.error("Developer: method BXIM.openNotify is deprecated. Use method 'Messenger.openNotifications' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openNotifications(...arguments);
	};
	legacyMessenger.openSettings = function () {
	  console.error("Developer: method BXIM.openSettings is deprecated. Use method 'Messenger.openSettings' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openSettings(...arguments);
	};
	legacyMessenger.callTo = function () {
	  console.error("Developer: method BXIM.callTo is deprecated. Use method 'Messenger.startVideoCall' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.startVideoCall(...arguments);
	};
	legacyMessenger.messenger = {};
	legacyMessenger.messenger.popupPopupMenu = false;

	class Messenger {
	  constructor() {
	    this.v2enabled = false;
	    const settings = main_core.Extension.getSettings('im.public');
	    this.v2enabled = settings.get('v2enabled', false);
	  }
	  openChat(dialogId = '', text = '') {
	    if (!this.v2enabled) {
	      return new Promise((resolve, reject) => {
	        window.BXIM.openMessenger(dialogId);
	        resolve();
	      });
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openChat(dialogId, text);
	  }
	  openLines() {
	    if (!this.v2enabled) {
	      return new Promise((resolve, reject) => {
	        window.BXIM.openMessenger(0, 'im-ol');
	        resolve();
	      });
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openLines();
	  }
	  openNotifications() {
	    if (!this.v2enabled) {
	      return new Promise((resolve, reject) => {
	        window.BXIM.openNotify();
	        resolve();
	      });
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openNotifications();
	  }
	  openRecentSearch() {
	    if (!this.v2enabled) {
	      return new Promise((resolve, reject) => {
	        window.BXIM.openMessenger();
	        resolve();
	      });
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openRecentSearch();
	  }
	  openSettings(options = {}) {
	    if (!this.v2enabled) {
	      return new Promise((resolve, reject) => {
	        const params = {};
	        if (main_core.Type.isPlainObject(options)) {
	          if (main_core.Type.isStringFilled(options.selected)) {
	            params.active = options.selected;
	          }
	          if (main_core.Type.isStringFilled(options.section)) {
	            params.onlyPanel = options.section;
	          }
	        }
	        window.BXIM.openSettings(params);
	        resolve();
	      });
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openSettings();
	  }
	  startVideoCall(dialogId = '', withVideo = true) {
	    if (!this.v2enabled) {
	      return new Promise(resolve => {
	        window.BXIM.callTo(dialogId, withVideo);
	        resolve();
	      });
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    return new Promise((resolve, reject) => {
	      DesktopManager == null ? void 0 : DesktopManager.getInstance().checkRunStatus().then(() => {
	        DesktopManager == null ? void 0 : DesktopManager.getInstance().startVideoCall(dialogId, withVideo);
	        resolve();
	      }).catch(() => {
	        const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	        MessengerSlider == null ? void 0 : MessengerSlider.getInstance().startVideoCall(dialogId, withVideo).then(resolve).catch(reject);
	      });
	    });
	  }
	}
	const messenger = new Messenger();

	// pretty export
	const namespace = main_core.Reflection.getClass('BX.Messenger');
	if (namespace) {
	  namespace.Public = messenger;
	}

	// compatibility layer
	if (messenger.v2enabled && main_core.Type.isUndefined(window.BXIM) && window.parent === window) {
	  window.BXIM = legacyMessenger;
	}

	exports.Messenger = messenger;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX));
//# sourceMappingURL=public.bundle.js.map
