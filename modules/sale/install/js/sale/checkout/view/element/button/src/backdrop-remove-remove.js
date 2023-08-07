import { BitrixVue } from 'ui.vue';
import { EventEmitter } from "main.core.events";
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-backdrop_remove_remove', {
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
				EventEmitter.emit(EventType.basket.buttonRemoveProduct, {index: this.index});
				EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
			},
		},
	// language=Vue
	template: `
      <div class="checkout-basket-item-remove-btn-container pt-2">
      	<button class="product-item-detail-remove-button btn btn-danger rounded-pill" @click="click">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_REMOVE}}</button>
      </div>
	`
});