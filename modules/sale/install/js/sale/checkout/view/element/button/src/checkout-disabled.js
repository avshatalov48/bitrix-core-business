import { BitrixVue } from 'ui.vue';

BitrixVue.component('sale-checkout-view-element-button-checkout_disabled', {
	props: ['title'],
	computed:
		{
			getObjectClass()
			{
				const classes = [
					'btn',
					'btn-primary',
					'product-item-detail-buy-button',
					'btn-lg',
					'rounded-pill'
				];
				return classes;
			}
		},
	// language=Vue
	template: `
		<div class="checkout-btn-container">
			<button :disabled="true" :class="getObjectClass" >{{title}}</button>
		</div>
	`
});