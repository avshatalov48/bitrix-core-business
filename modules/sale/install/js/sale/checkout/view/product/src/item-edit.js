import { BitrixVue } from 'ui.vue';
import { MixinProductItemEdit } from 'sale.checkout.view.mixins';

import 'sale.checkout.view.element.input';

import './price'
import './props-list'
import './item-warning-list'

BitrixVue.component('sale-checkout-view-product-item_edit', {
	props: ['item', 'index', 'error'],
	mixins:[MixinProductItemEdit],
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
						<sale-checkout-view-product-props_list :list="item.props" v-if="hasProps()"/>
						<div class="checkout-basket-desktop-only">
							<sale-checkout-view-product-sku_tree :tree="item.sku.tree" :index="index" v-if="hasSkyTree()"/>
						</div>
						<slot name="button-change-sku"/>
						<sale-checkout-view-product-item_warning_list :list="error"/>
					</div>
				</div>
			</div>
			<div class="checkout-table-td">
				<div class="checkout-basket-item-summary-info">
					<div class="checkout-item-quantity-block">
						<div class="checkout-item-quantity-field-container">
							<slot name="button-minus"/>
							<div class="checkout-item-quantity-field-block">
								<sale-checkout-view-element-input-product_item_quantity :item="item" :index="index"/>
								<div class="checkout-item-quantity-field">{{item.quantity}}</div>
							</div>
							<slot name="button-plus"/>
							<slot name="quantity-description"/>
						</div>
					</div>
					<sale-checkout-view-product-price :item="item"/>
					<slot name="button-remove"/>
				</div>
			</div>
		</div>
	`
});
