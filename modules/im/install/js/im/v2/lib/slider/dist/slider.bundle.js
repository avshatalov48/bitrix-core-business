this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_const,im_v2_lib_logger,im_v2_application_launch,im_v2_lib_call,im_v2_lib_utils) {
	'use strict';

	const SLIDER_PREFIX = 'im:slider';
	const BASE_STACK_INDEX = 1200;
	const SLIDER_CONTAINER_CLASS = 'bx-im-messenger__slider';
	const LOADER_CHATS_PATH = '/bitrix/js/im/v2/lib/slider/src/images/loader-chats.svg?v2';
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
	    this.instances = {};
	    this.sidePanelManager = BX.SidePanel.Instance;
	    this.v2enabled = false;
	    im_v2_lib_logger.Logger.warn('Slider: class created');
	    this.initSettings();
	    this.bindEvents();
	  }
	  openChat(dialogId = '', text = '') {
	    if (main_core.Type.isNumber(dialogId)) {
	      dialogId = dialogId.toString();
	    }
	    return this.openSlider().then(() => {
	      this.store.dispatch('application/setLayout', {
	        layoutName: im_v2_const.Layout.chat.name,
	        entityId: dialogId
	      }).then(() => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenChat, {
	          dialogId
	        });
	      });
	    });
	  }
	  openLines() {
	    return new Promise((resolve, reject) => {
	      BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('IM_LIB_SLIDER_LINES_NOT_IMPLEMENTED'),
	        position: "top-right",
	        autoHideDelay: 10000
	      });
	      reject('Messenger: lines is not implemented yet');
	    });
	  }
	  openNotifications() {
	    return this.openSlider().then(() => {
	      this.store.dispatch('application/setLayout', {
	        layoutName: im_v2_const.Layout.notification.name
	      }).then(() => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.layout.onOpenNotifications);
	      });
	    });
	  }
	  openRecentSearch() {
	    return this.openSlider().then(() => {
	      this.store.dispatch('application/setLayout', {
	        layoutName: im_v2_const.Layout.chat.name
	      });
	    }).then(() => {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.recent.openSearch);
	    });
	  }
	  openSettings(selected = '', section = '') {
	    im_v2_lib_logger.Logger.warn('Slider: onOpenSettings', selected, section);
	    return new Promise((resolve, reject) => {
	      BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('IM_LIB_SLIDER_SETTINGS_NOT_IMPLEMENTED'),
	        position: "top-right",
	        autoHideDelay: 10000
	      });
	      reject('Messenger: settings is not implemented yet');
	    });
	  }
	  startVideoCall(dialogId = '', withVideo = true) {
	    im_v2_lib_logger.Logger.warn('Slider: onStartVideoCall', dialogId, withVideo);
	    if (!im_v2_lib_utils.Utils.dialog.isDialogId(dialogId)) {
	      im_v2_lib_logger.Logger.error('Slider: onStartVideoCall - dialogId is not correct', dialogId);
	      return false;
	    }
	    return new Promise(resolve => {
	      im_v2_lib_call.CallManager.getInstance().startCall(dialogId, withVideo);
	      resolve();
	    });
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
	    this.launchMessengerApplication();
	    return new Promise(resolve => {
	      if (this.isFocused()) {
	        return resolve();
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
	      this.application = application;
	      this.store = this.application.controller.store;
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
	    this.openChat();
	  }
	  onCloseByEsc({
	    data: event
	  }) {
	    [event] = event;
	    const sliderId = event.getSlider().getUrl().toString();
	    if (!sliderId.startsWith(SLIDER_PREFIX)) {
	      return false;
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
	      return false;
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
	    return !!slider.getUrl().toString().startsWith(SLIDER_PREFIX);
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
	MessengerSlider.instance = null;

	exports.MessengerSlider = MessengerSlider;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=slider.bundle.js.map
