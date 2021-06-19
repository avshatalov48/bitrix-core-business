import { Vue } from 'ui.vue';
import { Event } from 'main.core'
import { EventType } from 'sale.checkout.const';

Vue.component('sale-checkout-view-element-button-shipping-button_to_shop', {
	props: ['url'],
	computed:
	{
		localize()
		{
			return Object.freeze(
				Vue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_'))
		},
	},
	methods:
		{
			clickAction()
			{
				document.location.href = this.url
			}
		},
	// language=Vue
	template: `
      <div class="checkout-order-status-btn-container" @click="clickAction">
      	<button class="btn btn-checkout-order-status btn-md rounded-pill">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_TO_SHOP}}</button>
      </div>
	`
});