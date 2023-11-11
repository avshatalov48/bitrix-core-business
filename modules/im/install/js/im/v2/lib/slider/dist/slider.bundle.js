/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_desktopApi,main_core,main_core_events,im_v2_application_core,im_v2_const,im_v2_lib_logger,im_v2_application_launch,im_v2_lib_call,im_v2_lib_phone,im_v2_lib_utils,im_v2_lib_desktop,im_v2_provider_service) {
	'use strict';

	const SLIDER_PREFIX = 'im:slider';
	const BASE_STACK_INDEX = 1200;
	const SLIDER_CONTAINER_CLASS = 'bx-im-messenger__slider';
	const LOADER_CHATS_PATH = '/bitrix/js/im/v2/lib/slider/src/images/loader-chats.svg?v2';
	var _checkHistoryDialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkHistoryDialogId");
	var _prepareHistorySliderLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareHistorySliderLink");
	class MessengerSlider {
	  static init() {
	    if (this.instance) {
	      return;
	    }
	    this.instance = new this();
	  }
	  static getInstance() {
	    this.init();
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _prepareHistorySliderLink, {
	      value: _prepareHistorySliderLink2
	    });
	    Object.defineProperty(this, _checkHistoryDialogId, {
	      value: _checkHistoryDialogId2
	    });
	    this.instances = {};
	    this.sidePanelManager = BX.SidePanel.Instance;
	    this.v2enabled = false;
	    im_v2_lib_logger.Logger.warn('Slider: class created');
	    this.initSettings();
	    this.bindEvents();
	    this.store = im_v2_application_core.Core.getStore();
	  }
	  async openChat(dialogId = '') {
	    const preparedDialogId = dialogId.toString();
	    if (im_v2_lib_utils.Utils.dialog.isLinesExternalId(preparedDialogId)) {
	      return this.openLines(preparedDialogId);
	    }
	    await this.openSlider();
	    await this.store.dispatch('application/setLayout', {
	      layoutName: im_v2_const.Layout.chat.name,
	      entityId: preparedDialogId
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenChat, {
	      dialogId: preparedDialogId
	    });
	    return Promise.resolve();
	  }
	  async openLines(dialogId = '') {
	    let preparedDialogId = dialogId.toString();
	    if (im_v2_lib_utils.Utils.dialog.isLinesExternalId(preparedDialogId)) {
	      const linesService = new im_v2_provider_service.LinesService();
	      preparedDialogId = await linesService.getDialogIdByUserCode(preparedDialogId);
	    }
	    await this.openSlider();
	    return this.store.dispatch('application/setLayout', {
	      layoutName: im_v2_const.Layout.openlines.name,
	      entityId: preparedDialogId
	    });
	  }
	  openHistory(dialogId = '') {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _checkHistoryDialogId)[_checkHistoryDialogId](dialogId)) {
	      return Promise.reject();
	    }
	    const sliderLink = babelHelpers.classPrivateFieldLooseBase(this, _prepareHistorySliderLink)[_prepareHistorySliderLink](dialogId);
	    BX.SidePanel.Instance.open(sliderLink, {
	      width: im_v2_lib_utils.Utils.dialog.isLinesExternalId(dialogId) ? 700 : 1000,
	      allowChangeHistory: false,
	      allowChangeTitle: false,
	      cacheable: false
	    });
	    return Promise.resolve();
	  }
	  async openNotifications() {
	    await this.openSlider();
	    await this.store.dispatch('application/setLayout', {
	      layoutName: im_v2_const.Layout.notification.name
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenNotifications);
	    return Promise.resolve();
	  }
	  async openRecentSearch() {
	    await this.openSlider();
	    await this.store.dispatch('application/setLayout', {
	      layoutName: im_v2_const.Layout.chat.name
	    });
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.recent.openSearch);
	    return Promise.resolve();
	  }
	  async openSettings(options = {}) {
	    im_v2_lib_logger.Logger.warn('Slider: openSettings', options);
	    await this.openSlider();
	    await this.store.dispatch('application/setLayout', {
	      layoutName: im_v2_const.Layout.settings.name
	    });
	    return Promise.resolve();
	  }
	  openConference(code = '') {
	    im_v2_lib_logger.Logger.warn('Slider: openConference', code);
	    if (!im_v2_lib_utils.Utils.conference.isValidCode(code)) {
	      return new Promise((resolve, reject) => {
	        reject();
	      });
	    }
	    const url = im_v2_lib_utils.Utils.conference.getUrlByCode(code);
	    im_v2_lib_utils.Utils.browser.openLink(url, im_v2_lib_utils.Utils.conference.getWindowNameByCode(code));
	    return new Promise((resolve, reject) => {
	      resolve();
	    });
	  }
	  startVideoCall(dialogId = '', withVideo = true) {
	    im_v2_lib_logger.Logger.warn('Slider: onStartVideoCall', dialogId, withVideo);
	    if (!im_v2_lib_utils.Utils.dialog.isDialogId(dialogId)) {
	      im_v2_lib_logger.Logger.error('Slider: onStartVideoCall - dialogId is not correct', dialogId);
	      return false;
	    }
	    im_v2_lib_call.CallManager.getInstance().startCall(dialogId, withVideo);
	    return Promise.resolve();
	  }
	  startPhoneCall(number, params) {
	    im_v2_lib_logger.Logger.warn('Slider: startPhoneCall', number, params);
	    im_v2_lib_phone.PhoneManager.getInstance().startCall(number, params);
	    return Promise.resolve();
	  }
	  startCallList(callListId, params) {
	    im_v2_lib_logger.Logger.warn('Slider: startCallList', callListId, params);
	    im_v2_lib_phone.PhoneManager.getInstance().startCallList(callListId, params);
	    return Promise.resolve();
	  }
	  openNewTab(path) {
	    if (im_v2_lib_desktopApi.DesktopApi.getApiVersion() >= 75 && im_v2_lib_desktopApi.DesktopApi.isChatTab()) {
	      im_v2_lib_desktopApi.DesktopApi.createImTab(`${path}&${im_v2_const.GetParameter.desktopChatTabMode}=Y`);
	    } else {
	      im_v2_lib_utils.Utils.browser.openLink(path);
	    }
	  }
	  bindEvents() {
	    if (!this.v2enabled) {
	      im_v2_lib_logger.Logger.warn('Slider: v2 is not enabled');
	      return false;
	    }
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onCloseByEsc', this.onCloseByEsc.bind(this));
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', this.onClose.bind(this));
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.onDestroy.bind(this));
	    main_core.Event.ready(this.initZIndex.bind(this));
	    return true;
	  }
	  initSettings() {
	    const settings = main_core.Extension.getSettings('im.v2.lib.slider');
	    this.v2enabled = settings.get('v2enabled', false);
	  }
	  openSlider() {
	    if (im_v2_lib_desktop.DesktopManager.isChatWindow()) {
	      this.sidePanelManager.closeAll(true);
	      return Promise.resolve();
	    }
	    if (this.isOpened()) {
	      main_core.ZIndexManager.bringToFront(this.getCurrent().getOverlay());
	      return Promise.resolve();
	    }
	    this.launchMessengerApplication();
	    return new Promise(resolve => {
	      if (this.isFocused()) {
	        resolve();
	        return;
	      }
	      const nextId = this.getNextId();
	      this.sidePanelManager.open(`${SLIDER_PREFIX}:${nextId}`, {
	        data: {
	          rightBoundary: 0
	        },
	        cacheable: false,
	        animationDuration: 100,
	        hideControls: true,
	        customLeftBoundary: 0,
	        customRightBoundary: 0,
	        loader: LOADER_CHATS_PATH,
	        contentCallback: () => {
	          return `<div class="${SLIDER_CONTAINER_CLASS}"></div>`;
	        },
	        events: {
	          onLoad: event => {
	            event.slider.showLoader();
	          },
	          onOpenComplete: event => {
	            this.initMessengerComponent().then(() => {
	              event.slider.closeLoader();
	              return resolve();
	            });
	          }
	        }
	      });
	      this.instances[nextId] = this.sidePanelManager.getSlider(`${SLIDER_PREFIX}:${nextId}`);
	    });
	  }
	  launchMessengerApplication() {
	    if (this.applicationPromise) {
	      return this.applicationPromise;
	    }
	    this.applicationPromise = main_core.Runtime.loadExtension('im.v2.application.messenger').then(() => {
	      return im_v2_application_launch.Launch('messenger');
	    }).then(application => {
	      im_v2_lib_logger.Logger.warn('Slider: Messenger application launched', application);
	      return application;
	    });
	    return this.applicationPromise;
	  }
	  initMessengerComponent() {
	    return this.applicationPromise.then(application => {
	      this.store.dispatch('application/setLayout', {
	        layoutName: im_v2_const.Layout.chat.name,
	        entityId: ''
	      });
	      return application.initComponent(`.${SLIDER_CONTAINER_CLASS}`);
	    });
	  }
	  onDialogOpen(event) {
	    im_v2_lib_logger.Logger.warn('Slider: onDialogOpen', event.data.dialogId);
	  }
	  onClose({
	    data: event
	  }) {
	    [event] = event;
	    const sliderId = event.getSlider().getUrl().toString();
	    if (!sliderId.startsWith(SLIDER_PREFIX)) {
	      return;
	    }
	    if (!this.canClose()) {
	      event.denyAction();
	      return;
	    }

	    // TODO: emit event to close all popups

	    const id = this.getIdFromSliderId(sliderId);
	    delete this.instances[id];
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.slider.onClose);
	    this.store.dispatch('application/setLayout', {
	      layoutName: im_v2_const.Layout.chat.name,
	      entityId: ''
	    });
	  }
	  onCloseByEsc({
	    data: event
	  }) {
	    [event] = event;
	    const sliderId = event.getSlider().getUrl().toString();
	    if (!sliderId.startsWith(SLIDER_PREFIX)) {
	      return;
	    }
	    if (!this.canCloseByEsc()) {
	      event.denyAction();
	    }
	  }
	  onDestroy({
	    data: event
	  }) {
	    [event] = event;
	    const sliderId = event.getSlider().getUrl().toString();
	    if (!sliderId.startsWith(SLIDER_PREFIX)) {
	      return;
	    }
	    const id = this.getIdFromSliderId(sliderId);
	    delete this.instances[id];
	  }
	  initZIndex() {
	    if (!main_core.ZIndexManager) {
	      return;
	    }
	    const stack = main_core.ZIndexManager.getOrAddStack(document.body);
	    stack.baseIndex = BASE_STACK_INDEX;
	    stack.sort();
	  }
	  getZIndex() {
	    return BASE_STACK_INDEX;
	  }
	  isOpened() {
	    return Object.keys(this.instances).length > 0;
	  }
	  isFocused() {
	    if (!this.isOpened()) {
	      return false;
	    }
	    const slider = this.sidePanelManager.getTopSlider();
	    if (!slider) {
	      return false;
	    }
	    return slider.getUrl().toString().startsWith(SLIDER_PREFIX);
	  }
	  canClose() {
	    return true;
	  }
	  canCloseByEsc() {
	    return false;
	  }
	  getCurrent() {
	    return this.instances[this.getCurrentId()];
	  }
	  getCurrentId() {
	    return Object.keys(this.instances).length;
	  }
	  getNextId() {
	    return this.getCurrentId() + 1;
	  }
	  getIdFromSliderId(sliderId) {
	    return Number.parseInt(sliderId.slice(SLIDER_PREFIX.length + 1), 10);
	  }
	}
	function _checkHistoryDialogId2(dialogId) {
	  return im_v2_lib_utils.Utils.dialog.isDialogId(dialogId) || im_v2_lib_utils.Utils.dialog.isLinesHistoryId(dialogId) || im_v2_lib_utils.Utils.dialog.isLinesExternalId(dialogId);
	}
	function _prepareHistorySliderLink2(dialogId) {
	  const getParams = new URLSearchParams({
	    [im_v2_const.GetParameter.openHistory]: dialogId,
	    [im_v2_const.GetParameter.backgroundType]: 'light'
	  });
	  return `/desktop_app/history.php?${getParams.toString()}`;
	}
	MessengerSlider.instance = null;

	exports.MessengerSlider = MessengerSlider;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service));
//# sourceMappingURL=slider.bundle.js.map
