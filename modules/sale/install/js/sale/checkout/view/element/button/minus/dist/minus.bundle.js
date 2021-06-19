this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,ui_vue,main_core,sale_checkout_const) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-element-button-minus', {
	  props: ['index'],
	  methods: {
	    minus: function minus() {
	      main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonMinusProduct, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-quantity-btn-minus no-select\" @click=\"minus\"/>\n\t"
	});

}((this.BX.Sale.Checkout.View.Element.Button = this.BX.Sale.Checkout.View.Element.Button || {}),BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=minus.bundle.js.map
