import {Vue} from 'ui.vue';
import {Text, Type} from 'main.core';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {DiscountType} from "catalog.product-calculator";
import {FormElementPosition} from "../types/form-element-position";
import {CurrencyCore} from "currency.currency-core";
import type {FormScheme} from "../types/form-scheme";
import type {BasketItem} from "../types/basket-item";

export class ProductList extends VuexBuilderModel
{
	/**
	 * @inheritDoc
	 */
	getName(): string
	{
		return 'productList';
	}

	getState(): FormScheme
	{
		return {
			currency: '',
			taxIncluded: 'N',
			basket: [],
			total: {
				sum: 0,
				discount: 0,
				taxSum: 0,
				result: 0,
			},
		}
	}

	static getBaseProduct(): BasketItem
	{
		const random = Text.getRandom();
		return {
			offerId: null,
			selectorId: random,
			fields: {
				innerId: random,
				productId: null,
				skuId: null,
				code: null,
				type: null,
				module: null,
				sort: 0,
				price: null,
				basePrice: null,
				priceExclusive: null,
				quantity: 1,
				name: '',
				discount: 0,
				discountRate: 0,
				discountInfos: [],
				discountType: DiscountType.PERCENTAGE,
				tax: 0,
				taxSum: 0,
				taxIncluded: 'N',
				measureCode: 0,
				measureName: '',
				measureRatio: 1,
				isCustomPrice: 'N',
				additionalFields: [],
				properties: [],
				brands: [],
				weight: 0,
				dimensions: {},
			},
			calculatedFields: [],
			catalogFields: {},
			showDiscount: 'N',
			showTax: 'N',
			skuTree: [],
			image: null,
			sum: 0,
			catalogPrice: null,
			discountSum: 0,
			detailUrl: '',
			encodedFields: null,
			errors: [],
		};
	}

	getActions()
	{
		return {
			resetBasket ({commit})
			{
				commit('clearBasket');
				commit('addItem', {});
			},
			removeItem({dispatch, commit, state}, payload)
			{
				commit('deleteItem', payload);
				if (state.basket.length === 0)
				{
					commit('addItem', {});
				}
				else
				{
					state.basket.forEach((item, i) => {
						commit('updateItem', {
							index: i,
							fields: {sort: i}
						});
					});
				}
				dispatch('calculateTotal');
			},
			changeItem: ({dispatch, commit}, payload) =>
			{
				commit('updateItem', payload);
				dispatch('calculateTotal');
			},
			setCurrency: ({commit}, payload) =>
			{
				const currency = payload || '';
				commit('setCurrency', currency);
			},
			addItem: ({dispatch, commit}, payload) =>
			{
				const item = payload.item || {fields: {}};
				commit('addItem', {
					item,
					position: payload.position || FormElementPosition.TOP
				});
				dispatch('calculateTotal');
			},
			calculateTotal: ({commit, state}) =>
			{
				const total = {
					sum: 0,
					taxSum: 0,
					discount: 0,
					result: 0,
				};

				state.basket.forEach((item) => {
					const basePrice = Text.toNumber(item.fields.basePrice || 0);
					const quantity = Text.toNumber(item.fields.quantity || 0);
					const discount = Text.toNumber(item.fields.discount || 0);
					const taxSum = Text.toNumber(item.fields.taxSum || 0);
					total.sum += basePrice * quantity;
					total.result += Text.toNumber(item.sum);
					total.discount += discount * quantity;
					total.taxSum += taxSum * quantity;
				});

				total.discount = (total.discount > total.sum) ? total.sum : total.discount;

				commit('setTotal', total)
			}
		}
	}

	getGetters()
	{
		return {
			getBasket: state => (): Array<BasketItem> =>
			{
				return state.basket;
			},
			getBaseProduct: () => (): BasketItem =>
			{
				return ProductList.getBaseProduct();
			},
		}
	}

	getMutations()
	{
		return {
			addItem: (state, payload) =>
			{
				let item = ProductList.getBaseProduct();

				item = Object.assign(item, payload.item);
				if (payload.position === FormElementPosition.BOTTOM)
				{
					state.basket.push(item);
				}
				else
				{
					state.basket.unshift(item);
				}

				state.basket.forEach((item, index) => {
					item.fields.sort = index;
				});
			},
			updateItem: (state, payload) =>
			{
				if (Type.isNil(state.basket[payload.index]))
				{
					Vue.set(state.basket, payload.index, ProductList.getBaseProduct());
				}

				state.basket[payload.index] = Object.assign(
					state.basket[payload.index],
					payload.product
				);
			},
			clearBasket: (state) =>
			{
				state.basket = [];
			},
			deleteItem: (state, payload) =>
			{
				state.basket.splice(payload.index, 1);
				state.basket.forEach((item, index) => {
					item.fields.sort = index;
				});
			},
			setErrors: (state, payload) =>
			{
				state.errors = payload;
			},
			clearErrors: (state) =>
			{
				state.errors = [];
			},
			setCurrency: (state, payload) =>
			{
				state.currency = payload;
			},
			setTotal: (state, payload) =>
			{
				const formattedTotal = payload;
				if (Type.isStringFilled(state.currency))
				{
					for (const key in payload)
					{
						if (payload.hasOwnProperty(key))
						{
							formattedTotal[key] = CurrencyCore.currencyFormat(payload[key], state.currency)
						}
					}
				}

				state.total = Object.assign(
					state.total,
					formattedTotal
				);
			},
		}
	}
}
