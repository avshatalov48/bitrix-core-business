this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
(function (exports,ui_vue) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-product-old', {
	  props: ['items', 'total', 'mode'],
	  // language=Vue
	  template: "\n\t<div class=\"checkout-item-list-container\">\n\t\t<table class=\"checkout-item-list\">\n\t\t\t<sale-checkout-view-product-list :items=\"items\" :mode=\"mode\"/>\n\t\t\t<sale-checkout-view-product-summary :total=\"total\"/>\n\t\t</table>\n\t</div>\n\t"
	});

}((this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {}),BX));
//# sourceMappingURL=product.bundle.js.map
