import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events';
import { EventType, Application } from 'sale.checkout.const';

import 'sale.checkout.view.element.animate-price'

BitrixVue.component('sale-checkout-view-product-summary', {
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
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_SUMMARY_'))
		},
		getConstMode()
		{
			return Application.mode
		}
	},
	// language=Vue
	template: `
		<div class="checkout-basket-summary-container">
			<!--region mobile -->
			<div class="checkout-basket-mobile-only checkout-basket-summary-discount checkout-summary-item-discount" v-if="hasDiscount()">
				<div class="checkout-basket-summary-text">{{localize.CHECKOUT_VIEW_SUMMARY_BASKET_PROFIT}}</div>
				<div class="checkout-item-price-block">
					<span class="checkout-summary-item-price-discount">
						<sale-checkout-view-element-animate_price :sum="this.total.discount.sum" :currency="this.total.currency" :prefix="'-'"/>
					</span>
				</div>
			</div>
			<!--endregion-->

			<!--region web-->
			<div class="checkout-basket-summary">
				<div class="checkout-basket-summary-text">{{localize.CHECKOUT_VIEW_SUMMARY_BASKET_ITEMS}}</div>
				<div class="checkout-item-price-block">
					<div class="checkout-item-price-discount-container checkout-basket-desktop-only" v-if="hasDiscount()">
						<span class="checkout-item-price-discount">
							<sale-checkout-view-element-animate_price :sum="this.total.basePrice" :currency="this.total.currency" />
						</span>
						<span class="checkout-item-price-discount-diff">
							<sale-checkout-view-element-animate_price :sum="this.total.discount.sum" :currency="this.total.currency" :prefix="'-'"/>
						</span>
					</div>
					<span class="checkout-item-price">
						<sale-checkout-view-element-animate_price :sum="this.total.price" :currency="this.total.currency" />
					</span>
				</div>
				<template v-if="mode === getConstMode.view">
					<div class="d-block w-100 text-right">
						<span class="checkout-basket-total-backdrop-btn checkout-basket-mobile-only" @click="backdropTotalOpen">{{localize.CHECKOUT_VIEW_SUMMARY_DETAILS}}</span>
					</div>
				</template>
			</div>
			<!--endregion-->
		</div>
	`
});
