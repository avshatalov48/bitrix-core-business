import { Vue } from 'ui.vue';
import { EventEmitter } from 'main.core.events';
import { EventType, Application } from 'sale.checkout.const';

import './price'
import './props-list'

Vue.component('sale-checkout-view-product-item_edit', {
	props: ['item', 'index', 'mode'],
	computed:
		{
			getSrc()
			{
				return encodeURI(this.item.product.picture)
			},
			getConstMode()
			{
				return Application.mode
			}
		},
	methods:
		{
			backdropOpen()
			{
				EventEmitter.emit(EventType.basket.backdropOpen, {index: this.index})
			}
		},
	// language=Vue
	template: `
      <div class="checkout-basket-item-container">
<!--      <div class="checkout-basket-item-label">{{item.name}}</div>-->
      <div class="checkout-basket-item-inner">
        <a :href="item.product.detailPageUrl" class="checkout-basket-item-image-block">
          <img :src="getSrc" :alt="item.name" class="checkout-basket-item-image">
        </a>
        <div class="checkout-basket-item-info-container">
          <h2 class="checkout-basket-item-name-block">
            <a :href="item.product.detailPageUrl" class="checkout-basket-item-name-text">{{item.name}}</a>
          </h2>
          <div class="checkout-basket-item-info-block">
            <sale-checkout-view-product-props_list :list="item.props"/>
<!--            <div class="checkout-basket-desktop-only">{{sku}}</div>-->
<!--            <div class="checkout-basket-mobile-only">-->
<!--              <span class="checkout-basket-item-change-btn" @click="backdropOpen">{{localize.CHECKOUT_VIEW_ITEM_ITEM_EDIT_CHANGE}}</span>-->
<!--            </div>-->
<!--            <div class="checkout-item-warning-container">-->
<!--              <div class="text-danger">Available: 344 pcs.</div>-->
<!--              <div class="text-danger">Unknown error</div>-->
<!--            </div>-->
          </div> 
        </div>
        <div class="checkout-basket-item-summary-info">
          <div class="checkout-item-quantity-block">
            <div class="checkout-item-quantity-field-container">
              <slot name="button-minus"/>
              <div class="checkout-item-quantity-field-block">
                <input disabled class="checkout-item-quantity-field" type="text" inputmode="numeric" :value="item.quantity">
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