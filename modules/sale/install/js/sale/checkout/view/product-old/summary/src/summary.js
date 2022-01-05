import {Vue} from 'ui.vue';
import {CurrencyCore} from 'currency.currency-core';

import './summary.css'

Vue.component('sale-checkout-view-product-summary', {
	props: ['total'],
	methods:
		{
			hasDiscount()
			{
				return this.total.discount.sum !== 0;
			}
		},
	computed:
		{
			localize() {
				return Object.freeze(
					Vue.getFilteredPhrases('CHECKOUT_VIEW_SUMMARY_'))
			},
			priceFormatted()
			{
				return CurrencyCore.currencyFormat(this.total.price, this.total.currency, true);
			},
			basePriceFormatted()
			{
				return CurrencyCore.currencyFormat(this.total.basePrice, this.total.currency, true);
			},
			discountSumFormatted()
			{
				return CurrencyCore.currencyFormat(this.total.discount.sum, this.total.currency, true);
			}
		},
	// language=Vue
	template: `
		<tbody>
		<tr class="checkout-item-summary">
			<td>
				<div class="checkout-summary">
					<span>{{localize.CHECKOUT_VIEW_SUMMARY_BASKET_ITEMS}}</span>
				</div>
			</td>
			<td>
				<div class="checkout-item-price-block">
					<div v-if="hasDiscount()"
						 class="checkout-item-price-discount-container">
						<span class="checkout-item-price-discount" v-html="basePriceFormatted"></span>
						<span class="checkout-item-price-discount-diff" v-html="'-' + discountSumFormatted"></span>
					</div>
					<span class="checkout-item-price" v-html="priceFormatted"></span>
				</div>
			</td>
		</tr>
		</tbody>
	`
});