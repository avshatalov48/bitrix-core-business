import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {Type} from 'main.core';
import {Loader as LoaderConst, Check as Const} from 'sale.checkout.const';

export class Check extends VuexBuilderModel
{
	getName()
	{
		return 'check';
	}

	getState()
	{
		return {
			check: [],
			status: LoaderConst.status.none,
		}
	}

	static getBaseItem()
	{
		return {
			id: 0,
			paymentId: 0,
			dateFormatted: null,
			status: Const.status.new,
			link: null
		};
	}

	validate(fields)
	{
		const result = {};

		if (Type.isObject(fields.check))
		{
			result.check = this.validateCheck(fields.check);
		}

		if (Type.isString(fields.status))
		{
			result.status = fields.status.toString()
		}

		return result;
	}

	validateCheck(fields)
	{
		const result = {};

		if (Type.isNumber(fields.id) || Type.isString(fields.id))
		{
			result.id = parseInt(fields.id);
		}

		if (Type.isNumber(fields.paymentId) || Type.isString(fields.paymentId))
		{
			result.paymentId = parseInt(fields.paymentId);
		}

		if (Type.isString(fields.dateFormatted))
		{
			result.dateFormatted = fields.dateFormatted.toString();
		}

		if (Type.isString(fields.link))
		{
			result.link = fields.link.toString();
		}

		if (Type.isString(fields.status))
		{
			let allowed = Object.values(Const.status);

			let status = fields.status.toString();

			result.status = allowed.includes(status) ? status : Const.status.new;
		}

		return result;
	}

	getActions()
	{
		return {
			setStatus: ({ commit }, payload) =>
			{
				payload = this.validate(payload);

				const status = Object.values(LoaderConst.status);

				payload.status = status.includes(payload.status) ? payload.status : LoaderConst.status.none;

				commit('setStatus', payload);
			},
			addItem: ({ commit }, payload) =>
			{
				payload.fields = this.validateCheck(payload.fields);
				commit('addItem', payload);
			},
			changeItem: ({ commit }, payload) =>
			{
				payload.fields = this.validateCheck(payload.fields);
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
			getStatus: state =>
			{
				return state.status;
			},
			getCheck: state =>
			{
				return state.check;
			},
		}
	}

	getMutations()
	{
		return {
			setStatus: (state, payload) =>
			{
				let item = { status: LoaderConst.status.none };

				item = Object.assign(item, payload);
				state.status = item.status;
			},
			addItem: (state, payload) =>
			{
				let item = Check.getBaseItem();

				item = Object.assign(item, payload.fields);

				state.check.push(item);
			},
			updateItem: (state, payload) =>
			{
				if (typeof state.check[payload.index] === 'undefined')
				{
					Vue.set(state.check, payload.index, Check.getBaseItem());
				}

				state.check[payload.index] = Object.assign(
					state.check[payload.index],
					payload.fields
				);
			},
			deleteItem: (state, payload) =>
			{
				state.check.splice(payload.index, 1);
			},
			clearCheck: (state) =>
			{
				state.check = [];
			},
		}
	}
}