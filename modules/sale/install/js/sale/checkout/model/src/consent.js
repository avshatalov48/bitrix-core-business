import { VuexBuilderModel } from 'ui.vue.vuex';
import { Type } from 'main.core';
import { Consent as Const } from 'sale.checkout.const';

export class Consent extends VuexBuilderModel
{
	getName()
	{
		return 'consent';
	}

	getState()
	{
		return {
			status: Const.status.init,
			consent: Consent.getBaseItem(),
			errors: [],
		};
	}

	static getBaseItem()
	{
		return {
			items: [],
			title: '',
			isLoaded: '',
			autoSave: '',
			submitEventName: '',
			params: [],
		};
	}

	validate(fields)
	{
		const result = {};

		if (Type.isString(fields.status))
		{
			result.status = fields.status.toString();
		}

		if (Type.isObject(fields.consent))
		{
			result.consent = this.validateConsent(fields.consent);
		}

		return result;
	}

	validateConsent(fields)
	{
		const result = {};

		result.items = fields.items;

		if (Type.isString(fields.title))
		{
			result.title = fields.title.toString();
		}

		if (Type.isString(fields.isLoaded))
		{
			result.isLoaded = fields.isLoaded.toString();
		}

		if (Type.isString(fields.autoSave))
		{
			result.autoSave = fields.autoSave.toString();
		}

		if (Type.isString(fields.submitEventName))
		{
			result.submitEventName = fields.submitEventName.toString();
		}

		if (Type.isArrayFilled(fields.params))
		{
			result.params = this.validateParams(fields.params);
		}

		return result;
	}

	validateParams(fields)
	{
		const result = [];
		try
		{
			for (const key in fields)
			{
				if (!fields.hasOwnProperty(key))
				{
					continue;
				}

				if (Type.isNumber(fields[key]) || Type.isString(fields[key]))
				{
					result[key] = fields[key];
				}
			}
		}
		catch
		{}

		return result;
	}

	getActions()
	{
		return {
			setStatus: ({ commit }, payload) => {
				payload = this.validate({ status: payload });

				const status = Object.values(Const.status);

				payload.status = status.includes(payload.status) ? payload.status : Const.status.init;

				commit('setStatus', payload);
			},

			set: ({ commit }, payload) => {
				payload = this.validate({ consent: payload });
				commit('set', payload);
			},

			setChecked: ({ commit }, payload) => {
				commit('setChecked', payload);
			},
		};
	}

	getGetters()
	{
		return {
			getStatus: (state) => {
				return state.status;
			},
			get: (state) => {
				return state.consent;
			},
		};
	}

	getMutations()
	{
		return {
			setStatus: (state, payload) => {
				state.status = payload.status;
			},

			set: (state, payload) => {
				const item = Consent.getBaseItem();

				state.consent = Object.assign(item, payload.consent);
			},
			setChecked: (state, payload) => {
				const currentItem = state.consent.items.find((item) => parseInt(item.id, 10) === payload.id);
				currentItem.checked = payload.checked;
			},
			setErrors: (state, payload) => {
				state.errors = payload;
			},
			clearErrors: (state) => {
				state.errors = [];
			},
		};
	}
}
