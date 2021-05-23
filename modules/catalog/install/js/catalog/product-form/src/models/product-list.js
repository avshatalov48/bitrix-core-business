import {Vue} from 'ui.vue';
import {Text, Type} from 'main.core';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {DiscountType, FieldScheme} from "catalog.product-calculator";
import {ProductFormElementPosition} from "../product-form";
import {CurrencyCore} from "currency.currency-core";

export class ProductList extends VuexBuilderModel
{
	/**
	 * @inheritDoc
	 */
	getName()
	{
		return 'productList';
	}

	getState()
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

	static getBaseProduct()
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
				module: null,
				sort: 0,
				price: 0,
				basePrice: 0,
				priceExclusive: 0,
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
			},
			calculatedFields: FieldScheme,
			showDiscount: 'N',
			showTax: 'N',
			skuTree: [],
			image: null,
			sum: 0,
			discountSum: 0,
			detailUrl: '',
			encodedFields: null,
			errors: [],
		};
	}

	getActions()
	{
		return {
			resetBasket ({ commit })
			{
				commit('clearBasket');
				commit('addItem');
			},
			removeItem({ commit, state }, payload)
			{
				commit('deleteItem', payload);
				if (state.basket.length === 0)
				{
					commit('addItem');
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
				commit('calculateTotal');
			},
			changeItem: ({ commit }, payload) =>
			{
				commit('updateItem', payload);
				commit('calculateTotal');
			},
			setCurrency: ({ commit }, payload) =>
			{
				const currency = payload || '';
				commit('setCurrency', currency);
			},
			addItem: ({ commit }, payload) =>
			{
				const item = payload.item || {fields: {}};
				commit('addItem', {
					item,
					position: payload.position || ProductFormElementPosition.TOP
				});
				commit('calculateTotal');
			},
		}
	}

	getGetters()
	{
		return {
			getBasket: state => () =>
			{
				return state.basket;
			},
			getBaseProduct: () => () =>
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
				if (payload.position === ProductFormElementPosition.BOTTOM)
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
				if (typeof state.basket[payload.index] === 'undefined')
				{
					Vue.set(state.basket, payload.index, ProductList.getBaseProduct());
				}

				state.basket[payload.index] = Object.assign(
					state.basket[payload.index],
					payload.fields
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
			calculateTotal: (state) =>
			{
				const total = {
					sum: 0,
					taxSum: 0,
					discount: 0,
					result: 0,
				};

				state.basket.forEach((item, i) => {
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

				if (Type.isStringFilled(state.currency))
				{
					for (const key in total)
					{
						state.total[key] = CurrencyCore.currencyFormat(total[key], state.currency)
					}
				}
				else
				{
					state.total = total;
				}
			},
			setTotal: (state, payload) =>
			{
				state.total = Object.assign(
					state.total,
					payload
				);
			},
		}
	}
}