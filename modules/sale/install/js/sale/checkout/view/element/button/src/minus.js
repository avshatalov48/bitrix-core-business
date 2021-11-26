import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-minus', {
	props: ['index'],
	methods:
	{
		minus()
		{
			EventEmitter.emit(EventType.basket.buttonMinusProduct, {index: this.index});
		}
	},
	// language=Vue
	template: `
		<div class="checkout-item-quantity-btn-minus no-select" @click="minus"/>
	`
});