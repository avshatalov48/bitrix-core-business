/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_application_core,im_v2_const,im_v2_lib_logger,im_v2_application_launch,im_v2_lib_desktop,im_v2_lib_layout) {
	'use strict';

	const SLIDER_PREFIX = 'im:slider';
	const BASE_STACK_INDEX = 1200;
	const SLIDER_CONTAINER_CLASS = 'bx-im-messenger__slider';
	const LOADER_CHATS_PATH = '/bitrix/js/im/v2/lib/slider/src/images/loader-chats.svg?v3';
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
	    im_v2_lib_logger.Logger.warn('Slider: class created');
	    this.bindEvents();
	    this.store = im_v2_application_core.Core.getStore();
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onCloseByEsc', this.onCloseByEsc.bind(this));
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', this.onClose.bind(this));
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.onDestroy.bind(this));
	    main_core.Event.ready(this.initZIndex.bind(this));
	    return true;
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
	    void this.launchMessengerApplication();
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
	          onOpenComplete: async event => {
	            await this.initMessengerComponent();
	            event.slider.closeLoader();
	            return resolve();
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
	  async initMessengerComponent() {
	    const application = await this.applicationPromise;
	    return application.initComponent(`.${SLIDER_CONTAINER_CLASS}`);
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
	    im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.chat.name
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
	MessengerSlider.instance = null;

	exports.MessengerSlider = MessengerSlider;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=slider.bundle.js.map
