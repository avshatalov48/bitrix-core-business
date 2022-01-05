import {Vue} from 'ui.vue';

import 'sale.checkout.view.product.list';
import 'sale.checkout.view.product.summary';
import './product.css'

Vue.component('sale-checkout-view-product-old', {
	props: ['items', 'total', 'mode'],
	// language=Vue
	template: `
	<div class="checkout-item-list-container">
		<table class="checkout-item-list">
			<sale-checkout-view-product-list :items="items" :mode="mode"/>
			<sale-checkout-view-product-summary :total="total"/>
		</table>
	</div>
	`
});