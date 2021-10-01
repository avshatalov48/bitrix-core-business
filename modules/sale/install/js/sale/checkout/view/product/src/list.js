import { BitrixVue } from 'ui.vue';

import './row'

BitrixVue.component('sale-checkout-view-product-list', {
	props: ['items', 'mode'],
	// language=Vue
	template: `
		<div>
			<sale-checkout-view-product-row v-for="(item, index) in items" :key="index"
											:item="item" :index="index" :mode="mode" />
		</div>
	`
});