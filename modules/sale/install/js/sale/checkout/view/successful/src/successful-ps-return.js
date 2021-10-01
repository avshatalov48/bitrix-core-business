import { BitrixVue } from 'ui.vue';
import { Url } from 'sale.checkout.lib';
import { CurrencyCore } from 'currency.currency-core';
import 'sale.checkout.view.element.button';

import './call'
import './property-list';

BitrixVue.component('sale-checkout-view-successful_ps_return', {
	props: ['items', 'order', 'total', 'config'],
	computed:
	{
		urlOrderDetail()
		{
			let path = Url.getCurrentUrl()
			return Url.addLinkParam(path, 'mode', 'detail')
		},
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_'))
		},
		priceFormatted()
		{
			return CurrencyCore.currencyFormat(this.total.price, this.total.currency, true);
		},
	},
	// language=Vue
	template: `
      <div class="checkout-order-status-successful">
		  <svg class="checkout-order-status-icon" width="106" height="106" viewBox="0 0 106 106" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle class="checkout-order-status-icon-circle" stroke-width="3" cx="53" cy="53" r="51"/>
			<path class="checkout-order-status-icon-angle" fill="#fff" fill-rule="evenodd" clip-rule="evenodd" d="M45.517 72L28.5 55.4156L34.4559 49.611L45.517 60.3909L70.5441 36L76.5 41.8046L45.517 72Z"/>
		  </svg>
	
		  <div class="checkout-order-status-text">
		  	{{localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_PAYED}}
		  </div>
	
		  <sale-checkout-view-successful-property_list :items="items" :order="order">
			<template v-slot:block-1><div v-html="localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_PAYED_1.replace('#AMOUNT#', priceFormatted)"/></template>
		  </sale-checkout-view-successful-property_list>
          <sale-checkout-view-element-button-shipping-button :url="config.mainPage">
            <template v-slot:button-title>{{localize.CHECKOUT_VIEW_SUCCESSFUL_ELEMENT_BUTTON_SHIPPING_TO_SHOP}}</template>
          </sale-checkout-view-element-button-shipping-button>
          <sale-checkout-view-element-button-shipping-link :url="urlOrderDetail">
            <template v-slot:link-title>{{localize.CHECKOUT_VIEW_SUCCESSFUL_ELEMENT_BUTTON_SHIPPING_TO_ORDER}}</template>
          </sale-checkout-view-element-button-shipping-link>
      </div>
	`
});