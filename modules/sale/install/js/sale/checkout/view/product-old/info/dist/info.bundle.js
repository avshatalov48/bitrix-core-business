this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,ui_vue) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-product-info', {
	  props: ['item', 'index', 'config'],
	  computed: {
	    getSrc: function getSrc() {
	      return encodeURI(this.item.product.picture);
	    }
	  },
	  // language=Vue
	  template: " \n\t\t<div class=\"checkout-item-info\" style='position: relative;' ref=\"container\">\n\t\t\t<a :href=\"item.product.detailPageUrl\" class=\"checkout-item-image-block\">\n\t\t\t\t<img :src=\"getSrc\" alt=\"\" class=\"checkout-item-image\">\n\t\t\t</a>\n\t\t\t\n\t\t\t<div class=\"checkout-item-info-container\">\n\t\t\t\t<div class=\"checkout-item-info-block\">\n\t\t\t\t\t<h2 class=\"checkout-item-name\">\n\t\t\t\t\t\t<a :href=\"item.product.detailPageUrl\" class=\"checkout-item-name-link\" >{{item.name}}</a>\n\t\t\t\t\t</h2>\n\t\t\t\t\t<sale-checkout-view-product-props_list :list=\"item.props\"/>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div class=\"checkout-item-quantity-block\">\n\t\t\t\t<div class=\"checkout-item-quantity-field-container\">\n\t\t\t\t\t<slot name=\"button-minus\"/>\n\t\t\t\t\t<div class=\"checkout-item-quantity-field-block\">\n\t\t\t\t\t<input disabled class=\"checkout-item-quantity-field\" type=\"text\" inputmode=\"numeric\" :value=\"item.quantity\">\n\t\t\t\t\t</div>\n\t\t\t\t\t<slot name=\"button-plus\"/>\n\t\t\t\t</div>\n\t\t\t\t<span class=\"checkout-item-quantity-description\">\n\t\t\t\t\t<span class=\"checkout-item-quantity-description-text\">{{item.measureText}}</span>\n\t\t\t\t\t<span class=\"checkout-item-quantity-description-price\"></span>\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\t\t\n\t"
	});

}((this.BX.Sale.Checkout.View.Product = this.BX.Sale.Checkout.View.Product || {}),BX));
//# sourceMappingURL=info.bundle.js.map
