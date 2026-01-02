/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_desktopApi,main_core) {
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
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    if (DesktopManager.isDesktop()) {
	      return Promise.resolve(true);
	    }
	    const targetUrl = new URL(url);
	    if (targetUrl.host !== location.host) {
	      return Promise.resolve(false);
	    }
	    const skipNativeBrowser = Boolean(options.skipNativeBrowser);
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForOpenBrowserPage());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().openPage(targetUrl.href, {
	        skipNativeBrowser
	      });
	    }
	    if (skipNativeBrowser === true) {
	      return Promise.resolve(false);
	    }
	    window.open(targetUrl.href, '_blank');
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
	  async openChatWithBotContext(dialogId = '', context = {}) {
	    var _getOpener2;
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger(dialogId);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToChatWithBotContext(dialogId, context);
	    }
	    return (_getOpener2 = getOpener()) == null ? void 0 : _getOpener2.openChatWithBotContext(dialogId, context);
	  }
	  async forwardEntityToChat(dialogId, entityConfig) {
	    var _getOpener3;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    return (_getOpener3 = getOpener()) == null ? void 0 : _getOpener3.forwardEntityToChat(dialogId, entityConfig);
	  }
	  async openLines(dialogId = '') {
	    var _getOpener4;
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
	    return (_getOpener4 = getOpener()) == null ? void 0 : _getOpener4.openLines(dialogId);
	  }
	  async openCopilot(dialogId = '', contextId = 0) {
	    var _getOpener5;
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger(dialogId);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToCopilot(dialogId);
	    }
	    return (_getOpener5 = getOpener()) == null ? void 0 : _getOpener5.openCopilot(dialogId, contextId);
	  }
	  async openCollab(dialogId = '') {
	    var _getOpener6;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToCollab(dialogId);
	    }
	    return (_getOpener6 = getOpener()) == null ? void 0 : _getOpener6.openCollab(dialogId);
	  }
	  async openChannel(dialogId = '') {
	    var _getOpener7;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToChannel(dialogId);
	    }
	    return (_getOpener7 = getOpener()) == null ? void 0 : _getOpener7.openChannel(dialogId);
	  }
	  async openTaskComments(dialogId = '', messageId = 0) {
	    var _getOpener8;
	    const FeatureManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.FeatureManager');
	    const Feature = main_core.Reflection.getClass('BX.Messenger.v2.Lib.Feature');
	    if (!(FeatureManager != null && FeatureManager.isFeatureAvailable(Feature.isTasksRecentListAvailable))) {
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToTaskComments(dialogId, messageId);
	    }
	    return (_getOpener8 = getOpener()) == null ? void 0 : _getOpener8.openTaskComments(dialogId, messageId);
	  }
	  async openLinesHistory(dialogId = '') {
	    var _getOpener9;
	    if (!this.v2enabled) {
	      window.BXIM.openHistory(dialogId);
	      return Promise.resolve();
	    }
	    return (_getOpener9 = getOpener()) == null ? void 0 : _getOpener9.openHistory(dialogId);
	  }
	  async openNotifications() {
	    var _getOpener10;
	    if (!this.v2enabled) {
	      window.BXIM.openNotify();
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToNotifications();
	    }
	    return (_getOpener10 = getOpener()) == null ? void 0 : _getOpener10.openNotifications();
	  }
	  async openRecentSearch() {
	    var _getOpener11;
	    if (!this.v2enabled) {
	      window.BXIM.openMessenger();
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToRecentSearch();
	    }
	    return (_getOpener11 = getOpener()) == null ? void 0 : _getOpener11.openRecentSearch();
	  }
	  async openSettings(options = {}) {
	    var _options$onlyPanel2, _getOpener12;
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
	    return (_getOpener12 = getOpener()) == null ? void 0 : _getOpener12.openSettings(settingsSection);
	  }
	  async openConference(options = {}) {
	    var _getOpener13;
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
	    return (_getOpener13 = getOpener()) == null ? void 0 : _getOpener13.openConference(code);
	  }
	  async openChatCreation(chatType, params = {}) {
	    var _getOpener14;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToChatCreation(chatType);
	    }
	    return (_getOpener14 = getOpener()) == null ? void 0 : _getOpener14.openChatCreation(chatType, params);
	  }
	  async startVideoCall(dialogId = '', withVideo = true) {
	    var _getOpener15;
	    if (!this.v2enabled) {
	      window.BXIM.callTo(dialogId, withVideo);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    if (isRedirectAllowed) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToVideoCall(dialogId, withVideo);
	    }
	    return (_getOpener15 = getOpener()) == null ? void 0 : _getOpener15.startVideoCall(dialogId, withVideo);
	  }
	  async startPhoneCall(number, params) {
	    var _getOpener16;
	    if (!this.v2enabled) {
	      window.BXIM.phoneTo(number, params);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive && !im_v2_lib_desktopApi.DesktopApi.isAirDesignEnabledInDesktop()) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToPhoneCall(number, params);
	    }
	    return (_getOpener16 = getOpener()) == null ? void 0 : _getOpener16.startPhoneCall(number, params);
	  }
	  async startCallList(callListId, params) {
	    var _getOpener17;
	    if (!this.v2enabled) {
	      window.BXIM.startCallList(callListId, params);
	      return Promise.resolve();
	    }
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const desktopIsActive = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkStatusInDifferentContext());
	    if (desktopIsActive && !im_v2_lib_desktopApi.DesktopApi.isAirDesignEnabledInDesktop()) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToCallList(callListId, params);
	    }
	    return (_getOpener17 = getOpener()) == null ? void 0 : _getOpener17.startCallList(callListId, params);
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
	    const Notifier = main_core.Reflection.getClass('BX.Messenger.v2.Lib.Notifier');
	    Notifier == null ? void 0 : Notifier.file.onDiskSaveComplete();
	  }
	  async openNavigationItem({
	    id,
	    entityId,
	    target
	  }) {
	    var _getOpener18;
	    const DesktopManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
	    const LayoutManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.LayoutManager');
	    const isRedirectAllowed = await (DesktopManager == null ? void 0 : DesktopManager.getInstance().checkForRedirect());
	    const isLayout = LayoutManager == null ? void 0 : LayoutManager.getInstance().isValidLayout(id);
	    if (isRedirectAllowed && isLayout) {
	      return DesktopManager == null ? void 0 : DesktopManager.getInstance().redirectToLayout({
	        id,
	        entityId
	      });
	    }
	    return (_getOpener18 = getOpener()) == null ? void 0 : _getOpener18.openNavigationItem({
	      id,
	      entityId,
	      target
	    });
	  }
	  isEmbeddedMode() {
	    const LayoutManager = main_core.Reflection.getClass('BX.Messenger.v2.Lib.LayoutManager');
	    if (!LayoutManager) {
	      return false;
	    }
	    return LayoutManager.getInstance().isEmbeddedMode();
	  }
	  isMessengerSliderOpened() {
	    const MessengerSlider = main_core.Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
	    if (!MessengerSlider) {
	      return false;
	    }
	    return MessengerSlider.getInstance().isOpened();
	  }
	  isChatOpened(dialogId) {
	    var _getOpener19;
	    return (_getOpener19 = getOpener()) == null ? void 0 : _getOpener19.isChatOpened(dialogId);
	  }
	  async initApplication(applicationName, config = {}) {
	    const launch = main_core.Reflection.getClass('BX.Messenger.v2.Application.Launch');
	    if (!launch) {
	      return Promise.reject();
	    }
	    return launch(applicationName, {
	      ...config,
	      embedded: true
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

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX));
//# sourceMappingURL=public.bundle.js.map
