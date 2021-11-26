import { BitrixVue } from 'ui.vue';
import { MixinProductItemEdit } from 'sale.checkout.view.mixins';

import 'sale.checkout.view.element.button'
import 'sale.checkout.view.element.input';

import './price'
import './props-list'
import './item-warning-list'

BitrixVue.component('sale-checkout-view-product-item_backdrop', {
	props: ['item', 'index', 'error'],
	mixins:[MixinProductItemEdit],
	// language=Vue
	template: `
		<div class="checkout-basket-item-backdrop-wrapper js-backdrop-open-change-sku" style="">
			<sale-checkout-view-element-button-backdrop_overlay_close class="js-backdrop-open-change-sku" :index="this.index"/>
			<div class="checkout-basket-item-backdrop-container js-backdrop-open-change-sku">
				<div class="checkout-basket-item-detail-header justify-content-between align-items-center">
					<div class="checkout-basket-item-detail-swipe-btn-container" id="bx_3966226736_424_7e1b8e3524755c391129a9d7e6f2d206_prebuy_swipe_btn">
						<div class="checkout-basket-item-detail-swipe-btn"/>
					</div>
				</div>
					<div class="checkout-basket-item-backdrop-inner">
					<div class="checkout-basket-item-backdrop-main">
						<div class="checkout-basket-item-head">
							<a :href="item.product.detailPageUrl" class="checkout-basket-item-image-block">
								<img :src="getSrc" :alt="item.name" class="checkout-basket-item-image">
							</a>
							<h2 class="checkout-basket-item-name-block">
								<a :href="item.product.detailPageUrl" class="checkout-basket-item-name-text">{{item.name}}</a>
							</h2>
						</div>
						<div class="checkout-basket-item-info-container" v-if="hasProps() || hasSkyTree()">
							<div class="checkout-basket-item-info-block">
								<sale-checkout-view-product-props_list :list="item.props" v-if="hasProps()"/>
								<sale-checkout-view-product-sku_tree :tree="item.sku.tree" :index="index" v-if="hasSkyTree()"/>
							</div>
							<sale-checkout-view-product-item_warning_list :list="error"/>
						</div>
					</div>
					<div class="checkout-basket-item-backdrop-bottom">
						<div class="checkout-basket-item-summary-info">
							<div class="checkout-item-quantity-block">
								<div class="checkout-item-quantity-field-container">
									<slot name="button-minus" />
									<div class="checkout-item-quantity-field-block">
										<sale-checkout-view-element-input-product_item_quantity :item="item" :index="index"/>
										<div class="checkout-item-quantity-field">{{item.quantity}}</div>
									</div>
									<slot name="button-plus" />
									<slot name="quantity-description" />
								</div>
							</div>
							<sale-checkout-view-product-price :item="item" :index="index" />
						</div>
						<sale-checkout-view-element-button-backdrop_sku_change :index="index"/>
					</div>
				</div>
			</div>
		</div>
	`
});
