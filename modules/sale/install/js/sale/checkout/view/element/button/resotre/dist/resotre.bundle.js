this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
this.BX.Sale.Checkout.View.Element = this.BX.Sale.Checkout.View.Element || {};
(function (exports,ui_vue,main_core,sale_checkout_const) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-element-button-restore', {
	  props: ['index'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.Vue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_RESTORE_'));
	    }
	  },
	  methods: {
	    resotre: function resotre() {
	      main_core.Event.EventEmitter.emit(sale_checkout_const.EventType.basket.buttonRestoreProduct, {
	        index: this.index
	      });
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-resotre-block\" @click=\"resotre\">\n\t\t\t<button class=\"checkout-resotre-btn btn btn-sm border rounded-pill\">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_RESTORE_NAME}}</button>\n\t\t</div>\n\t"
	});

}((this.BX.Sale.Checkout.View.Element.Button = this.BX.Sale.Checkout.View.Element.Button || {}),BX,BX,BX.Sale.Checkout.Const));
//# sourceMappingURL=resotre.bundle.js.map
