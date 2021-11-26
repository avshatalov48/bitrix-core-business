import { BitrixVue } from 'ui.vue';

import './row'

BitrixVue.component('sale-checkout-view-product-list', {
	props: ['items', 'mode', 'errors'],
	methods:
	{
		getError(index)
		{
			let error = this.errors.find(error => error.index === index);
			return typeof error !== 'undefined' ? error.list:null
		}
	},
	// language=Vue
	template: `
		<div class="checkout-basket-item-inner">
			<sale-checkout-view-product-row v-for="(item, index) in items" :key="index"
											:item="item" :index="index" :mode="mode" :error="getError(index)" />
		</div>
	`
});
