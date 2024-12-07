import { BuilderModel, GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

import { tariffRestrictionsFieldsConfig } from './format/field-config';
import { formatFieldsWithConfig } from '../../../utils/validate';

import type { JsonObject } from 'main.core';

export type TariffRestrictions = {
	fullChatHistory: {
		isAvailable: boolean,
		limitDays: number | null,
	}
};

/* eslint-disable no-param-reassign */
export class TariffRestrictionsModel extends BuilderModel
{
	getState(): TariffRestrictions
	{
		return {
			fullChatHistory: {
				isAvailable: true,
				limitDays: null,
			},
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function application/tariffRestrictions/get */
			get: (state: TariffRestrictions): TariffRestrictions => {
				return state;
			},
			/** @function application/tariffRestrictions/isHistoryAvailable */
			isHistoryAvailable: (state: TariffRestrictions): boolean => {
				return state.fullChatHistory?.isAvailable ?? false;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function application/tariffRestrictions/set */
			set: (store, payload: JsonObject) => {
				store.commit('set', this.formatFields(payload));
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state: TariffRestrictions, payload: JsonObject) => {
				Object.entries(payload).forEach(([key, value]) => {
					state[key] = value;
				});
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, tariffRestrictionsFieldsConfig);
	}
}
