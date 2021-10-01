this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,ui_vue) {
	'use strict';

	ui_vue.BitrixVue.component('sale-checkout-view-empty_cart', {
	  props: ['config'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_EMPTY_CART_'));
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-clear-page\">\n\t\t\t<div class=\"checkout-clear-page-image-container\">\n\t\t\t\t<img src=\"/bitrix/js/sale/checkout/images/empty_cart.svg?v=2\" alt=\"\">\n\t\t\t</div>\n\t\t\t<div class=\"checkout-clear-page-description\">\n\t\t\t\t{{localize.CHECKOUT_VIEW_EMPTY_CART_DESCRIPTION}}\n\t\t\t</div>\n\t\t\t<div class=\"checkout-clear-page-btn-container\">\n\t\t\t\t<a class=\"btn border border-dark btn-md rounded-pill pl-4 pr-4 w-100\" id=\"\" :href=\"config.path.emptyCart\">\n\t\t\t\t\t{{localize.CHECKOUT_VIEW_EMPTY_CART_START}}\n\t\t\t\t</a>\n\t\t\t</div>\n\t\t</div>\n"
	});

}((this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {}),BX));
//# sourceMappingURL=empty-cart.bundle.js.map
