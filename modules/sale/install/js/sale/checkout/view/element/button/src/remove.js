import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-remove', {
	props: ['index'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_'))
		},
	},
	methods:
	{
		remove()
		{
			EventEmitter.emit(EventType.basket.buttonRemoveProduct, {index: this.index});
		}
	},
	// language=Vue
	template: `
		<span class="checkout-basket-item-remove-btn checkout-basket-desktop-only" @click="remove">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_NAME}}</span>
	`
});