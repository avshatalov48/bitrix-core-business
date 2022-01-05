import {Vue} from 'ui.vue';

import './row'

Vue.component('sale-checkout-view-product-list', {
	props: ['items', 'mode'],
	// language=Vue
	template: `
		<tbody>
			<sale-checkout-view-product-row v-for="(item, index) in items" :key="index" 
											:item="item" :index="index" :mode="mode" />
		</tbody>
	`
});