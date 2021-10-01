import { BitrixVue } from 'ui.vue';
import "sale.checkout.view.element.button";

import './call'
import './property-list';

BitrixVue.component('sale-checkout-view-successful-without-ps', {
	props: ['items', 'order', 'config'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUCCESSFUL_'))
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
              {{localize.CHECKOUT_VIEW_SUCCESSFUL_STATUS_ORDER_CREATED}}
            </div>
			
			<sale-checkout-view-successful-property_list :items="items" :order="order"/>
			<sale-checkout-view-element-button-shipping-button :url="config.mainPage">
			  <template v-slot:button-title>{{localize.CHECKOUT_VIEW_SUCCESSFUL_ELEMENT_BUTTON_SHIPPING_TO_SHOP}}</template>
			</sale-checkout-view-element-button-shipping-button>
		</div>
`
});