import { Vue } from 'ui.vue';
import { Event } from 'main.core'
import { EventType } from 'sale.checkout.const';
import { MixinButtonWait } from 'sale.checkout.view.mixins';

Vue.component('sale-checkout-view-element-button-shipping-button_to_checkout', {
	mixins:[MixinButtonWait],
	computed:
	{
		localize() {
			return Object.freeze(
				Vue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_'))
		}
	},
	methods:
	{
		clickAction()
		{
			this.setWait();
			
			Event.EventEmitter.emit(EventType.element.buttonShipping);
		}
	},
	// language=Vue
	template: `
		<div class="checkout-order-status-btn-container" @click="clickAction">
			<button :class="getObjectClass">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_NAME_NOW}}</button>
		</div>
	`
});