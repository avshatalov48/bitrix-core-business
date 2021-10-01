import { BitrixVue } from 'ui.vue';
import { EventEmitter } from "main.core.events";
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-backdrop_sku_change', {
	props: ['index'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'))
		},
	},
	methods:
	{
		click()
		{
			EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
		},
	},
	// language=Vue
	template: `
      <div class="checkout-basket-item-change-confirm-btn-container">
      	<button class="product-item-detail-buy-button btn btn-primary rounded-pill" @click="click">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_SAVE}}</button>
      </div>
	`
});