import { BitrixVue } from 'ui.vue';
import { CurrencyCore } from 'currency.currency-core';

BitrixVue.component('sale-checkout-view-product-price_measure', {
	props: ['item'],
	computed:
		{
			priceFormatted()
			{
				return CurrencyCore.currencyFormat(this.item.price, this.item.currency, true);
			}
		},
	// language=Vue
	template: `
		<span class="checkout-item-quantity-description">
			<span class="checkout-item-quantity-description-text">
				<div v-html="priceFormatted + '/' + item.measureText"/>
			</span>
			<span class="checkout-item-quantity-description-price"/>
		</span>
	`
});
