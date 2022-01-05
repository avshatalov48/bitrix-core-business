import {Vue} from 'ui.vue';
import 'sale.checkout.view.element.button.resotre'
import 'sale.checkout.view.product.props-list'

Vue.component('sale-checkout-view-product-info_deleted', {
	props: ['item', 'index'],
	methods:
		{
			getSrc()
			{
				return encodeURI(this.item.product.picture)
			}
		},
	computed:
		{
			localize() {
				return Object.freeze(
					Vue.getFilteredPhrases('CHECKOUT_VIEW_PRODUCT_INFO_DELETED_'))
			},
		},
	// language=Vue
	template: `
		<div class="checkout-item-info">
			<div class="checkout-item-image-block">
				<img :src="getSrc()" alt="" class="checkout-item-image">
			</div>
			
			<div class="checkout-item-name-block">
				<h2 class="checkout-item-name">{{item.name}}</h2>
				<sale-checkout-view-product-props_list :list="item.props"/>
			</div>
			
			<div class="checkout-item-deleted-block">
				<div class="checkout-item-deleted-text">{{localize.CHECKOUT_VIEW_PRODUCT_INFO_DELETED_WAS_DELETED}}</div>
			</div>
			
			<sale-checkout-view-element-button-restore :index="index"/>
			
		</div>
	`
});