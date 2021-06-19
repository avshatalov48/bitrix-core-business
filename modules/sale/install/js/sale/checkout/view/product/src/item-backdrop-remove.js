import { Vue } from 'ui.vue';
import { EventEmitter } from "main.core.events";
import { EventType } from 'sale.checkout.const';

Vue.component('sale-checkout-view-product-item_backdrop_remove', {
	props: ['index'],
	computed:
	{
		localize() {
			return Object.freeze(
				Vue.getFilteredPhrases('CHECKOUT_VIEW_ITEM_BACKDROP_'))
		}
	},
	methods:
		{
			close()
			{
				EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
			},
			remove()
			{
				EventEmitter.emit(EventType.basket.buttonRemoveProduct, {index: this.index});
				EventEmitter.emit(EventType.basket.backdropClose, {index: this.index})
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

          <div class="checkout-basket-item-remove-btn-container pt-2">
            <button class="product-item-detail-remove-button btn btn-danger rounded-pill" @click="remove">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_REMOVE}}</button>
          </div>

          <div class="checkout-basket-item-cancel-btn-container">
            <button class="product-item-detail-cancel-button btn border border-dark rounded-pill" @click="close">{{localize.CHECKOUT_VIEW_ITEM_BACKDROP_CANCEL}}</button>
          </div>
        </div>
      </div>
      </div>
	`
});