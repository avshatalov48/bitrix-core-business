import { Vue } from 'ui.vue';
import { EventEmitter } from "main.core.events";
import { EventType } from 'sale.checkout.const';

import 'sale.checkout.view.element.button.plus'
import 'sale.checkout.view.element.button.minus'

import './price'
import './props-list'

Vue.component('sale-checkout-view-product-item_backdrop', {
	props: ['item', 'index', 'mode'],
	computed:
	{
		localize() {
			return Object.freeze(
				Vue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'))
		},
		getSrc()
		{
			return encodeURI(this.item.product.picture)
		}
	},
	methods:
		{
			close()
			{
				EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
			},
			cancel()
			{
				EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
			},
			remove()
			{
				EventEmitter.emit(EventType.basket.buttonRemoveProduct, {index: this.index});
				EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
			},
			hasProps()
			{
				return this.item.props.length > 0;
			}
		},
	// language=Vue
	template: `
      <div class="checkout-basket-item-backdrop-wrapper" style="">
      <div class="checkout-basket-item-backdrop-overlay" @click="close"></div>
      <div class="checkout-basket-item-backdrop-container">
        <!-- region top-->
        <div class="checkout-basket-item-detail-header justify-content-between align-items-center">
          <div class="checkout-basket-item-detail-header-separate"></div>
          <div class="checkout-basket-item-detail-swipe-btn-container"
               id="bx_3966226736_424_7e1b8e3524755c391129a9d7e6f2d206_prebuy_swipe_btn">
            <div class="checkout-basket-item-detail-swipe-btn"></div>
          </div>
          <div class="checkout-basket-item-detail-close-btn-container" @click="close">
				<span class="checkout-basket-item-detail-close-btn"
                        id="bx_3966226736_424_7e1b8e3524755c391129a9d7e6f2d206_prebuy_close_btn">
					<span class="checkout-basket-item-detail-close-btn-text" >{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_CLOSE}}</span>
				</span>
          </div>
        </div>
        <!--endregion-->
        <div class="checkout-basket-item-backdrop-inner">
          <a :href="item.product.detailPageUrl" class="checkout-basket-item-image-block">
            <img :src="getSrc" :alt="item.name"
                 class="checkout-basket-item-image">
          </a>
          <h2 class="checkout-basket-item-name-block">
            <a :href="item.product.detailPageUrl" class="checkout-basket-item-name-text">{{item.name}}</a>
          </h2>

          <div class="checkout-basket-item-info-container" v-if="hasProps()">
            <div class="checkout-basket-item-info-block">
              <sale-checkout-view-product-props_list :list="item.props"/>

              <!--              <div class="checkout-item-warning-container">-->
              <!--                <div class="text-danger">Available: 344 pcs.</div>-->
              <!--                <div class="text-danger">Unknown error</div>-->
              <!--              </div>-->
            </div>
          </div>

          <div class="checkout-basket-item-summary-info">
            <div class="checkout-item-quantity-block">
              <div class="checkout-item-quantity-field-container">
                <slot name="button-minus" />
                <div class="checkout-item-quantity-field-block">
                  <input disabled class="checkout-item-quantity-field" type="text" inputmode="numeric" :value="item.quantity">
                  <div class="checkout-item-quantity-field">{{item.quantity}}</div>
                </div>
                <slot name="button-plus" />
                <slot name="quantity-description" />
              </div>
            </div>
            <sale-checkout-view-product-price :item="item" :index="index" />
          </div>

          <div class="checkout-basket-item-change-confirm-btn-container">
            <button class="product-item-detail-buy-button btn btn-primary rounded-pill" @click="close">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_CHANGE}}</button>
          </div>

          <div class="checkout-basket-item-remove-btn-container">
            <button class="product-item-detail-remove-button btn btn-danger rounded-pill" @click="remove">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_REMOVE}}</button>
          </div>

          <div class="checkout-basket-item-cancel-btn-container">
            <button class="product-item-detail-cancel-button btn border border-dark rounded-pill" @click="cancel">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_CANCEL}}</button>
          </div>
        </div>
      </div>
      </div>
	`
});