import { BitrixVue } from 'ui.vue';
import { EventEmitter } from 'main.core.events'
import { EventType } from 'sale.checkout.const';

BitrixVue.component('sale-checkout-view-element-button-item_mobile_menu', {
	props: ['index'],
	methods:
	{
		backdropOpen()
		{
			document.body.style.overflowY = 'hidden';
			EventEmitter.emit(EventType.basket.backdropOpenMobileMenu, {index: this.index})
		}
	},
	// language=Vue
	template: `
        <span class="checkout-basket-item-remove-dots-btn checkout-basket-mobile-only" @click="backdropOpen"/>
	`
});
