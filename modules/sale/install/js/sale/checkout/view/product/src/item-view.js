import { BitrixVue } from 'ui.vue';

import './price'
import './props-list'

BitrixVue.component('sale-checkout-view-product-item_view', {
	props: ['item'],
	computed:
	{
		getSrc()
		{
			return encodeURI(this.item.product.picture)
		}
	},
	// language=Vue
	template: `
		<div class="checkout-table-row">
			<div class="checkout-table-td">
				<a :href="item.product.detailPageUrl" class="checkout-basket-item-image-block">
					<img :src="getSrc" :alt="item.name" class="checkout-basket-item-image">
				</a>
			</div>
			<div class="checkout-table-td">
				<div class="checkout-basket-item-info-container">
					<h2 class="checkout-basket-item-name-block">
						<a :href="item.product.detailPageUrl" class="checkout-basket-item-name-text">{{item.name}}</a>
					</h2>
					<div class="checkout-basket-item-info-block">
						<sale-checkout-view-product-props_list :list="item.props"/>
					</div>
				</div>
			</div>
			<div class="checkout-table-td">
				<div class="checkout-basket-item-summary-info">
					<div class="checkout-item-quantity-block">
						<div class="checkout-item-quantity-block-text">{{item.quantity}} {{item.measureText}}</div>
					</div>
					<sale-checkout-view-product-price :item="item"/>
				</div>
			</div>
		</div>
	`
});
