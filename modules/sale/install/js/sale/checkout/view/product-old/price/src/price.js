import {Vue} from 'ui.vue';
import 'sale.checkout.view.element.animate-price'

Vue.component('sale-checkout-view-product-price', {
	props: ['item'],
	computed:
	{
		hasDiscount()
		{
			return this.item.discount.sum !== 0;
		}
	},
	// language=Vue
	template: `
		<div class="checkout-item-price-block">
			<div v-if="hasDiscount" 
				class="checkout-item-price-discount-container">
				<span class="checkout-item-price-discount">
					<sale-checkout-view-element-animate_price :sum="this.item.baseSum" :currency="this.item.currency" />
				</span>
				<span class="checkout-item-price-discount-diff">
					<sale-checkout-view-element-animate_price :sum="this.item.discount.sum" :currency="this.item.currency" :prefix="'-'" />
				</span>
			</div>
			<span class="checkout-item-price">
				<sale-checkout-view-element-animate_price :sum="this.item.sum" :currency="this.item.currency"/>
			</span>
		</div>
	`
});