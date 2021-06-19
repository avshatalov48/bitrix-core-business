import { Vue } from 'ui.vue';
import { Url } from 'sale.checkout.lib';
import { Tag, Type } from 'main.core';
import { VuexBuilder } from 'ui.vue.vuex';
import { Application as Controller } from 'sale.checkout.controller'
import { Order, Basket, Property, Application, Consent, PaySystem } from 'sale.checkout.model'

import './view'

export class FormApplication
{
	constructor(options= {} )
	{
		this.wrapper = Tag.render`<div class=""></div>`;

		this.init()
			.then(() => this.prepareParams({options}))
			.then(() => {
				this.initStore()
					.then((result) => this.initTemplate(result)
						.then(() => this.initController()))
					.catch((error) => FormApplication.showError(error))
			});
	}

	init()
	{
		return Promise.resolve();
	}

	prepareParams(params)
	{
		this.options = params.options
		return Promise.resolve();
	}

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
			.addModel(Order.create())
			.addModel(Basket.create().setVariables(contextVariablesBasket))
			.addModel(Property.create())
			.addModel(PaySystem.create())
			.addModel(Application.create().setVariables(contextVariablesApp))
			.addModel(Consent.create())
			.build();
	}

	layout()
	{
		return this.wrapper;
	}

	initController()
	{
		 this.controller = new Controller({
			 store: this.store
		 });

		return new Promise((resolve) => resolve());
	}

	initTemplate(result)
	{
		return new Promise((resolve) =>
		{
			const context = this;
			this.store = result.store;

			this.templateEngine = Vue.create({
				el: this.wrapper,
				store: this.store,
				data: {
					options: this.options
				},
				created()
				{
					this.$app = context;

					let data = {};
					if (context.options.basket.length > 0)
					{
						data = {
							order:  this.options.order,
							basket:  this.options.basket,
							paySystem:  this.options.paySystem,
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
			});
		});
	}

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

	static showError(error)
	{
		console.error(error);
	}
}