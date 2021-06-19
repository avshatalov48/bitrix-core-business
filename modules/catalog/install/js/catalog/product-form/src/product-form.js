import {Vue} from 'ui.vue';
import {VuexBuilder} from 'ui.vue.vuex';
import {Type, Text, Tag, ajax, Extension} from 'main.core';
import 'ui.notification';

import {ProductList} from './models/product-list';
import {config} from "./config";
import './templates/form';
import './component.css';
import {EventEmitter} from "main.core.events";
import {CurrencyCore} from "currency.currency-core";
import type {FormOption} from "./types/form-option";
import {FormElementPosition} from "./types/form-element-position";
import {DiscountType} from "catalog.product-calculator";

export class ProductForm
{
	constructor(options: FormOption = {})
	{
		this.options = this.prepareOptions(options);
		this.editable = true;

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

	static initStore(): VuexBuilder
	{
		const builder = new VuexBuilder();

		return builder
			.addModel(ProductList.create())
			.useNamespace(true)
			.build();
	}

	prepareOptions(options: FormOption = {}): FormOption
	{
		const settingsCollection = Extension.getSettings('catalog.product-form');
		const defaultOptions: FormOption = {
			basket: [],
			measures: [],
			iblockId: null,
			basePriceId: settingsCollection.get('basePriceId'),
			taxList: [],
			singleProductMode: false,
			showResults: true,
			enableEmptyProductError: true,
			pricePrecision: 2,
			currency: settingsCollection.get('currency'),
			currencySymbol: settingsCollection.get('currencySymbol'),
			taxIncluded: settingsCollection.get('taxIncluded'),
			showDiscountBlock: settingsCollection.get('showDiscountBlock'),
			showTaxBlock: settingsCollection.get('showTaxBlock'),
			allowedDiscountTypes: [DiscountType.PERCENTAGE, DiscountType.MONETARY],
			newItemPosition: FormElementPosition.TOP,
			buttonsPosition: FormElementPosition.TOP,
			urlBuilderContext: 'SHOP',
			hideUnselectedProperties: false,
		};

		options = {...defaultOptions, ...options};
		options.showTaxBlock = 'N';

		options.defaultDiscountType = '';
		if (Type.isArray(options.allowedDiscountTypes))
		{
			if (options.allowedDiscountTypes.includes(DiscountType.PERCENTAGE))
			{
				options.defaultDiscountType = DiscountType.PERCENTAGE;
			}
			else if (options.allowedDiscountTypes.includes(DiscountType.MONETARY))
			{
				options.defaultDiscountType = DiscountType.MONETARY;
			}
		}

		return options;
	}

	layout(): HTMLElement
	{
		return this.wrapper;
	}

	initTemplate(result): Promise
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

			if (Type.isStringFilled(this.options.currency))
			{
				this.setData({
					currency: this.options.currency
				});
				CurrencyCore.loadCurrencyFormat(this.options.currency);
			}

			if (this.options.basket.length > 0)
			{
				this.setData(
					{basket: this.options.basket,},
					{newItemPosition: FormElementPosition.BOTTOM}
				);

				if (Type.isObject(this.options.totals))
				{
					this.store.commit('productList/setTotal', this.options.totals);
				}
				else
				{
					this.store.dispatch('productList/calculateTotal');
				}
			}
			else
			{
				const newItem = this.store.getters['productList/getBaseProduct']();
				newItem.fields.discountType = this.options.defaultDiscountType;
				this.addProduct(newItem);
			}
		});
	}

	addProduct(item = {}): void
	{
		this.store.dispatch('productList/addItem', {
			item,
			position: this.options.newItemPosition
		})
			.then(() => {
				this.#onBasketChange();
			});
	}

	#onBasketChange(): void
	{
		EventEmitter.emit(this, 'ProductForm:onBasketChange', {
			basket: this.store.getters['productList/getBasket']()
		});
	}

	changeProduct(product): void
	{
		this.store.dispatch('productList/changeItem', {
			index: product.index,
			fields: product.fields
		}).then(() => {
			this.#onBasketChange();
		});
	}

	removeProduct(product): void
	{
		this.store.dispatch('productList/removeItem', {
			index: product.index
		}).then(() => {
			this.#onBasketChange();
		});
	}

	setData(data, option = {}): void
	{
		if (Type.isObject(data.basket))
		{
			const formBasket = this.store.getters['productList/getBasket']();
			data.basket.forEach((fields) => {
				if (!Type.isObject(fields))
				{
					return;
				}
				const itemPosition = option.newItemPosition || this.options.newItemPosition;

				const innerId = fields.selectorId;
				if (Type.isNil(innerId))
				{
					this.store.dispatch('productList/addItem', {
						item: fields,
						position: itemPosition
					});

					return;
				}

				const basketIndex = formBasket.findIndex(item => item.selectorId === innerId);
				if (basketIndex === -1)
				{
					this.store.dispatch('productList/addItem', {
						item: fields,
						position: itemPosition
					});
				}
				else
				{
					this.store.dispatch('productList/changeItem', {basketIndex, fields});
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

	changeFormOption(optionName, value): void
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

	getTotal(): void
	{
		this.store.dispatch('productList/getTotal');
	}

	setEditable(value): void
	{
		this.editable = value;
	}

	static showError(error): void
	{
		console.error(error);
	}
}