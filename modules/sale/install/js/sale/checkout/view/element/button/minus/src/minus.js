import {Vue} from 'ui.vue';
import {Event} from 'main.core'
import {EventType} from 'sale.checkout.const';

Vue.component('sale-checkout-view-element-button-minus', {
	props: ['index'],
	methods:
		{
			minus()
			{
				Event.EventEmitter.emit(EventType.basket.buttonMinusProduct, {index: this.index});
			}
		},
	// language=Vue
	template: `
		<div class="checkout-item-quantity-btn-minus no-select" @click="minus"/>
	`
});