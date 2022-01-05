this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,ui_vue) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-product-price', {
	  props: ['item'],
	  computed: {
	    hasDiscount: function hasDiscount() {
	      return this.item.discount.sum !== 0;
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-price-block\">\n\t\t\t<div v-if=\"hasDiscount\" \n\t\t\t\tclass=\"checkout-item-price-discount-container\">\n\t\t\t\t<span class=\"checkout-item-price-discount\">\n\t\t\t\t\t<sale-checkout-view-element-animate_price :sum=\"this.item.baseSum\" :currency=\"this.item.currency\" />\n\t\t\t\t</span>\n\t\t\t\t<span class=\"checkout-item-price-discount-diff\">\n\t\t\t\t\t<sale-checkout-view-element-animate_price :sum=\"this.item.discount.sum\" :currency=\"this.item.currency\" :prefix=\"'-'\" />\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t\t<span class=\"checkout-item-price\">\n\t\t\t\t<sale-checkout-view-element-animate_price :sum=\"this.item.sum\" :currency=\"this.item.currency\"/>\n\t\t\t</span>\n\t\t</div>\n\t"
	});

}((this.BX.Sale.Checkout.View.Product = this.BX.Sale.Checkout.View.Product || {}),BX));
//# sourceMappingURL=price.bundle.js.map
