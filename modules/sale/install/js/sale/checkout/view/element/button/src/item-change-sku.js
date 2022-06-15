import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-item_change_sku', {
	props: ['index'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'))
		}
	},
	methods:
	{
		backdropOpen()
		{
			document.body.style.overflowY = 'hidden';
			EventEmitter.emit(EventType.basket.backdropOpenChangeSku, {index: this.index})
		}
	},
	// language=Vue
	template: `
        <div class="checkout-basket-mobile-only">
        	<span class="checkout-basket-item-change-btn" @click="backdropOpen">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_ITEM_EDIT_CHANGE}}</span>
        </div>
	`
});
