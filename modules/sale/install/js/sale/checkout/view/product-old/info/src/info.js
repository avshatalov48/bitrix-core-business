import {Vue} from 'ui.vue';

import 'sale.checkout.view.element.button.remove'
import 'sale.checkout.view.element.button.plus'
import 'sale.checkout.view.element.button.minus'
import 'sale.checkout.view.product.props-list'

Vue.component('sale-checkout-view-product-info', {
	props: ['item', 'index', 'config'],
	computed:
	{
		getSrc()
		{
			return encodeURI(this.item.product.picture)
		}
	},
	// language=Vue
	template: ` 
		<div class="checkout-item-info" style='position: relative;' ref="container">
			<a :href="item.product.detailPageUrl" class="checkout-item-image-block">
				<img :src="getSrc" alt="" class="checkout-item-image">
			</a>
			
			<div class="checkout-item-info-container">
				<div class="checkout-item-info-block">
					<h2 class="checkout-item-name">
						<a :href="item.product.detailPageUrl" class="checkout-item-name-link" >{{item.name}}</a>
					</h2>
					<sale-checkout-view-product-props_list :list="item.props"/>
				</div>
				
				<div class="checkout-item-quantity-block">
				<div class="checkout-item-quantity-field-container">
					<slot name="button-minus"/>
					<div class="checkout-item-quantity-field-block">
					<input disabled class="checkout-item-quantity-field" type="text" inputmode="numeric" :value="item.quantity">
					</div>
					<slot name="button-plus"/>
				</div>
				<span class="checkout-item-quantity-description">
					<span class="checkout-item-quantity-description-text">{{item.measureText}}</span>
					<span class="checkout-item-quantity-description-price"></span>
				</span>
			</div>
			</div>
		</div>		
	`
});