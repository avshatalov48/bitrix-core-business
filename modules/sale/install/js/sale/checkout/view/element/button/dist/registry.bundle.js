this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,sale_checkout_view_mixins,ui_vue,main_core_events,sale_checkout_const) {
	'use strict';

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-backdrop_close', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'));
	    }
	  },
	  methods: {
	    click: function click() {
	      document.body.style.overflowY = '';
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropClose, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-detail-close-btn-container\" @click=\"click\">\n\t\t  <span class=\"checkout-basket-item-detail-close-btn\" id=\"bx_3966226736_424_7e1b8e3524755c391129a9d7e6f2d206_prebuy_close_btn\">\n\t\t\t<span class=\"checkout-basket-item-detail-close-btn-text\" >{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_CLOSE}}</span>\n\t\t  </span>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-backdrop_overlay_close', {
	  props: ['index'],
	  methods: {
	    click: function click() {
	      document.body.style.overflowY = '';
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropClose, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-backdrop-overlay\" @click=\"click\"/>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-backdrop_remove_cancel', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'));
	    }
	  },
	  methods: {
	    click: function click() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropClose, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-cancel-btn-container\">\n      \t<button class=\"product-item-detail-cancel-button btn border border-dark rounded-pill\" @click=\"click\">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_CANCEL}}</button>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-backdrop_remove_remove', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'));
	    }
	  },
	  methods: {
	    click: function click() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonRemoveProduct, {
	        index: this.index
	      });
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropClose, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-remove-btn-container pt-2\">\n      \t<button class=\"product-item-detail-remove-button btn btn-danger rounded-pill\" @click=\"click\">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_REMOVE}}</button>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-backdrop_sku_change', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'));
	    }
	  },
	  methods: {
	    click: function click() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropClose, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-basket-item-change-confirm-btn-container\">\n      \t<button class=\"product-item-detail-buy-button btn btn-primary rounded-pill\" @click=\"click\">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_SAVE}}</button>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-shipping-button', {
	  props: ['url'],
	  mixins: [sale_checkout_view_mixins.MixinButtonWait],
	  methods: {
	    clickAction: function clickAction() {
	      this.setWait();
	      document.location.href = this.url;
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-order-status-btn-container\" @click=\"clickAction\">\n      \t<button :class=\"getObjectClass\">\n          <slot name=\"button-title\"/>\n\t\t</button>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-shipping-button_to_checkout', {
	  mixins: [sale_checkout_view_mixins.MixinButtonWait],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_'));
	    }
	  },
	  methods: {
	    clickAction: function clickAction() {
	      this.setWait();
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.element.buttonShipping);
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-order-status-btn-container\" @click=\"clickAction\">\n\t\t\t<button :class=\"getObjectClass\">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_NAME_NOW}}</button>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-checkout', {
	  props: ['title', 'wait'],
	  methods: {
	    checkout: function checkout() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.element.buttonCheckout);
	    }
	  },
	  computed: {
	    getObjectClass: function getObjectClass() {
	      var classes = ['btn', 'btn-primary', 'product-item-buy-button', 'rounded-pill'];

	      if (this.wait) {
	        classes.push('btn-wait');
	      }

	      return classes;
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-btn-container\" @click=\"checkout\">\n\t\t\t<button :class=\"getObjectClass\" >{{title}}</button>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-checkout_disabled', {
	  props: ['title'],
	  computed: {
	    getObjectClass: function getObjectClass() {
	      var classes = ['btn', 'btn-primary', 'product-item-detail-buy-button', 'btn-lg', 'rounded-pill'];
	      return classes;
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-btn-container\">\n\t\t\t<button :disabled=\"true\" :class=\"getObjectClass\" >{{title}}</button>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-item_change_sku', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'));
	    }
	  },
	  methods: {
	    backdropOpen: function backdropOpen() {
	      document.body.style.overflowY = 'hidden';
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropOpenChangeSku, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n        <div class=\"checkout-basket-mobile-only\">\n        \t<span class=\"checkout-basket-item-change-btn\" @click=\"backdropOpen\">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_ITEM_EDIT_CHANGE}}</span>\n        </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-item_mobile_menu', {
	  props: ['index'],
	  methods: {
	    backdropOpen: function backdropOpen() {
	      document.body.style.overflowY = 'hidden';
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropOpenMobileMenu, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n        <span class=\"checkout-basket-item-remove-dots-btn checkout-basket-mobile-only\" @click=\"backdropOpen\"/>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-shipping-link', {
	  props: ['url'],
	  methods: {
	    clickAction: function clickAction() {
	      document.location.href = this.url;
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"btn btn-checkout-order-status-link\" @click=\"clickAction\">\n\t  \t\t<slot name=\"link-title\"/>\n\t  </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-minus', {
	  props: ['index'],
	  methods: {
	    minus: function minus() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonMinusProduct, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-quantity-btn-minus no-select\" @click=\"minus\"/>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-plus', {
	  props: ['index'],
	  methods: {
	    plus: function plus() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonPlusProduct, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-quantity-btn-plus no-select\" @click=\"plus\"/>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-remove', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_'));
	    }
	  },
	  methods: {
	    remove: function remove() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonRemoveProduct, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<span class=\"checkout-basket-item-remove-btn checkout-basket-desktop-only\" @click=\"remove\">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_NAME}}</span>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-element-button-restore', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_RESTORE_'));
	    }
	  },
	  methods: {
	    restore: function restore() {
	      main_core_events.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonRestoreProduct, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-resotre-block\" @click=\"restore\">\n\t\t\t<button class=\"checkout-resotre-btn btn btn-sm border rounded-pill\">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_RESTORE_NAME}}</button>\n\t\t</div>\n\t"
	});

}((this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {}),BX.Sale.Checkout.View.Mixins,BX,BX.Event,BX.Sale.Checkout.Const));
//# sourceMappingURL=registry.bundle.js.map
