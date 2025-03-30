/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core) {
	'use strict';

	/* eslint-disable no-console */
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
	  const Opener = main_core.Reflection.getClass('BX.Messenger.v2.Lib.Opener');
	  return Opener == null ? void 0 : Opener.openHistory(...args);
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
	legacyMessenger.disk = {
	  saveToDiskAction(...args) {
	    console.warn("Developer: method BXIM.disk.saveToDiskAction is deprecated. Use method 'Messenger.saveFileToDisk' from 'im.public' or 'im.public.iframe' extension.");
	    const [, params] = args;
	    if (!params || !params.fileId) {
	      return Promise.reject();
	    }
	    return messenger.saveFileToDisk(params.fileId);
	  }
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

	class Desktop {
	  constructor() {
	    const settings = main_core.Extension.getSettings('im.public');
	    this.v2enabled = settings.get('v2enabled', false);
	  }
	  async openPage(url, options = {}) {
	    if (!this.v2enabled) {
	      return Promise.resolve(false);
	    }
	    const anchorElement = main_core.Dom.create({
	      tag: 'a',
	      attrs: {
	        href: url
	      }
	    });
	    if (anchorElement.host !== location.host) {
	      return Promise.resolve(false);
	    }
	    const skipNativeBrowser = Boolean(options.skipNativeBrowser);
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForOpenBrowserPage());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().openPage(anchorElement.href, {
	        skipNativeBrowser
	      });
	    }
	    if (skipNativeBrowser === true) {
	      return Promise.resolve(false);
	    }
	    window.open(anchorElement.href, '_blank');
	    return Promise.resolve(true);
	  }
	}
	const desktop = new Desktop();

	const SectionNameMap = {
	  notify: 'notification'
	};
	const prepareSettingsSection = legacySectionName => {
	  var _SectionNameMap$legac;
	  return (_SectionNameMap$legac = SectionNameMap[legacySectionName]) != null ? _SectionNameMap$legac : '';
	};

	class Messenger {
	  constructor() {
	    this.v2enabled = false;
	    const settings = main_core.Extension.getSettings('im.public');
	    this.v2enabled = settings.get('v2enabled', false);
	    this.desktop = desktop;
	  }
	  async openChat(dialogId = '', messageId = 0) {
	    var _getOpener;
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger(dialogId);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToChat(dialogId, messageId);
	    }
	    return (_getOpener = getOpener()) == null ? void 0 : _getOpener.openChat(dialogId, messageId);
	  }
	  async forwardEntityToChat(dialogId, entityConfig) {
	    var _getOpener2;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    return (_getOpener2 = getOpener()) == null ? void 0 : _getOpener2.forwardEntityToChat(dialogId, entityConfig);
	  }
	  async openLines(dialogId = '') {
	    var _getOpener3;
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
	    return (_getOpener3 = getOpener()) == null ? void 0 : _getOpener3.openLines(dialogId);
	  }
	  async openCopilot(dialogId = '', contextId = 0) {
	    var _getOpener4;
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger(dialogId);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToCopilot(dialogId);
	    }
	    return (_getOpener4 = getOpener()) == null ? void 0 : _getOpener4.openCopilot(dialogId, contextId);
	  }
	  async openCollab(dialogId = '') {
	    var _getOpener5;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToCollab(dialogId);
	    }
	    return (_getOpener5 = getOpener()) == null ? void 0 : _getOpener5.openCollab(dialogId);
	  }
	  async openLinesHistory(dialogId = '') {
	    var _getOpener6;
	    if (!this.v2enabled) {
	      window.BXIM.openHistory(dialogId);
	      return Promise.resolve();
	    }
	    return (_getOpener6 = getOpener()) == null ? void 0 : _getOpener6.openHistory(dialogId);
	  }
	  async openNotifications() {
	    var _getOpener7;
	    if (!this.v2enabled) {
	      window.BXIM.openNotify();
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToNotifications();
	    }
	    return (_getOpener7 = getOpener()) == null ? void 0 : _getOpener7.openNotifications();
	  }
	  async openRecentSearch() {
	    var _getOpener8;
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger();
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToRecentSearch();
	    }
	    return (_getOpener8 = getOpener()) == null ? void 0 : _getOpener8.openRecentSearch();
	  }
	  async openSettings(options = {}) {
	    var _options$onlyPanel2, _getOpener9;
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
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      var _options$onlyPanel;
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToSettings((_options$onlyPanel = options.onlyPanel) != null ? _options$onlyPanel : '');
	    }
	    const settingsSection = prepareSettingsSection((_options$onlyPanel2 = options.onlyPanel) != null ? _options$onlyPanel2 : '');
	    return (_getOpener9 = getOpener()) == null ? void 0 : _getOpener9.openSettings(settingsSection);
	  }
	  async openConference(options = {}) {
	    var _getOpener10;
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
	    return (_getOpener10 = getOpener()) == null ? void 0 : _getOpener10.openConference(code);
	  }
	  async openChatCreation(chatType) {
	    var _getOpener11;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToChatCreation(chatType);
	    }
	    return (_getOpener11 = getOpener()) == null ? void 0 : _getOpener11.openChatCreation(chatType);
	  }
	  async startVideoCall(dialogId = '', withVideo = true) {
	    var _getOpener12;
	    if (!this.v2enabled) {
	      window.BXIM.callTo(dialogId, withVideo);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToVideoCall(dialogId, withVideo);
	    }
	    return (_getOpener12 = getOpener()) == null ? void 0 : _getOpener12.startVideoCall(dialogId, withVideo);
	  }
	  async startPhoneCall(number, params) {
	    var _getOpener13;
	    if (!this.v2enabled) {
	      window.BXIM.phoneTo(number, params);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToPhoneCall(number, params);
	    }
	    return (_getOpener13 = getOpener()) == null ? void 0 : _getOpener13.startPhoneCall(number, params);
	  }
	  async startCallList(callListId, params) {
	    var _getOpener14;
	    if (!this.v2enabled) {
	      window.BXIM.startCallList(callListId, params);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToCallList(callListId, params);
	    }
	    return (_getOpener14 = getOpener()) == null ? void 0 : _getOpener14.startCallList(callListId, params);
	  }
	  enableDesktopRedirect() {
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    DesktopManager == null ? void 0 : DesktopManager.getInstance().enableRedirect();
	  }
	  disableDesktopRedirect() {
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    DesktopManager == null ? void 0 : DesktopManager.getInstance().disableRedirect();
	  }
	  setWebRTCDebug(debug = false) {
	    if (!this.v2enabled) {
	      return;
	    }
	    const PhoneManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.PhoneManager');
	    PhoneManager == null ? void 0 : PhoneManager.getInstance().toggleDebugFlag(debug);
	    const CallManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.CallManager');
	    CallManager == null ? void 0 : CallManager.getInstance().toggleDebugFlag(debug);
	  }
	  async saveFileToDisk(fileId) {
	    const {
	      DiskService
	    } = main_core.Reflection.getClass('BX.Messenger.v2.Service');
	    if (!DiskService) {
	      return;
	    }
	    await new DiskService().save([fileId]).catch(error => {
	      console.error('Messenger.saveFileToDisk error:', error);
	    });
	    BX.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('IM_SERVICE_FILE_SAVED_ON_DISK_SUCCESS_MSGVER_1')
	    });
	  }
	}
	const getOpener = () => {
	  return main_core.Reflection.getClass('BX.Messenger.v2.Lib.Opener');
	};
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
