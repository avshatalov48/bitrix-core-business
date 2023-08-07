/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core) {
	'use strict';

	const legacyMessenger = {};
	legacyMessenger.openMessenger = function (...args) {
	  console.error("Developer: method BXIM.openMessenger is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openChat(...args);
	};
	legacyMessenger.openMessengerSlider = function (dialogId) {
	  console.error("Developer: method BXIM.openMessengerSlider is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openChat(dialogId);
	};
	legacyMessenger.openNotify = function (...args) {
	  console.error("Developer: method BXIM.openNotify is deprecated. Use method 'Messenger.openNotifications' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openNotifications(...args);
	};
	legacyMessenger.openSettings = function (...args) {
	  console.error("Developer: method BXIM.openSettings is deprecated. Use method 'Messenger.openSettings' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openSettings(...args);
	};
	legacyMessenger.callTo = function (...args) {
	  console.error("Developer: method BXIM.callTo is deprecated. Use method 'Messenger.startVideoCall' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.startVideoCall(...args);
	};
	legacyMessenger.messenger = {};
	legacyMessenger.messenger.popupPopupMenu = false;
	legacyMessenger.settings = {};
	const legacyDesktop = {
	  init: () => {},
	  enableInVersion: () => false,
	  getApiVersion: () => 0,
	  addCustomEvent: () => {},
	  onCustomEvent: () => {},
	  ready: () => true,
	  log: () => {}
	};

	class Messenger {
	  constructor() {
	    this.v2enabled = false;
	    const settings = main_core.Extension.getSettings('im.public');
	    this.v2enabled = settings.get('v2enabled', false);
	  }
	  async openChat(dialogId = '', text = '') {
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger(dialogId);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().openChat(dialogId);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openChat(dialogId, text);
	  }
	  openLines() {
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger(0, 'im-ol');
	      return Promise.resolve();
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openLines();
	  }
	  async openNotifications() {
	    if (!this.v2enabled) {
	      window.BXIM.openNotify();
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().openNotifications();
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openNotifications();
	  }
	  async openRecentSearch() {
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger();
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().openRecentSearch();
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openRecentSearch();
	  }
	  openSettings(options = {}) {
	    if (!this.v2enabled) {
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
	      return Promise.resolve();
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openSettings();
	  }
	  async startVideoCall(dialogId = '', withVideo = true) {
	    if (!this.v2enabled) {
	      window.BXIM.callTo(dialogId, withVideo);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().startVideoCall(dialogId, withVideo);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().startVideoCall(dialogId, withVideo);
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
	if (messenger.v2enabled && main_core.Type.isUndefined(window.BX.desktop) && main_core.Type.isObject(window.BXDesktopSystem) && window.parent === window) {
	  window.BX.desktop = legacyDesktop;
	}

	exports.Messenger = messenger;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX));
//# sourceMappingURL=public.bundle.js.map
