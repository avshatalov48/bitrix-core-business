this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,ui_vue,main_core,sale_checkout_const) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-element-button-plus', {
	  props: ['index'],
	  methods: {
	    plus: function plus() {
	      main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonPlusProduct, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-quantity-btn-plus no-select\" @click=\"plus\"/>\n\t"
	});

}((this.BX.Sale.Checkout.View.Element.Button = this.BX.Sale.Checkout.View.Element.Button || {}),BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=plus.bundle.js.map
