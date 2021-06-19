import { Vue } from 'ui.vue';
import { EventType, Application, Loader as LoaderConst } from 'sale.checkout.const';
import { EventEmitter } from "main.core.events";
import { MixinLoader } from "sale.checkout.view.mixins";

import 'sale.checkout.view.element.button.item-mobile-menu'
import 'sale.checkout.view.element.button.remove'
import 'sale.checkout.view.element.button.plus'
import 'sale.checkout.view.element.button.minus'

import './item-backdrop'
import './item-deleted'
import './item-view'
import './item-edit'
import './measure'
import './price-measure'

Vue.component('sale-checkout-view-product-row', {
	props: ['item', 'index', 'mode'],
	mixins:[MixinLoader],
	data()
	{
		return {
			showBackdrop : 'N',
		}
	},
	computed:
	{
		config()
		{
			return {status: this.item.status}
		},
		isBackdrop()
		{
			return this.showBackdrop === 'Y';
		},
		isDeleted()
		{
			return this.item.deleted === 'Y';
		},
		isLocked()
		{
			return this.item.status === LoaderConst.status.wait
		},
		getConstMode()
		{
			return Application.mode
		},
		buttonMinusDisabled()
		{
			return this.item.quantity - this.item.product.ratio < this.item.product.ratio
		},
		buttonPlusDisabled()
		{
			return this.item.quantity + this.item.product.ratio > this.item.product.availableQuantity
		},
		getObjectClass()
		{
			const classes = [
				'checkout-item'
			];
			
			if(this.isDeleted)
			{
				classes.push('checkout-basket-item-deleted')
			}
			
			if(this.isLocked)
			{
				classes.push('checkout-basket-item-locked');
			}
			
			if(this.isBackdrop)
			{
				classes.push('active');
			}
			
			return classes;
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.basket.backdropOpen, (event) => {
			let index = event.getData().index;
			if(index === this.index)
			{
				this.showBackdrop = 'Y'
			}
		});
		
		EventEmitter.subscribe(EventType.basket.backdropClose, (event) => {
			let index = event.getData().index;
			if(index === this.index)
			{
				this.showBackdrop = 'N'
			}
		});
	},
	
	// beforeDestroy()
	// {
	// 	EventEmitter.unsubscribe('test', this.onRequestPermissions);
	// },
	// language=Vue
	template: `
		<div class="checkout-basket-item" :class="getObjectClass" style='position: relative;' ref="container">
			<template v-if="isDeleted">
              <sale-checkout-view-product-item_deleted :item="item" :index="index"/>
			</template>
            <template v-else>
              <template v-if="mode === getConstMode.edit">
                <sale-checkout-view-product-item_edit :item="item" :index="index" :mode="mode">
                  <template v-slot:button-minus><sale-checkout-view-element-button-minus :class="{'checkout-item-quantity-btn-disabled': buttonMinusDisabled}" :index="index"/></template>
                  <template v-slot:button-plus><sale-checkout-view-element-button-plus :class="{'checkout-item-quantity-btn-disabled': buttonPlusDisabled}" :index="index"/></template>
                  <template v-slot:button-remove>
                    <div class="checkout-basket-item-remove-btn-block">
						<sale-checkout-view-element-button-remove :index="index"/>
                      	<sale-checkout-view-element-button-item_mobile_menu :index="index"/>
					</div>
				  </template>
                  <template v-slot:quantity-description>
					<template v-if="buttonMinusDisabled"><sale-checkout-view-product-measure :item="item"/></template>
					<template v-else><sale-checkout-view-product-price_measure :item="item"/></template>
				  </template>
                </sale-checkout-view-product-item_edit>

                <sale-checkout-view-product-item_backdrop_remove :index="index"/>
				
              </template>
              <template v-else>
				<sale-checkout-view-product-item_view :item="item"/>
              </template>
            </template>
		</div>
	`
});