import { BitrixVue } from 'ui.vue';
import { EventEmitter } from "main.core.events";
import { Type } from 'main.core';
import { Application as ApplicationConst, Loader as LoaderConst, PaySystem as PaySystemConst, Property as PropertyConst,
		 EventType } from 'sale.checkout.const';

import "sale.checkout.view.total";
import "sale.checkout.view.product";
import "sale.checkout.view.property";
import "sale.checkout.view.user-consent"
import "sale.checkout.view.element.button";
import "sale.checkout.view.successful";
import "sale.checkout.view.empty-cart";
import "sale.checkout.view.payment"
import "sale.checkout.view.alert"

BitrixVue.component('sale-checkout-form', {
	data()
	{
		return {
			stage: ApplicationConst.stage,
			mode: ApplicationConst.mode,
			status: LoaderConst.status,
			totalIsShow: 'N'
		}
	},
	computed:
	{
		checkoutButtonEnabled()
		{
			const properties = [];

			let list = this.$store.getters['property/getProperty'];

			for (let listKey in list)
			{
				if (!Type.isStringFilled(list[listKey].value) && list[listKey].required === 'Y')
				{
					return false;
				}

				if (!Type.isStringFilled(list[listKey].value))
				{
					continue;
				}

				if (
					list[listKey].type === PropertyConst.type.checkbox
					&& list[listKey].required === 'Y'
					&& list[listKey].value !== 'Y'
				)
				{
					return false;
				}

				if (list[listKey].type === PropertyConst.type.checkbox)
				{
					continue;
				}

				properties.push(list[listKey].value);
			}

			return properties.length > 0
		},
		hasPS()
		{
			const result = [];
			let list = this.$store.getters['pay-system/getPaySystem'];
			list.forEach((fields) => {
				if(fields.type !== PaySystemConst.type.cash)
				{
					result.push(fields)
				}
			})
			return result.length > 0
		},
		needCheckConsent()
		{
			return this.getConsent.id > 0;
		},
		getBasket()
		{
			return this.$store.getters['basket/getBasket'];
		},
		getBasketErrors()
		{
			return this.$store.getters['basket/getErrors'];
		},
		getOrder()
		{
			return this.$store.getters['order/getOrder'];
		},
		getProperty()
		{
			return this.$store.getters['property/getProperty'];
		},
		getVariant()
		{
			return this.$store.getters['property-variant/getVariant'];
		},
		getPropertyErrors()
		{
			return this.$store.getters['property/getErrors'];
		},
		getTotal()
		{
			const total = this.$store.getters['basket/getTotal'];
			return {
				price: total.price,
				basePrice: total.basePrice,
				discount: this.$store.getters['basket/getDiscount'],
				currency: this.$store.getters['basket/getCurrency']
			}
		},
		getConsent()
		{
			return this.$store.getters['consent/get'];
		},
		getStage()
		{
			return this.$store.getters['application/getStage'];
		},
		getStatus()
		{
			return this.$store.getters['application/getStatus'];
		},
		getBasketConfig()
		{
			return {
				status: this.$store.getters['basket/getStatus']
			}
		},
		getPaySystem()
		{
			return this.$store.getters['pay-system/getPaySystem']
		},
		getCheck()
		{
			return this.$store.getters['check/getCheck']
		},
		getPayment()
		{
			return this.$store.getters['payment/getPayment']
		},
		getPaymentConfig()
		{
			return {
				status: this.$store.getters['pay-system/getStatus'],
				returnUrl: this.$store.getters['application/getPathLocation'],
				mainPage: this.$store.getters['application/getPathMainPage'],
			}
		},
		getSuccessfulConfig()
		{
			return {
				mainPage: this.$store.getters['application/getPathMainPage'],
			}
		},
		getEmptyCartConfig()
		{
			return {
				path: this.$store.getters['application/getPath'],
			}
		},
		getTitleCheckoutButton()
		{
			return {
				title: this.$store.getters['application/getTitleCheckoutButton'],
			}
		},
		getErrors()
		{
			return this.$store.getters['application/getErrors'];
		}
	},
	created()
	{

		EventEmitter.subscribe(EventType.basket.backdropTotalOpen, (event) => {
			this.totalIsShow = 'Y';
		});

		EventEmitter.subscribe(EventType.basket.backdropTotalClose, (event) => {
			this.totalIsShow = 'N';
		});
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.basket.backdropTotalOpen);
		EventEmitter.unsubscribe(EventType.basket.backdropTotalClose);
	},
	// language=Vue
	template: `
      <div class="checkout-container-wrapper">
		  <div class="checkout-basket-container">
			<template v-if="getStage === stage.edit">
			  <sale-checkout-view-product :items="getBasket" :total="getTotal" :mode="mode.edit" :errors="getBasketErrors" :config="getBasketConfig"/>
			  <sale-checkout-view-property :items="getProperty" :mode="mode.edit" :errors="getPropertyErrors" :propertyVariants="getVariant"/>
			  <sale-checkout-view-alert-list :errors="getErrors"/>
			  <sale-checkout-view-user_consent :item="getConsent" v-if="needCheckConsent"/>
			  <template v-if="checkoutButtonEnabled">
				<sale-checkout-view-element-button-checkout :title="getTitleCheckoutButton.title" :wait="getStatus === status.wait"/>
			  </template>
			  <template v-else>
				<sale-checkout-view-element-button-checkout_disabled :title="getTitleCheckoutButton.title"/>
			  </template>
			</template>
			<template v-else-if="getStage === stage.success">
			  <template v-if="hasPS">
				<sale-checkout-view-successful :items="getProperty" :order="getOrder" :config="getSuccessfulConfig"/>
			  </template>
			  <template v-else>
				<sale-checkout-view-successful-without-ps :items="getProperty" :order="getOrder" :config="getSuccessfulConfig"/>
			  </template>
			</template>
			<template v-else-if="getStage === stage.payed">
              <sale-checkout-view-successful_ps_return :items="getProperty" :order="getOrder" :total="getTotal" :config="getSuccessfulConfig"/>
			</template>
			<template v-else-if="getStage === stage.view">
			  <sale-checkout-view-product :items="getBasket" :total="getTotal" :mode="mode.view" :errors="getBasketErrors" :config="getBasketConfig"/>
			  <sale-checkout-view-property :items="getProperty" :mode="mode.view" :order="getOrder" :propertyVariants="getVariant"/>
			  <sale-checkout-view-product-summary :total="getTotal" :mode="mode.view"/>
              <sale-checkout-view-payment :order="getOrder" :payments="getPayment" :paySystems="getPaySystem" :check="getCheck" :config="getPaymentConfig"/>
			</template>
			<template v-else-if="getStage === stage.empty">
			  <sale-checkout-view-empty_cart :config="getEmptyCartConfig"/>
			</template>
		  </div>
		  <template v-if="getStage === stage.view">
			<sale-checkout-view-total :total="getTotal" :showBackdrop="totalIsShow"/>
		  </template>
      </div>
	`
});