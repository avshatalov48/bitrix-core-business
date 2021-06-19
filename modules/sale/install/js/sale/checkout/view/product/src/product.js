import {Vue} from 'ui.vue';
import { Application } from 'sale.checkout.const';

import './list'
import './summary'

Vue.component('sale-checkout-view-product', {
	props: ['items', 'total', 'mode'],
	computed:
	{
		getObjectClass()
		{
			const classes = [
				'checkout-basket-list-items'
			];
			
			if(this.mode === Application.mode.view)
			{
				classes.push('checkout-basket-list-items-view-mode');
			}
			
			return classes;
		}
	},
	// language=Vue
	template: `
    	<div :class="getObjectClass">
			<sale-checkout-view-product-list :items="items" :mode="mode"/>
			<sale-checkout-view-product-summary :total="total" :mode="mode"/>
		</div>
	`
});