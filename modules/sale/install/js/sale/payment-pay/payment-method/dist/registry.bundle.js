this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale['Payment-Pay'] = this.BX.Sale['Payment-Pay'] || {};
(function (exports,ui_vue) {
	'use strict';

	ui_vue.Vue.component('sale-payment_pay-payment_method-list', {
	  props: ['items'],
	  data: function data() {
	    return {
	      list: []
	    };
	  },
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.Vue.getFilteredPhrases('PAYMENT_PAY_PAYMENT_METHOD_'));
	    }
	  },
	  methods: {
	    showDescription: function showDescription(item) {
	      item.SHOW_DESCRIPTION = item.SHOW_DESCRIPTION === 'Y' ? 'N' : 'Y';
	    },
	    isShow: function isShow(item) {
	      return item.SHOW_DESCRIPTION === 'Y';
	    },
	    getLogoSrc: function getLogoSrc(item) {
	      return item.LOGOTIP ? item.LOGOTIP : '/bitrix/js/sale/payment-pay/payment-method/images/default_logo.png';
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-basket-section\">\n\t\t\t<h2 class=\"checkout-basket-title\">{{localize.PAYMENT_PAY_PAYMENT_METHOD_1}}</h2>\n\t\t\t<div class=\"checkout-basket-pay-method-list\">\n\t\t\t\t<div class=\"checkout-basket-pay-method-item-container\" v-for=\"(item, index) in items\">\n\t\t\t\t\t<div class=\"checkout-basket-pay-method-item-logo-block\">\n\t\t\t\t\t\t<div class=\"checkout-basket-pay-method-logo\" :style=\"'background-image: url(\\'' + getLogoSrc(item) + '\\')'\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"checkout-basket-pay-method-text-block\">\n\t\t\t\t\t\t<div class=\"checkout-basket-pay-method-text\">{{item.NAME}}</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"checkout-basket-pay-method-btn-block\">\n\t\t\t\t\t\t<button class=\"checkout-checkout-btn-info border btn btn-sm rounded-pill\" @click='showDescription(item)'>{{localize.PAYMENT_PAY_PAYMENT_METHOD_2}}</button>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"checkout-basket-pay-method-description\" v-if=\"isShow(item)\">{{item.DESCRIPTION}}\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Sale['Payment-Pay']['Payment-Method'] = this.BX.Sale['Payment-Pay']['Payment-Method'] || {}),BX));
//# sourceMappingURL=registry.bundle.js.map
