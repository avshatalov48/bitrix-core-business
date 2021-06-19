this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,main_core,sale_checkout_const,ui_vue) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-element-button-checkout', {
	  props: ['title', 'wait'],
	  methods: {
	    checkout: function checkout() {
	      main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.element.buttonCheckout);
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

	ui_vue.Vue.component('sale-checkout-view-element-button-checkout_disabled', {
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

}((this.BX.Sale.Checkout.View.Element.Button = this.BX.Sale.Checkout.View.Element.Button || {}),BX,BX.Sale.Checkout.Const,BX));
//# sourceMappingURL=registry.bundle.js.map
