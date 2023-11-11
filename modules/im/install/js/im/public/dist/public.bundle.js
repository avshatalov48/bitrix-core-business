/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core) {
	'use strict';

	const legacyMessenger = {};
	legacyMessenger.openMessenger = function (...args) {
	  console.warn("Developer: method BXIM.openMessenger is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openChat(...args);
	};
	legacyMessenger.openMessengerSlider = function (dialogId) {
	  console.warn("Developer: method BXIM.openMessengerSlider is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openChat(dialogId);
	};
	legacyMessenger.openHistory = function (...args) {
	  console.warn("Developer: method BXIM.openHistory is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");
	  const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	  return MessengerSlider.getInstance().openHistory(...args);
	};
	legacyMessenger.openNotify = function (...args) {
	  console.warn("Developer: method BXIM.openNotify is deprecated. Use method 'Messenger.openNotifications' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openNotifications(...args);
	};
	legacyMessenger.openSettings = function (...args) {
	  console.warn("Developer: method BXIM.openSettings is deprecated. Use method 'Messenger.openSettings' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openSettings(...args);
	};
	legacyMessenger.openVideoconf = function (code) {
	  console.warn("Developer: method BXIM.openVideoconf is deprecated. Use method 'Messenger.openConference' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.openConference({
	    code
	  });
	};
	legacyMessenger.openVideoconfByUrl = function (link) {
	  console.warn("Developer: method BXIM.openVideoconfByUrl is deprecated. Use method 'Messenger.openConference' from 'im.public' or 'im.public.iframe' extension.");
	  const Utils = main_core.Reflection.getClass('BX.Messenger.v2.Lib.Utils');
	  if (Utils && main_core.Type.isStringFilled(url) && !Utils.conference.isCurrentPortal(url)) {
	    return false;
	  }
	  messenger.openConference({
	    link
	  });
	  return true;
	};
	legacyMessenger.callTo = function (...args) {
	  console.warn("Developer: method BXIM.callTo is deprecated. Use method 'Messenger.startVideoCall' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.startVideoCall(...args);
	};
	legacyMessenger.phoneTo = function (...args) {
	  console.warn("Developer: method BXIM.phoneTo is deprecated. Use method 'Messenger.startPhoneCall' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.startPhoneCall(...args);
	};
	legacyMessenger.startCallList = function (...args) {
	  console.warn("Developer: method BXIM.startCallList is deprecated. Use method 'Messenger.startCallList' from 'im.public' or 'im.public.iframe' extension.");
	  return messenger.startCallList(...args);
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
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToChat(dialogId);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openChat(dialogId, text);
	  }
	  async openLines(dialogId = '') {
	    if (!this.v2enabled) {
	      const preparedDialogId = dialogId === '' ? 0 : dialogId;
	      window.BXIM.openMessenger(preparedDialogId, 'im-ol');
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToLines(dialogId);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openLines(dialogId);
	  }
	  async openLinesHistory(dialogId = '') {
	    if (!this.v2enabled) {
	      window.BXIM.openHistory(dialogId);
	      return Promise.resolve();
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openHistory(dialogId);
	  }
	  async openNotifications() {
	    if (!this.v2enabled) {
	      window.BXIM.openNotify();
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToNotifications();
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
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToRecentSearch();
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
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openSettings(options);
	  }
	  async openConference(options = {}) {
	    if (!this.v2enabled) {
	      if (main_core.Type.isPlainObject(options)) {
	        if (main_core.Type.isStringFilled(options.code)) {
	          window.BXIM.openVideoconf(options.code);
	        }
	        if (main_core.Type.isStringFilled(options.link)) {
	          window.BXIM.openVideoconfByUrl(options.link);
	        }
	      }
	      return Promise.resolve();
	    }
	    const Utils = main_core.Reflection.getClass('BX.Messenger.v2.Lib.Utils');
	    if (main_core.Type.isStringFilled(options.url) && !Utils.conference.isCurrentPortal(options.url)) {
	      Utils.browser.openLink(options.url);
	      return Promise.resolve();
	    }
	    const code = Utils.conference.getCodeByOptions(options);
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    if (DesktopManager != null && DesktopManager.isDesktop()) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().openConference(code);
	    }
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToConference(code);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().openConference(code);
	  }
	  async startVideoCall(dialogId = '', withVideo = true) {
	    if (!this.v2enabled) {
	      window.BXIM.callTo(dialogId, withVideo);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToVideoCall(dialogId, withVideo);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().startVideoCall(dialogId, withVideo);
	  }
	  async startPhoneCall(number, params) {
	    if (!this.v2enabled) {
	      window.BXIM.phoneTo(number, params);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToPhoneCall(number, params);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().startPhoneCall(number, params);
	  }
	  async startCallList(callListId, params) {
	    if (!this.v2enabled) {
	      window.BXIM.startCallList(callListId, params);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToCallList(callListId, params);
	    }
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    return MessengerSlider == null ? void 0 : MessengerSlider.getInstance().startCallList(callListId, params);
	  }
	  enableDesktopRedirect() {
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    DesktopManager == null ? void 0 : DesktopManager.getInstance().enableRedirect();
	  }
	  disableDesktopRedirect() {
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    DesktopManager == null ? void 0 : DesktopManager.getInstance().disableRedirect();
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
