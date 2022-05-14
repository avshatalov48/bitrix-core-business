import { BitrixVue } from 'ui.vue';
import { Url } from 'sale.checkout.lib';
import { Tag, Type } from 'main.core';
import { VuexBuilder } from 'ui.vue.vuex';
import { Application as Controller } from 'sale.checkout.controller'
import {
	Order as OrderModel,
	Check as CheckModel,
	Basket as BasketModel,
	Payment as PaymentModel,
	Property as PropertyModel,
	Application as ApplicationModel,
	Consent as ConsentModel,
	PaySystem as PaySystemModel } from 'sale.checkout.model'

import './view'

export class Application
{
	constructor(options= {} )
	{
		this.wrapper = Tag.render`<div class=""></div>`;
		
		this.init()
			.then(() => this.prepareParams({options}))
			.then(() => {
				this.initStore()
					.then((result) => {
						this.setStore(result);
						this.initController().then(() => {})
						this.initTemplate().then(() => {})
					})
					.catch((error) => Application.showError(error))
			});
	}

	/**
	 * @private
	 */
	init()
	{
		return Promise.resolve();
	}

	/**
	 * @private
	 */
	prepareParams(params)
	{
		this.options = params.options
		return Promise.resolve();
	}

	/**
	 * @private
	 */
	initStore()
	{
		const builder = new VuexBuilder();
		
		let contextVariablesBasket =
			{
				product: this.options.product
			};
		
		let contextVariablesApp =
			{
				path: this.options.path,
				common: this.options.common,
				option: this.options.option,
				messages: this.options.messages
			};
		
		contextVariablesApp.path.location = Url.getCurrentUrl()
		
		return builder
			.addModel(OrderModel.create())
			.addModel(BasketModel.create().setVariables(contextVariablesBasket))
			.addModel(PropertyModel.create())
			.addModel(PaymentModel.create())
			.addModel(CheckModel.create())
			.addModel(PaySystemModel.create())
			.addModel(ApplicationModel.create().setVariables(contextVariablesApp))
			.addModel(ConsentModel.create())
			.build();
	}

	/**
	 * @private
	 */
	layout()
	{
		return this.wrapper;
	}

	/**
	 * @private
	 */
	initController()
	{
		this.controller = new Controller({
			store: this.store
		});
		
		return new Promise((resolve) => resolve());
	}

	/**
	 * @private
	 */
	initTemplate()
	{
		return new Promise((resolve) =>
		{
			const context = this;
			
			this.templateEngine = BitrixVue.createApp({
				store: this.store,
				data: {
					options: this.options
				},
				beforeCreate()
				{
					this.$bitrix.Application.set(context);
				},
				created()
				{
					let data = {};
					if (context.options.basket.length > 0)
					{
						data = {
							order:  this.options.order,
							basket:  this.options.basket,
							paySystem:  this.options.paySystem,
							payment:  this.options.payment,
							check:  this.options.check,
							total: this.options.total,
							currency: this.options.currency,
							discount: this.options.discount,
							property: this.options.property,
							consent: this.options.consent,
							consentStatus: this.options.consentStatus
						}
					}
					
					data.stage = this.options.stage;
					
					context.setModelData(data);
				},
				mounted()
				{
					resolve();
				},
				template: `<sale-checkout-form/>`,
			})
				.mount(this.wrapper);
		});
	}

	/**
	 * @private
	 */
	setStore(data)
	{
		this.store = data.store;
	}

	/**
	 * @private
	 */
	setModelData(data)
	{
		//region: application model
		if (Type.isString(data.stage))
		{
			this.store.dispatch('application/setStage', {stage: data.stage});
		}
		//endregion
		
		//region: order model
		if (Type.isObject(data.order))
		{
			this.store.dispatch('order/set', data.order);
		}
		//endregion
		
		//region: basket model
		if (Type.isObject(data.basket))
		{
			data.basket.forEach((fields, index) => {
				this.store.dispatch('basket/changeItem', {index, fields});
			});
		}
		
		if (Type.isString(data.currency))
		{
			this.store.dispatch('basket/setCurrency', {currency: data.currency});
		}
		
		if (Type.isObject(data.discount))
		{
			this.store.dispatch('basket/setDiscount', data.discount);
		}
		
		if (Type.isObject(data.total))
		{
			this.store.dispatch('basket/setTotal', data.total);
		}
		//endregion
		
		//region: property model
		if (Type.isObject(data.property))
		{
			data.property.forEach((fields, index) => {
				this.store.dispatch('property/changeItem', {index, fields});
			});
		}
		//endregion

		//region: payment model
		if (Type.isObject(data.payment))
		{
			data.payment.forEach((fields, index) => {
				this.store.dispatch('payment/changeItem', {index, fields});
			});
		}
		//endregion

		// region: check model
		if (Type.isObject(data.check))
		{
			data.check.forEach((fields, index) => {
				this.store.dispatch('check/changeItem', {index, fields});
			});
		}
		//endregion
		
		// region: paySystem model
		if (Type.isObject(data.paySystem))
		{
			data.paySystem.forEach((fields, index) => {
				this.store.dispatch('pay-system/changeItem', {index, fields});
			});
		}
		//endregion
		
		//region: consent
		if (Type.isString(data.consentStatus))
		{
			this.store.dispatch('consent/setStatus', data.consentStatus);
		}
		
		if (Type.isObject(data.consent))
		{
			this.store.dispatch('consent/set', data.consent);
		}
		//endregion
		
		// region: errors
		if (Type.isObject(data.errors))
		{
			this.store.commit('basket/setErrors', data.errors);
		}
		//endregion
	}

	/**
	 * @private
	 */
	static showError(error)
	{
		console.error(error);
	}
}