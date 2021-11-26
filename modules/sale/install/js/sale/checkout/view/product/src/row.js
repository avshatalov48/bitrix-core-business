import { BitrixVue } from 'ui.vue';
import { EventType, Application, Loader as LoaderConst } from 'sale.checkout.const';
import { EventEmitter } from 'main.core.events';
import { MixinLoader } from 'sale.checkout.view.mixins';

import 'sale.checkout.view.element.button';

import './item-backdrop'
import './item-deleted'
import './item-view'
import './item-edit'
import './measure'
import './price-measure'

BitrixVue.component('sale-checkout-view-product-row', {
	props: ['item', 'index', 'mode', 'error'],
	mixins:[MixinLoader],
	data()
	{
		return {
			showBackdropMobileMenu : 'N',
			showBackdropChangeSku : 'N',
		}
	},
	computed:
	{
		config()
		{
			return {status: this.item.status}
		},
		hasSkuPropsColor()
		{
			let tree = this.item.sku.tree
			return tree.hasOwnProperty('EXISTING_VALUES') && tree.EXISTING_VALUES.hasOwnProperty('COLOR_REF')
		},
		isBackdropMobileMenu()
		{
			return this.showBackdropMobileMenu === 'Y';
		},
		isBackdropChangeSku()
		{
			return this.showBackdropChangeSku === 'Y';
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
				'checkout-table-row-group',
				'checkout-basket-item',
			];

			if (this.hasSkuPropsColor)
			{
				classes.push('checkout-basket-item--has-sku-color')
			}

			if(this.isDeleted)
			{
				classes.push('checkout-basket-item-deleted')
			}

			if(this.isLocked)
			{
				classes.push('checkout-basket-item-locked');
			}

			if(this.isBackdropChangeSku)
			{
				classes.push('active-backdrop-open-change-sku');
			}
			if(this.isBackdropMobileMenu)
			{
				classes.push('active-backdrop-open-mobile-menu');

			}

			return classes;
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.basket.backdropOpenMobileMenu, (event) => {
			let index = event.getData().index;
			if(index === this.index)
			{
				this.showBackdropMobileMenu = 'Y'
			}
		});

		EventEmitter.subscribe(EventType.basket.backdropOpenChangeSku, (event) => {
			let index = event.getData().index;
			if(index === this.index)
			{
				this.showBackdropChangeSku = 'Y'
			}
		});

		EventEmitter.subscribe(EventType.basket.backdropClose, (event) => {
			let index = event.getData().index;
			if(index === this.index)
			{
				this.showBackdropMobileMenu = 'N'
				this.showBackdropChangeSku = 'N'
			}
		});
	},
	beforeDestroy()
	{
		// EventEmitter.unsubscribe(EventType.basket.backdropOpenMobileMenu);
		// EventEmitter.unsubscribe(EventType.basket.backdropOpenChangeSku);
		// EventEmitter.unsubscribe(EventType.basket.backdropClose);
	},
	// language=Vue
	template: `
		<div :class="getObjectClass" style='position: relative;' ref="container">
			<template v-if="isDeleted">
				<sale-checkout-view-product-item_deleted :item="item" :index="index"/>
			</template>
			<template v-else>
				<template v-if="mode === getConstMode.edit">
					<sale-checkout-view-product-item_edit :item="item" :index="index" :mode="mode" :error="error">
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
						<template v-slot:button-change-sku><sale-checkout-view-element-button-item_change_sku :index="index"/></template>
					</sale-checkout-view-product-item_edit>
					<sale-checkout-view-product-item_backdrop_remove :index="index"/>
					<sale-checkout-view-product-item_backdrop :item="item" :index="index" :error="error">
						<template v-slot:button-minus><sale-checkout-view-element-button-minus :class="{'checkout-item-quantity-btn-disabled': buttonMinusDisabled}" :index="index"/></template>
						<template v-slot:button-plus><sale-checkout-view-element-button-plus :class="{'checkout-item-quantity-btn-disabled': buttonPlusDisabled}" :index="index"/></template>
					</sale-checkout-view-product-item_backdrop>
				</template>
				<template v-else>
					<sale-checkout-view-product-item_view :item="item"/>
				</template>
			</template>
		</div>
	`
});
