import { Vue } from 'ui.vue';
import { VuexBuilderModel } from 'ui.vue.vuex';
import { Type } from 'main.core';

export class Variant extends VuexBuilderModel
{
	getName()
	{
		return 'property-variant';
	}

	getState()
	{
		return {
			variant: [],
		}
	}

	static getBaseItem()
	{
		return {
			id: 0,
			propertyId: 0,
			value: "",
			name: "",
		};
	}

	validate(fields)
	{
		const result = {};

		if (Type.isNumber(fields.id) || Type.isString(fields.id))
		{
			result.id = parseInt(fields.id);
		}

		if (Type.isNumber(fields.orderPropsId) || Type.isString(fields.orderPropsId))
		{
			result.propertyId = parseInt(fields.orderPropsId);
		}

		if (Type.isString(fields.name))
		{
			result.name = fields.name.toString();
		}

		if (Type.isString(fields.value))
		{
			result.value = fields.value.toString();
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
				if (!state.variant[id] || state.variant[id].length <= 0)
				{
					return [];
				}

				return state.variant[id];
			},
			getVariant: state =>
			{
				return state.variant;
			},
			getBaseItem: state =>
			{
				return Variant.getBaseItem();
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
				payload = this.prepareFields(payload);

				let item = Variant.getBaseItem();

				item = Object.assign(item, payload);
				state.variant.unshift(item);
				state.variant.forEach((item, index) => {
					item.sort = index + 1;
				});
			},
			updateItem: (state, payload) =>
			{
				if (typeof state.variant[payload.index] === 'undefined')
				{
					Vue.set(state.variant, payload.index, Variant.getBaseItem());
				}

				state.variant[payload.index] = Object.assign(
					state.variant[payload.index],
					payload.fields
				);
			},
			deleteItem: (state, payload) =>
			{
				state.variant.splice(payload.index, 1);
			},
			clearVariant: (state) =>
			{
				state.variant = [];
			},
			setErrors: (state, payload) =>
			{
				state.errors = payload;
			},
			clearErrors: (state) =>
			{
				state.errors = [];
			}
		}
	}
}