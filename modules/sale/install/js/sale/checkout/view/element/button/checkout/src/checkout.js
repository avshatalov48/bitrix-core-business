import {Vue} from 'ui.vue';
import {Event} from 'main.core'
import {EventType} from 'sale.checkout.const';
import './checkout.css'

Vue.component('sale-checkout-view-element-button-checkout', {
	props: ['title', 'wait'],
	methods:
		{
			checkout()
			{
				Event.EventEmitter.emit(EventType.element.buttonCheckout);
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