/* eslint-disable no-param-reassign */
import { BuilderModel, GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import { Type } from 'main.core';

type SearchRecentItem = {
	dialogId: string,
	foundByUser: boolean,
}

type SearchState = {
	collection: {
		[dialogId: string]: SearchRecentItem
	}
};

export class RecentSearchModel extends BuilderModel
{
	getState(): SearchState
	{
		return {
			collection: {},
		};
	}

	getElementState(): SearchRecentItem
	{
		return {
			dialogId: '0',
			foundByUser: false,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function recent/search/getDialogIds */
			getDialogIds: (state: SearchState): string[] => {
				return Object.values(state.collection).map((item) => item.dialogId);
			},
			/** @function recent/search/get */
			get: (state: SearchState) => (rawDialogId: string): SearchRecentItem | null => {
				let dialogId = rawDialogId;
				if (Type.isNumber(dialogId))
				{
					dialogId = dialogId.toString();
				}

				if (state.collection[dialogId])
				{
					return state.collection[dialogId];
				}

				return null;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function recent/search/set */
			set: (store, payload) => {
				payload.forEach((item) => {
					const recentElement = this.validate(item);

					store.commit('set', {
						dialogId: recentElement.dialogId,
						foundByUser: recentElement.foundByUser,
					});
				});
			},
			/** @function recent/search/clear */
			clear: (store, payload) => {
				store.commit('clear');
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state: SearchState, payload: SearchRecentItem) => {
				state.collection[payload.dialogId] = payload;
			},
			clear: (state: SearchState) => {
				state.collection = {};
			},
		};
	}

	validate(fields: Object, options): SearchRecentItem
	{
		const element = this.getElementState();

		if (Type.isStringFilled(fields.dialogId))
		{
			element.dialogId = fields.dialogId;
		}

		if (Type.isBoolean(fields.byUser))
		{
			element.foundByUser = fields.byUser;
		}

		return element;
	}
}
