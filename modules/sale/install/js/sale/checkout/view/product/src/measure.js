import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-checkout-view-product-measure', {
	props: ['item'],
	// language=Vue
	template: `
		<span class="checkout-item-quantity-description">
			<span class="checkout-item-quantity-description-text">{{item.measureText}}</span>
			<span class="checkout-item-quantity-description-price"/>
		</span>
	`
});
