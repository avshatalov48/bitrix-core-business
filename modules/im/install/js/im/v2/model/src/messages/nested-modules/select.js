import { ActionTree, BuilderModel, MutationTree, GetterTree } from 'ui.vue3.vuex';

type SelectState = {
	collection: Set<number>,
	isBulkActionsMode: boolean,
};

export class SelectModel extends BuilderModel
{
	getState(): SelectState
	{
		return {
			collection: new Set(),
			isBulkActionsMode: false,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function messages/select/getCollection */
			getCollection: (state: SelectState): Set<number> => {
				return state.collection;
			},
			/** @function messages/select/getBulkActionsMode */
			getBulkActionsMode: (state: SelectState): boolean => {
				return state.isBulkActionsMode;
			},
			/** @function messages/select/isMessageSelected */
			isMessageSelected: (state: SelectState) => (messageId: number): boolean => {
				return state.collection.has(messageId);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function messages/select/toggle */
			toggle: (store: Object, messageId: number) => {
				if (store.state.collection.has(messageId))
				{
					store.commit('delete', messageId);
				}
				else
				{
					store.commit('add', messageId);
				}
			},
			/** @function messages/select/deleteByMessageId */
			deleteByMessageId: (store: Object, messageId: number) => {
				if (!store.state.collection.has(messageId))
				{
					return;
				}

				store.commit('delete', messageId);
			},
			/** @function messages/select/toggleBulkActionsMode */
			toggleBulkActionsMode: (store: Object, active: boolean) => {
				store.commit('toggleBulkActionsMode', active);
			},
			/** @function messages/select/clear */
			clear: (store: Object) => {
				store.commit('clear');
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			add: (state: SelectState, messageId: number) => {
				state.collection.add(messageId);
			},
			delete: (state: SelectState, messageId: number) => {
				state.collection.delete(messageId);
			},
			toggleBulkActionsMode: (state: SelectState, active: boolean) => {
				// eslint-disable-next-line no-param-reassign
				state.isBulkActionsMode = active;
			},
			clear: (state: SelectState) => {
				state.collection.clear();
			},
		};
	}
}
