import {Vue} from 'ui.vue';
import {Event} from 'main.core'
import {EventType} from 'sale.checkout.const';

import './remove.css'

Vue.component('sale-checkout-view-element-button-remove', {
	props: ['index'],
	computed:
		{
			localize() {
				return Object.freeze(
					Vue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_'))
			},
		},
	methods:
		{
			remove()
			{
				Event.EventEmitter.emit(EventType.basket.buttonRemoveProduct, {index: this.index});
			}
		},
	// language=Vue
	template: `
		<span class="checkout-basket-item-remove-btn checkout-basket-desktop-only" @click="remove">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_REMOVE_NAME}}</span>
	`
});