import { BitrixVue } from 'ui.vue';
import { Application, Loader as LoaderConst } from 'sale.checkout.const';
import { MixinLoader } from "sale.checkout.view.mixins";

import './list'
import './summary'

BitrixVue.component('sale-checkout-view-product', {
	props: ['items', 'total', 'mode', 'errors', 'config'],
	mixins:[MixinLoader],
	computed:
	{
		isLocked()
		{
			return this.config.status === LoaderConst.status.wait
		},
		getObjectClass()
		{
			const classes = [
				'checkout-basket-list-items',
				'checkout-table'
			];

			if(this.mode === Application.mode.view)
			{
				classes.push('checkout-basket-list-items-view-mode');
			}

			if(this.isLocked)
			{
				classes.push('checkout-basket-item-locked');
			}

			return classes;
		}
	},
	// language=Vue
	template: `
    	<div :class="getObjectClass" ref="container">
			<sale-checkout-view-product-list :items="items" :errors="errors" :mode="mode"/>
			<sale-checkout-view-product-summary :total="total" :mode="mode"/>
		</div>
	`
});
