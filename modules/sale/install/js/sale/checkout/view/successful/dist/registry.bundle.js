this.BX = this.BX || {};
this.BX.Sale = this.BX.Sale || {};
this.BX.Sale.Checkout = this.BX.Sale.Checkout || {};
this.BX.Sale.Checkout.View = this.BX.Sale.Checkout.View || {};
(function (exports,main_core,sale_checkout_lib,currency_currencyCore,ui_vue) {
	'use strict';

	ui_vue.BitrixVue.component('sale-checkout-view-successful-property_list', {
	  props: ['items', 'order'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_'));
	    },
	    getNumber: function getNumber() {
	      return main_core.Text.encode(this.order.accountNumber);
	    },
	    getTitle: function getTitle() {
	      var message = this.localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_TITLE;
	      return message.replace('#ORDER_NUMBER#', this.getNumber);
	    },
	    getPropertiesShort: function getPropertiesShort() {
	      var properties = [];

	      for (var propertyId in this.items) {
	        if (main_core.Type.isStringFilled(this.items[propertyId].value)) {
	          properties.push(this.items[propertyId].value);
	        }
	      }

	      return properties.join(', ');
	    }
	  },
	  template: "\n\t\t<div class=\"checkout-order-info\">\n\t\t\t<div>{{getTitle}}</div>\n\t\t\t<div>{{getPropertiesShort}}</div>\n\t\t\t<slot name=\"block-1\"/>\n\t\t</div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-successful-call', {
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_CALL_'));
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-order-common-container\">\n\t\t\t<div class=\"checkout-order-common-row\">\n\t\t\t\t<svg class=\"checkout-order-common-row-icon\" width=\"26\" height=\"27\" viewBox=\"0 0 26 27\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M12.9124 26.6569C20.0093 26.6569 25.7624 20.9038 25.7624 13.807C25.7624 6.71014 20.0093 0.957031 12.9124 0.957031C5.81561 0.957031 0.0625 6.71014 0.0625 13.807C0.0625 20.9038 5.81561 26.6569 12.9124 26.6569Z\" fill=\"white\"/>\n\t\t\t\t\t<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M10.8218 19.498L5.72461 14.5304L7.50861 12.7918L10.8218 16.0207L18.3182 8.71484L20.1022 10.4535L10.8218 19.498Z\" fill=\"#65A90F\"/>\n\t\t\t\t</svg>\n\t\t\t\t<div>{{localize.CHECKOUT_VIEW_SUCCESSFUL_CALL_MANAGER}}</div>\n\t\t\t</div>\n\t\t</div>\n"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-successful-without-ps', {
	  props: ['items', 'order', 'config'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_'));
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"checkout-order-status-successful\">\n\t\t\t<svg class=\"checkout-order-status-icon\" width=\"106\" height=\"106\" viewBox=\"0 0 106 106\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n             \t<circle class=\"checkout-order-status-icon-circle\" stroke-width=\"3\" cx=\"53\" cy=\"53\" r=\"51\"/>\n\t\t\t\t<path class=\"checkout-order-status-icon-angle\" fill=\"#fff\" fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M45.517 72L28.5 55.4156L34.4559 49.611L45.517 60.3909L70.5441 36L76.5 41.8046L45.517 72Z\"/>\n\t\t\t</svg>\n\t\t\t\n            <div class=\"checkout-order-status-text\">\n              {{localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_CREATED}}\n            </div>\n\t\t\t\n\t\t\t<sale-checkout-view-successful-property_list :items=\"items\" :order=\"order\"/>\n\t\t\t<sale-checkout-view-element-button-shipping-button :url=\"config.mainPage\">\n\t\t\t  <template v-slot:button-title>{{localize.CHECKOUT_VIEW_SUCCESSFUL_ELEMENT_BUTTON_SHIPPING_TO_SHOP}}</template>\n\t\t\t</sale-checkout-view-element-button-shipping-button>\n\t\t</div>\n"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-successful_ps_return', {
	  props: ['items', 'order', 'total', 'config'],
	  computed: {
	    urlOrderDetail: function urlOrderDetail() {
	      var path = sale_checkout_lib.Url.getCurrentUrl();
	      return sale_checkout_lib.Url.addLinkParam(path, 'mode', 'detail');
	    },
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_'));
	    },
	    priceFormatted: function priceFormatted() {
	      return currency_currencyCore.CurrencyCore.currencyFormat(this.total.price, this.total.currency, true);
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-order-status-successful\">\n\t\t  <svg class=\"checkout-order-status-icon\" width=\"106\" height=\"106\" viewBox=\"0 0 106 106\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t<circle class=\"checkout-order-status-icon-circle\" stroke-width=\"3\" cx=\"53\" cy=\"53\" r=\"51\"/>\n\t\t\t<path class=\"checkout-order-status-icon-angle\" fill=\"#fff\" fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M45.517 72L28.5 55.4156L34.4559 49.611L45.517 60.3909L70.5441 36L76.5 41.8046L45.517 72Z\"/>\n\t\t  </svg>\n\t\n\t\t  <div class=\"checkout-order-status-text\">\n\t\t  \t{{localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_PAYED}}\n\t\t  </div>\n\t\n\t\t  <sale-checkout-view-successful-property_list :items=\"items\" :order=\"order\">\n\t\t\t<template v-slot:block-1><div v-html=\"localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_PAYED_1.replace('#AMOUNT#', priceFormatted)\"/></template>\n\t\t  </sale-checkout-view-successful-property_list>\n          <sale-checkout-view-element-button-shipping-button :url=\"config.mainPage\">\n            <template v-slot:button-title>{{localize.CHECKOUT_VIEW_SUCCESSFUL_ELEMENT_BUTTON_SHIPPING_TO_SHOP}}</template>\n          </sale-checkout-view-element-button-shipping-button>\n          <sale-checkout-view-element-button-shipping-link :url=\"urlOrderDetail\">\n            <template v-slot:link-title>{{localize.CHECKOUT_VIEW_SUCCESSFUL_ELEMENT_BUTTON_SHIPPING_TO_ORDER}}</template>\n          </sale-checkout-view-element-button-shipping-link>\n      </div>\n\t"
	});

	ui_vue.BitrixVue.component('sale-checkout-view-successful', {
	  props: ['items', 'order', 'config'],
	  computed: {
	    localize: function localize() {
	      return Object.freeze(ui_vue.BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_'));
	    }
	  },
	  // language=Vue
	  template: "\n      <div class=\"checkout-order-status-successful\">\n\t\t  <svg class=\"checkout-order-status-icon\" width=\"106\" height=\"106\" viewBox=\"0 0 106 106\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t<circle class=\"checkout-order-status-icon-circle\" stroke-width=\"3\" cx=\"53\" cy=\"53\" r=\"51\"/>\n\t\t\t<path class=\"checkout-order-status-icon-angle\" fill=\"#fff\" fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M45.517 72L28.5 55.4156L34.4559 49.611L45.517 60.3909L70.5441 36L76.5 41.8046L45.517 72Z\"/>\n\t\t  </svg>\n\t\n\t\t  <div class=\"checkout-order-status-text\">\n\t\t  \t{{localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_CREATED}}\n\t\t  </div>\n\t\n\t\t  <sale-checkout-view-successful-property_list :items=\"items\" :order=\"order\"/>\n\t\t  <sale-checkout-view-element-button-shipping-button_to_checkout/>\n\t\t  <sale-checkout-view-element-button-shipping-link :url=\"config.mainPage\">\n\t\t\t<template v-slot:link-title>{{localize.CHECKOUT_VIEW_SUCCESSFUL_ELEMENT_BUTTON_SHIPPING_BUY}}</template>\n\t\t  </sale-checkout-view-element-button-shipping-link>\n      </div>\n\t"
	});

}((this.BX.Sale.Checkout.View.Successful = this.BX.Sale.Checkout.View.Successful || {}),BX,BX.Sale.Checkout.Lib,BX.Currency,BX));
//# sourceMappingURL=registry.bundle.js.map
