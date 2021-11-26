import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-plus', {
	props: ['index'],
	methods:
	{
		plus()
		{
			EventEmitter.emit(EventType.basket.buttonPlusProduct, {index: this.index});
		}
	},
	// language=Vue
	template: `
		<div class="checkout-item-quantity-btn-plus no-select" @click="plus"/>
	`
});