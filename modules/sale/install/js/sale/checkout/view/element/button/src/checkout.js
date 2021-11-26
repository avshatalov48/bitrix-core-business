import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-checkout', {
	props: ['title', 'wait'],
	methods:
		{
			checkout()
			{
				EventEmitter.emit(EventType.element.buttonCheckout);
			}
		},
	computed:
		{
			getObjectClass()
			{
				const classes = [
					'btn',
					'btn-primary',
					'product-item-buy-button',
					'rounded-pill'
				];

				if(this.wait)
				{
					classes.push('btn-wait')
				}
				return classes;
			}
		},
	// language=Vue
	template: `
		<div class="checkout-btn-container" @click="checkout">
			<button :class="getObjectClass" >{{title}}</button>
		</div>
	`
});