import { Vue } from 'ui.vue';
import { VuexBuilderModel } from 'ui.vue.vuex';
import { Type } from 'main.core';

export class Payment extends VuexBuilderModel
{
	getName()
	{
		return 'payment';
	}

	getState()
	{
		return {
			payment: [],
			errors: []
		}
	}

	static getBaseItem()
	{
		return {
			id: 0,
			sum: 0.0,
			paid: 'N',
			currency: null,
			accountNumber: null,
			dateBillFormatted: null,
			paySystemId: 0
		};
	}

	validate(fields)
	{
		const result = {};

		if (Type.isNumber(fields.id) || Type.isString(fields.id))
		{
			result.id = parseInt(fields.id);
		}

		if (Type.isNumber(fields.sum) || Type.isString(fields.sum))
		{
			result.sum = parseFloat(fields.sum);
		}

		if (Type.isString(fields.paid))
		{
			result.paid = fields.paid.toString() === 'Y' ? 'Y':'N';
		}

		if (Type.isString(fields.currency))
		{
			result.currency = fields.currency.toString();
		}

		if (Type.isNumber(fields.accountNumber) || Type.isString(fields.accountNumber))
		{
			result.accountNumber = fields.accountNumber.toString();
		}

		if (Type.isString(fields.dateBillFormatted))
		{
			result.dateBillFormatted = fields.dateBillFormatted.toString();
		}

		if (Type.isNumber(fields.paySystemId) || Type.isString(fields.paySystemId))
		{
			result.paySystemId = parseInt(fields.paySystemId);
		}
		return result;
	}

	getActions()
	{
		return {
			addItem: ({ commit }, payload) =>
			{
				payload.fields = this.validate(payload.fields);

				commit('addItem', payload);
			},
			changeItem: ({ commit }, payload) =>
			{
				payload.fields = this.validate(payload.fields);

				commit('updateItem', payload);
			},
			removeItem({ commit }, payload)
			{
				commit('deleteItem', payload);
			}
		}
	}

	getGetters()
	{
		return {
			get: state => id =>
			{
				if (!state.payment[id] || state.payment[id].length <= 0)
				{
					return [];
				}

				return state.payment[id];
			},
			getPayment: state =>
			{
				return state.payment;
			},
			getErrors: state =>
			{
				return state.errors;
			}
		}
	}

	getMutations()
	{
		return {
			addItem: (state, payload) =>
			{
				let item = Payment.getBaseItem();

				item = Object.assign(item, payload.fields);

				state.payment.push(item);
			},
			updateItem: (state, payload) =>
			{
				if (typeof state.payment[payload.index] === 'undefined')
				{
					Vue.set(state.payment, payload.index, Payment.getBaseItem());
				}

				state.payment[payload.index] = Object.assign(
					state.payment[payload.index],
					payload.fields
				);
			},
			deleteItem: (state, payload) =>
			{
				state.payment.splice(payload.index, 1);
			},
			clearPayment: (state) =>
			{
				state.payment = [];
			},
		}
	}
}
