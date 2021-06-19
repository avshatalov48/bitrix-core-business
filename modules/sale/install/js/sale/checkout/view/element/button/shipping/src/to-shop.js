import { Vue } from 'ui.vue';
import { Event } from 'main.core'
import { EventType } from 'sale.checkout.const';

Vue.component('sale-checkout-view-element-button-shipping-to_shop', {
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
      <div class="btn btn-checkout-order-status-link" @click="clickAction">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_BUY}}</div>
	`
});