/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,main_core_events,im_v2_lib_logger,im_v2_lib_layout,im_v2_const) {
	'use strict';

	// @vue/component
	const OpenlinesLoader = {
	  data() {
	    return {};
	  },
	  template: `
		<div class="bx-im-content-openlines__loader">
			<div class="bx-im-content-openlines__loader_spinner"></div>
		</div>
	`
	};

	const IFRAME_PATH = '/desktop_app/';
	const IFRAME_PARAMS = {
	  IFRAME: 'Y',
	  IM_BACKGROUND: 'light',
	  IM_LINES: 'Y',
	  IM_MENU: 'N',
	  IM_STATUS: 'N',
	  IM_V2_LAYOUT: 'Y'
	};

	// @vue/component
	const OpenlinesContent = {
	  name: 'OpenlinesContent',
	  components: {
	    OpenlinesLoader
	  },
	  props: {
	    entityId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      isLoading: true
	    };
	  },
	  computed: {
	    iframeLink() {
	      const params = new URLSearchParams(IFRAME_PARAMS);
	      return `${IFRAME_PATH}?${params.toString()}`;
	    },
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    }
	  },
	  watch: {
	    layout: {
	      handler(newLayout, prevLayout) {
	        if (newLayout.name !== im_v2_const.Layout.openlines.name) {
	          return;
	        }
	        if (this.dialogIdChangedFromFrame) {
	          this.dialogIdChangedFromFrame = false;
	          return;
	        }
	        if (!newLayout.entityId || newLayout.entityId === prevLayout.entityId) {
	          return;
	        }
	        this.sendOpenEvent();
	      },
	      flush: 'post'
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Content: Openlines created');
	    this.subscribeToEvents();
	  },
	  beforeUnmount() {
	    this.unsubscribeEvents();
	  },
	  methods: {
	    subscribeToEvents() {
	      main_core.Event.bind(window, im_v2_const.EventType.lines.onInit, this.onLinesInit);
	      main_core.Event.bind(window, im_v2_const.EventType.lines.onChatOpen, this.onLinesChatOpen);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.slider.onClose, this.unregisterSliderBindings);
	    },
	    unsubscribeEvents() {
	      main_core.Event.unbind(window, im_v2_const.EventType.lines.onInit, this.onLinesInit);
	      main_core.Event.unbind(window, im_v2_const.EventType.lines.onChatOpen, this.onLinesChatOpen);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.slider.onClose, this.unregisterSliderBindings);
	    },
	    async onLinesInit() {
	      if (!this.isLoading) {
	        return;
	      }
	      await this.$nextTick();
	      this.isLoading = false;
	      this.sendOpenEvent();
	      this.registerSliderBindings();
	    },
	    onLinesChatOpen(event) {
	      if (this.entityId === event.detail) {
	        return;
	      }
	      this.dialogIdChangedFromFrame = true;
	      void im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	        name: im_v2_const.Layout.openlines.name,
	        entityId: event.detail
	      });
	    },
	    sendOpenEvent() {
	      if (!this.entityId) {
	        return;
	      }
	      const openEvent = new CustomEvent(im_v2_const.EventType.lines.openChat, {
	        detail: this.entityId
	      });
	      this.$refs.frame.contentWindow.dispatchEvent(openEvent);
	    },
	    registerSliderBindings() {
	      var _BX$SidePanel$Instanc;
	      this.frameDocument = this.$refs.frame.contentDocument;
	      if ((_BX$SidePanel$Instanc = BX.SidePanel.Instance) != null && _BX$SidePanel$Instanc.registerAnchorListener) {
	        BX.SidePanel.Instance.registerAnchorListener(this.frameDocument);
	        return;
	      }
	      main_core.Event.bind(this.frameDocument, 'click', BX.SidePanel.Instance.handleAnchorClick, {
	        capture: true
	      });
	    },
	    unregisterSliderBindings() {
	      var _BX$SidePanel$Instanc2;
	      if (!this.frameDocument) {
	        return;
	      }
	      if ((_BX$SidePanel$Instanc2 = BX.SidePanel.Instance) != null && _BX$SidePanel$Instanc2.unregisterAnchorListener) {
	        BX.SidePanel.Instance.unregisterAnchorListener(this.frameDocument);
	        return;
	      }
	      main_core.Event.unbind(this.frameDocument, 'click', BX.SidePanel.Instance.handleAnchorClick, {
	        capture: true
	      });
	    }
	  },
	  template: `
		<div class="bx-im-content-openlines__container">
			<iframe class="bx-im-content-openlines__iframe" :src="iframeLink" ref="frame" />
			<OpenlinesLoader v-if="isLoading" />
		</div>
	`
	};

	exports.OpenlinesContent = OpenlinesContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=openlines-content.bundle.js.map
