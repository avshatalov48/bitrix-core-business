import { Core } from 'im.v2.application.core';
import { ActionTree, BuilderModel, MutationTree, GetterTree } from 'ui.vue3.vuex';

type SelectState = {
	collection: {
		[dialogId: string]: Set<number>,
	},
};

export class SelectModel extends BuilderModel
{
	getState(): SelectState
	{
		return {
			collection: {},
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function messages/select/getCollection */
			getCollection: (state: SelectState) => (dialogId: string): ?Set<number> => {
				if (!state.collection[dialogId])
				{
					return null;
				}

				const preparedCollection = [...state.collection[dialogId]];

				const filteredMessageIds = preparedCollection.filter((messageId) => {
					return Core.getStore().getters['messages/isExists'](messageId);
				});

				return new Set(filteredMessageIds);
			},
			/** @function messages/select/isBulkActionsModeActive */
			isBulkActionsModeActive: (state: SelectState) => (dialogId: string): boolean => {
				return Boolean(state.collection[dialogId]);
			},
			/** @function messages/select/isMessageSelected */
			isMessageSelected: (state: SelectState) => (messageId: number, dialogId: string): boolean => {
				if (!state.collection[dialogId])
				{
					return false;
				}

				return state.collection[dialogId].has(messageId);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function messages/select/enableBulkMode */
			enableBulkMode: (store: Object, payload: {messageId: number, dialogId: string}) => {
				const { messageId, dialogId } = payload;

				if (store.state.collection[dialogId])
				{
					return;
				}

				store.commit('enableBulkMode', {
					messageId,
					dialogId,
				});
			},
			/** @function messages/select/disableBulkMode */
			disableBulkMode: (store: Object, payload: {dialogId: string}) => {
				const { dialogId } = payload;

				if (!store.state.collection[dialogId])
				{
					return;
				}

				store.commit('disableBulkMode', {
					dialogId,
				});
			},
			/** @function messages/select/toggleMessageSelection */
			toggleMessageSelection: (store: Object, payload: {messageId: number, dialogId: string}) => {
				const { messageId, dialogId } = payload;

				if (!store.state.collection[dialogId])
				{
					return;
				}

				store.commit('toggleMessageSelection', {
					messageId,
					dialogId,
				});
			},
			/** @function messages/select/clearCollection */
			clearCollection: (store: Object) => {
				store.commit('clearCollection');
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			enableBulkMode: (state: SelectState, payload: {messageId: number, dialogId: string}) => {
				const { messageId, dialogId } = payload;

				// eslint-disable-next-line no-param-reassign
				state.collection[dialogId] = new Set();
				state.collection[dialogId].add(messageId);
			},
			disableBulkMode: (state: SelectState, payload: {dialogId: string}) => {
				const { dialogId } = payload;

				// eslint-disable-next-line no-param-reassign
				delete state.collection[dialogId];
			},
			toggleMessageSelection: (state: SelectState, payload: {messageId: number, dialogId: string}) => {
				const { messageId, dialogId } = payload;

				if (state.collection[dialogId].has(messageId))
				{
					state.collection[dialogId].delete(messageId);

					return;
				}

				state.collection[dialogId].add(messageId);
			},
			clearCollection: (state: SelectState) => {
				// eslint-disable-next-line no-param-reassign
				state.collection = {};
			},
		};
	}
}
