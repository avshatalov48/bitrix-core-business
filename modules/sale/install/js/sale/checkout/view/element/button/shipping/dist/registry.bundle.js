this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,main_core,sale_checkout_const,ui_vue,sale_checkout_view_mixins) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-element-button-shipping-link', {
	  props: ['url'],
	  methods: {
	    clickAction: function clickAction() {
	      document.location.href = this.url;
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"btn btn-checkout-order-status-link\" @click=\"clickAction\">\n\t  \t\t<slot name=\"link-title\"/>\n\t  </div>\n\t"
	});

	ui_vue.Vue.component('sale-checkout-view-element-button-shipping-button_to_checkout', {
	  mixins: [sale_checkout_view_mixins.MixinButtonWait],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.Vue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_'));
	    }
	  },
	  methods: {
	    clickAction: function clickAction() {
	      this.setWait();
	      main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.element.buttonShipping);
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-order-status-btn-container\" @click=\"clickAction\">\n\t\t\t<button :class=\"getObjectClass\">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_NAME_NOW}}</button>\n\t\t</div>\n\t"
	});

	ui_vue.Vue.component('sale-checkout-view-element-button-shipping-button', {
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

}((this.BX.Sale.Checkout.View.Element.Button = this.BX.Sale.Checkout.View.Element.Button || {}),BX,BX.Sale.Checkout.Const,BX,BX.Sale.Checkout.View.Mixins));
//# sourceMappingURL=registry.bundle.js.map
