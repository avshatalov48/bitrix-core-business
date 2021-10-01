import { BitrixVue } from 'ui.vue';
import { EventType } from 'sale.checkout.const';
import { EventEmitter } from "main.core.events";

import './basket'
import './discount'
import './summary'

BitrixVue.component('sale-checkout-view-total', {
	props: ['total', 'showBackdrop'],
	computed:
	{
		localize()
		{
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_TOTAL_CLOSE'))
		},
		hasDiscount()
		{
			return this.total.discount.sum !== 0;
		},
		getObjectClass()
		{
			const classes = [
				'checkout-basket-total-container'
			];
			
			if(this.isBackdrop)
			{
				classes.push('active');
			}
			
			return classes;
		},
		isBackdrop()
		{
			return this.showBackdrop === 'Y';
		},
	},
	methods:
	{
		backdropTotalClose()
		{
			EventEmitter.emit(EventType.basket.backdropTotalClose)
		}
	},
	// language=Vue
	template: `
      <div :class="getObjectClass">
		  <div class="checkout-basket-total-overlay" @click="backdropTotalClose"></div>
          <div class="checkout-basket-total-backdrop-container">
            <div class="checkout-basket-total-backdrop-header justify-content-between align-items-center checkout-basket-mobile-only">
              <div class="checkout-basket-total-backdrop-header-separate"></div>
              <div class="checkout-basket-total-backdrop-swipe-btn-container">
                <div class="checkout-basket-total-backdrop-swipe-btn"></div>
              </div>
              <div class="checkout-basket-total-backdrop-close-btn-container" @click="backdropTotalClose">
				<span class="checkout-basket-total-backdrop-close-btn">
					<span class="checkout-basket-total-backdrop-close-btn-text" >{{localize.CHECKOUT_VIEW_TOTAL_CLOSE}}</span>
				</span>
              </div>
            </div>
			  <div class="checkout-basket-total-inner">
					<table class="checkout-basket-total-list">
						<sale-checkout-view-total-basket :total="total"/>
						<sale-checkout-view-total-discount :total="total" v-if="hasDiscount"/>
						<sale-checkout-view-total-summary :total="total"/>
					</table>
			  </div>
		  </div>
      </div>
	`
});