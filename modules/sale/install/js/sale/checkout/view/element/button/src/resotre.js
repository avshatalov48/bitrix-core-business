import { BitrixVue } from 'ui.vue';
import { Event } from 'main.core'
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-restore', {
	props: ['index'],
	computed:
	{
		localize() {
			return Object.freeze(
				BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_ELEMENT_BUTTON_RESTORE_'))
		},
	},
	methods:
	{
		restore()
		{
			Event.EventEmitter.emit(EventType.basket.buttonRestoreProduct, {index: this.index});
		}
	},
	// language=Vue
	template: `
		<div class="checkout-item-resotre-block" @click="restore">
			<button class="checkout-resotre-btn btn btn-sm border rounded-pill">{{localize.CHECKOUT_VIEW_ELEMENT_BUTTON_RESTORE_NAME}}</button>
		</div>
	`
});