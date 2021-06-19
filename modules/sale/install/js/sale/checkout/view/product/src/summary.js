import { Vue } from 'ui.vue';
import { CurrencyCore } from 'currency.currency-core';
import { EventEmitter } from "main.core.events";
import { EventType, Application } from 'sale.checkout.const';

Vue.component('sale-checkout-view-product-summary', {
	props: ['total', 'mode'],
	methods:
	{
		hasDiscount()
		{
			return this.total.discount.sum !== 0;
		},
		backdropTotalOpen()
		{
			EventEmitter.emit(EventType.basket.backdropTotalOpen)
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
		},
		getConstMode()
		{
			return Application.mode
		}
	},
	// language=Vue
	template: `
      <div class="checkout-basket-summary">
		  <div class="checkout-basket-summary-text">{{localize.CHECKOUT_VIEW_SUMMARY_BASKET_ITEMS}}</div>
		  <div class="checkout-item-price-block">
				<div class="checkout-item-price-discount-container" v-if="hasDiscount()">
				  <span class="checkout-item-price-discount" v-html="basePriceFormatted"></span>
				  <span class="checkout-item-price-discount-diff" v-html="'-' + discountSumFormatted"></span>
				</div>
			<span class="checkout-item-price" v-html="priceFormatted"></span>
		  </div>
          <template v-if="mode === getConstMode.view">
            <div class="d-block w-100 text-right">
              <span class="checkout-basket-total-backdrop-btn checkout-basket-mobile-only" @click="backdropTotalOpen">{{localize.CHECKOUT_VIEW_SUMMARY_DETAILS}}</span>
            </div>
		  </template>
      </div>
	`
});