import { BitrixVue } from 'ui.vue';
import { EventEmitter } from "main.core.events";
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-backdrop_close', {
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
			document.body.style.overflowY = '';
			EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
		},
	},
	// language=Vue
	template: `
      <div class="checkout-basket-item-detail-close-btn-container" @click="click">
		  <span class="checkout-basket-item-detail-close-btn" id="bx_3966226736_424_7e1b8e3524755c391129a9d7e6f2d206_prebuy_close_btn">
			<span class="checkout-basket-item-detail-close-btn-text" >{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_CLOSE}}</span>
		  </span>
      </div>
	`
});
