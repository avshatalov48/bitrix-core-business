/* eslint-disable no-param-reassign */
import { BuilderModel, GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

type SearchRecentItem = {
	dialogId: string,
	dateUpdate: ?Date,
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
			dateUpdate: null,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function recent/search/getCollection */
			getCollection: (state: SearchState): SearchRecentItem[] => {
				return Object.values(state.collection);
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
						dateUpdate: recentElement.dateUpdate,
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
		const result = {};

		if (Type.isStringFilled(fields.dialogId))
		{
			result.dialogId = fields.dialogId;
		}

		if (Type.isStringFilled(fields.date_update))
		{
			result.dateUpdate = Utils.date.cast(fields.date_update);
		}

		if (Type.isBoolean(fields.byUser))
		{
			result.foundByUser = fields.byUser;
		}

		return result;
	}
}
