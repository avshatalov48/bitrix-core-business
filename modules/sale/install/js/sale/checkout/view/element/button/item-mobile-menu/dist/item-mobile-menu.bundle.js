this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,ui_vue,main_core,sale_checkout_const) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-element-button-item_mobile_menu', {
	  props: ['index'],
	  methods: {
	    backdropOpen: function backdropOpen() {
	      main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.backdropOpen, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n        <span class=\"checkout-basket-item-remove-dots-btn checkout-basket-mobile-only\" @click=\"backdropOpen\"/>\n\t"
	});

}((this.BX.Sale.Checkout.View.Element.Button = this.BX.Sale.Checkout.View.Element.Button || {}),BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=item-mobile-menu.bundle.js.map
