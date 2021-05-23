import {Vue} from 'ui.vue';
import {VuexBuilder} from 'ui.vue.vuex';
import {Type, Text, Tag, ajax} from 'main.core';
import 'ui.notification';

import {ProductList} from './models/product-list';
import {config} from "./config";
import './templates/form';
import './component.css';
import {EventEmitter} from "main.core.events";
import {CurrencyCore} from "currency.currency-core";

export class ProductFormElementPosition
{
	static TOP: string = 'TOP';
	static BOTTOM: string = 'BOTTOM';
}

export class ProductForm
{
	constructor(options = {
		basket: [],
		measures: [],
		iblockId: null,
		basePriceId: null,
		taxList: [],
		currencySymbol: null,
		singleProductMode: false,
		showResults: false,
		currency: '',
		pricePrecision: 2,
		taxIncluded: 'N',
		showDiscountBlock: 'Y',
		showTaxBlock: 'Y',
		newItemPosition: ProductFormElementPosition.TOP,
		buttonsPosition: ProductFormElementPosition.TOP,
		urlBuilderContext: 'SHOP',
	})
	{
		options.taxIncluded = options.taxIncluded || 'N';
		options.showTaxBlock = 'N';
		options.urlBuilderContext = options.urlBuilderContext || 'SHOP';

		CurrencyCore.loadCurrencyFormat();

		this.options = options;
		this.editable = true;

		if(Type.isBoolean(options.isCatalogAvailable))
		{
			this.isCatalogAvailable = options.isCatalogAvailable;
		}
		else
		{
			this.isCatalogAvailable = false;
		}

		this.wrapper = Tag.render`<div class=""></div>`;

		if (Text.toNumber(options.iblockId) <= 0)
		{
			return;
		}
		ProductForm.initStore()
			.then((result) => this.initTemplate(result))
			.catch((error) => ProductForm.showError(error))
		;
	}

	static initStore()
	{
		const builder = new VuexBuilder();

		return builder
			.addModel(ProductList.create())
			.useNamespace(true)
			.build();
	}

	layout()
	{
		return this.wrapper;
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
				},
				mounted()
				{
					resolve();
				},
				template: `<${config.templateName} :options="options"/>`,
			});

			this.setData({
				currency: this.options.currency,
			});

			if (this.options.basket.length > 0)
			{
				this.setData({
					basket: this.options.basket,
				});

				if (Type.isObject(this.options.totals))
				{
					this.store.commit('productList/setTotal', this.options.totals);
				}
				else
				{
					this.store.commit('productList/calculateTotal');
				}
			}
			else
			{
				this.addProduct();
			}
		});
	}

	addProduct(item = {})
	{
		this.store.dispatch('productList/addItem', {
			item,
			position: this.options.newItemPosition
		})
			.then(() => {
				this.#onBasketChange();
			});
	}

	#onBasketChange()
	{
		EventEmitter.emit(this, 'ProductForm:onBasketChange', {
			basket: this.store.getters['productList/getBasket']()
		});
	}

	changeProduct(product)
	{
		this.store.dispatch('productList/changeItem', {
			index: product.index,
			fields: product.fields
		}).then(() => {
			this.#onBasketChange();
		});
	}

	removeProduct(product)
	{
		this.store.dispatch('productList/removeItem', {
			index: product.index
		}).then(() => {
			this.#onBasketChange();
		});
	}

	setData(data)
	{
		if (Type.isObject(data.basket))
		{
			data.basket.forEach((fields, index) => {
				if (Type.isObject(fields))
				{
					index = fields.innerId || index;
					this.store.dispatch('productList/changeItem', {index, fields});
				}
			});
		}

		if (Type.isStringFilled(data.currency))
		{
			this.store.dispatch('productList/setCurrency', data.currency);
		}
		
		if (Type.isObject(data.total))
		{
			this.store.commit('productList/setTotal', {
				sum: data.total.sum,
				taxSum: data.total.taxSum,
				discount: data.total.discount,
				result: data.total.result,
			})
		}

		if (Type.isObject(data.errors))
		{
			this.store.commit('productList/setErrors', data.errors);
		}
	}

	changeFormOption(optionName, value)
	{
		value = (value === 'Y') ? 'Y' : 'N';
		this.options[optionName] = value;
		const basket = this.store.getters['productList/getBasket']();
		basket.forEach((item, index) => {
			if (optionName === 'showDiscountBlock')
			{
				item.showDiscountBlock = value;
			}
			else if (optionName === 'showTaxBlock')
			{
				item.showTaxBlock = value;
			}
			else if (optionName === 'taxIncluded')
			{
				item.fields.taxIncluded = value;
			}

			this.store.dispatch('productList/changeItem', {
				index,
				fields: item
			});
		});

		ajax.runAction(
			'catalog.productForm.setConfig',
			{
				data: {
					configName: optionName,
					value: value
				}
			}
		);
	}

	getTotal()
	{
		this.store.dispatch('productList/getTotal');
	}

	setEditable(value)
	{
		this.editable = value;
	}

	static showError(error)
	{
		console.error(error);
	}
}