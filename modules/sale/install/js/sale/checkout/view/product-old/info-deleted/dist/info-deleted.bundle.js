this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,ui_vue) {
	'use strict';

	ui_vue.Vue.component('sale-checkout-view-product-info_deleted', {
	  props: ['item', 'index'],
	  methods: {
	    getSrc: function getSrc() {
	      return encodeURI(this.item.product.picture);
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.Vue.getFilteredPhrases('CHECKOUT_VIEW_PRODUCT_INFO_DELETED_'));
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-item-info\">\n\t\t\t<div class=\"checkout-item-image-block\">\n\t\t\t\t<img :src=\"getSrc()\" alt=\"\" class=\"checkout-item-image\">\n\t\t\t</div>\n\t\t\t\n\t\t\t<div class=\"checkout-item-name-block\">\n\t\t\t\t<h2 class=\"checkout-item-name\">{{item.name}}</h2>\n\t\t\t\t<sale-checkout-view-product-props_list :list=\"item.props\"/>\n\t\t\t</div>\n\t\t\t\n\t\t\t<div class=\"checkout-item-deleted-block\">\n\t\t\t\t<div class=\"checkout-item-deleted-text\">{{localize.CHECKOUT_VIEW_PRODUCT_INFO_DELETED_WAS_DELETED}}</div>\n\t\t\t</div>\n\t\t\t\n\t\t\t<sale-checkout-view-element-button-restore :index=\"index\"/>\n\t\t\t\n\t\t</div>\n\t"
	});

}((this.BX.Sale.Checkout.View.Product = this.BX.Sale.Checkout.View.Product || {}),BX));
//# sourceMappingURL=info-deleted.bundle.js.map
