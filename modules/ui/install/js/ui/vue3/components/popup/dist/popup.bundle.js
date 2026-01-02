/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue3 = this.BX.UI.Vue3 || {};
(function (exports,main_core,main_popup) {
	'use strict';

	class SidePanelIntegration {
	  constructor(popup) {
	    this.popup = null;
	    this.sliders = new Set();
	    this.popup = popup;
	    this.getPopup().subscribe('onShow', this.onPopupShow.bind(this));
	    this.getPopup().subscribe('onClose', this.onPopupClose.bind(this));
	    this.getPopup().subscribe('onDestroy', this.onPopupClose.bind(this));
	    this.handleSliderOpen = this.handleSliderOpen.bind(this);
	    this.handleSliderClose = this.handleSliderClose.bind(this);
	    this.handleSliderDestroy = this.handleSliderDestroy.bind(this);
	  }
	  bindEvents() {
	    this.unbindEvents();
	    if (top.BX) {
	      top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
	      top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
	      top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.handleSliderDestroy);
	    }
	  }
	  unbindEvents() {
	    if (top.BX) {
	      top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
	      top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
	      top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onDestroy', this.handleSliderDestroy);
	    }
	  }
	  onPopupShow() {
	    this.bindEvents();
	  }
	  onPopupClose() {
	    this.unbindEvents();
	    this.sliders.clear();
	    this.popup.unfreeze();
	  }
	  handleSliderOpen(event) {
	    const [sliderEvent] = event.getData();
	    const slider = sliderEvent.getSlider();
	    if (!this.isPopupInSlider(slider)) {
	      this.sliders.add(slider);
	      this.popup.freeze();
	    }
	  }
	  handleSliderClose(event) {
	    const [sliderEvent] = event.getData();
	    const slider = sliderEvent.getSlider();
	    this.sliders.delete(slider);
	    if (this.sliders.size === 0) {
	      this.popup.unfreeze();
	    }
	  }
	  handleSliderDestroy(event) {
	    const [sliderEvent] = event.getData();
	    const slider = sliderEvent.getSlider();
	    if (this.isPopupInSlider(slider)) {
	      this.unbindEvents();
	      this.getPopup().destroy();
	    } else {
	      this.sliders.delete(slider);
	      if (this.sliders.size === 0) {
	        this.popup.unfreeze();
	      }
	    }
	  }
	  isPopupInSlider(slider) {
	    if (slider.getFrameWindow()) {
	      return slider.getFrameWindow().document.contains(this.getPopup().getPopupContainer());
	    }
	    return slider.getContainer().contains(this.getPopup().getPopupContainer());
	  }
	  getPopup() {
	    return this.popup.getPopupInstance();
	  }
	}

	// @vue/component
	const Popup = {
	  props: {
	    id: {
	      type: String,
	      default: () => `ui-vue3-popup-${main_core.Text.getRandom()}`
	    },
	    options: {
	      /** @type PopupOptions */
	      type: Object,
	      default: null
	    }
	  },
	  emits: ['close'],
	  computed: {
	    popupContainer() {
	      return `#${this.popupOptions.id}`;
	    },
	    container() {
	      return this.getPopupInstance().getPopupContainer();
	    },
	    popupOptions() {
	      return {
	        ...this.defaultOptions,
	        ...this.options
	      };
	    },
	    defaultOptions() {
	      return {
	        id: this.id,
	        cacheable: false,
	        autoHide: true,
	        autoHideHandler: this.autoHideHandler,
	        closeByEsc: true,
	        animation: 'fading',
	        bindOptions: {
	          forceBindPosition: true
	        },
	        events: {
	          onPopupClose: this.closePopup,
	          onPopupDestroy: this.closePopup
	        }
	      };
	    }
	  },
	  created() {
	    new SidePanelIntegration(this);
	  },
	  beforeMount() {
	    this.getPopupInstance().show();
	  },
	  mounted() {
	    this.adjustPosition();
	    this.getPopupInstance().getContentContainer().remove();
	  },
	  beforeUnmount() {
	    var _this$instance$getPop;
	    this.popupHTML = (_this$instance$getPop = this.instance.getPopupContainer()) == null ? void 0 : _this$instance$getPop.innerHTML;
	  },
	  unmounted() {
	    var _this$instance;
	    if (this.popupHTML) {
	      this.instance.getPopupContainer().innerHTML = this.popupHTML;
	    }
	    (_this$instance = this.instance) == null ? void 0 : _this$instance.close();
	  },
	  methods: {
	    contains(element) {
	      var _this$container$conta;
	      return (_this$container$conta = this.container.contains(element)) != null ? _this$container$conta : false;
	    },
	    adjustPosition() {
	      this.getPopupInstance().adjustPosition(this.popupOptions.bindOptions);
	    },
	    freeze() {
	      this.getPopupInstance().setAutoHide(false);
	      this.getPopupInstance().setClosingByEsc(false);
	    },
	    unfreeze() {
	      this.getPopupInstance().setAutoHide(this.popupOptions.autoHide);
	      this.getPopupInstance().setClosingByEsc(this.popupOptions.closeByEsc);
	    },
	    getPopupInstance() {
	      if (!this.instance) {
	        var _PopupManager$getPopu;
	        (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(this.popupOptions.id)) == null ? void 0 : _PopupManager$getPopu.destroy();
	        this.instance = new main_popup.Popup(this.popupOptions);
	      }
	      return this.instance;
	    },
	    autoHideHandler({
	      target
	    }) {
	      const parentAutoHide = target !== this.container && !this.container.contains(target);
	      const isAhaMoment = target.closest('.popup-window-ui-tour');
	      return parentAutoHide && !isAhaMoment;
	    },
	    closePopup() {
	      this.$emit('close');
	    }
	  },
	  template: `
		<Teleport :to="popupContainer">
			<slot :adjustPosition="adjustPosition" :freeze="freeze" :unfreeze="unfreeze"></slot>
		</Teleport>
	`
	};

	exports.Popup = Popup;

}((this.BX.UI.Vue3.Components = this.BX.UI.Vue3.Components || {}),BX,BX.Main));
//# sourceMappingURL=popup.bundle.js.map
