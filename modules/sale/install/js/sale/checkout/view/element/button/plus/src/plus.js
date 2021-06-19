import {Vue} from 'ui.vue';
import {Event} from 'main.core'
import {EventType} from 'sale.checkout.const';

Vue.component('sale-checkout-view-element-button-plus', {
	props: ['index'],
	methods:
		{
			plus()
			{
				Event.EventEmitter.emit(EventType.basket.buttonPlusProduct, {index: this.index});
			}
		},
	// language=Vue
	template: `
		<div class="checkout-item-quantity-btn-plus no-select" @click="plus"/>
	`
});