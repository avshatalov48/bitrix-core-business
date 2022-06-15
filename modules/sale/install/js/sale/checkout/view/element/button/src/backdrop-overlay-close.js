import { BitrixVue } from 'ui.vue';
import { EventEmitter } from "main.core.events";
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-backdrop_overlay_close', {
	props: ['index'],
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
      <div class="checkout-basket-item-backdrop-overlay" @click="click"/>
	`
});
