import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

type MessageSearchState = {
	historyLimitExceededCollection: {[chatId: number]: boolean},
};

/* eslint-disable no-param-reassign */
export class MessageSearchModel extends BuilderModel
{
	getState(): MessageSearchState
	{
		return {
			historyLimitExceededCollection: {},
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function sidebar/messageSearch/isHistoryLimitExceeded */
			isHistoryLimitExceeded: (state) => (chatId: number): boolean => {
				const isAvailable = Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
				if (isAvailable)
				{
					return false;
				}

				return state.historyLimitExceededCollection[chatId] ?? false;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/messageSearch/setHistoryLimitExceeded */
			setHistoryLimitExceeded: (store, payload) => {
				const { chatId, isHistoryLimitExceeded = false } = payload;
				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			setHistoryLimitExceeded: (state, payload) => {
				const { chatId, isHistoryLimitExceeded } = payload;
				if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded)
				{
					return;
				}

				state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
			},
		};
	}
}
