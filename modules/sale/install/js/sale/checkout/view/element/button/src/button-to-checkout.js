import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';
import { MixinButtonWait } from 'sale.checkout.view.mixins';

BitrixVue.component('sale-checkout-view-element-button-shipping-button_to_checkout', {
	mixins:[MixinButtonWait],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_'))
		}
	},
	methods:
	{
		clickAction()
		{
			this.setWait();
			
			EventEmitter.emit(EventType.element.buttonShipping);
		}
	},
	// language=Vue
	template: `
		<div class="checkout-order-status-btn-container" @click="clickAction">
			<button :class="getObjectClass">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_SHIPPING_NAME_NOW}}</button>
		</div>
	`
});