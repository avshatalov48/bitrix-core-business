import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import type { ActionTree, MutationTree, GetterTree } from 'ui.vue3.vuex';

type MembersState = {
	collection: { [chatId: number]: ChatState }
};

type ChatState = {
	users: Set<number>,
	inited: boolean,
	hasNextPage: boolean,
	lastId: number
};

/* eslint-disable no-param-reassign */
export class MembersModel extends BuilderModel
{
	getState(): MembersState
	{
		return {
			collection: {},
		};
	}

	getChatState(): ChatState
	{
		return {
			users: new Set(),
			hasNextPage: true,
			lastId: 0,
			inited: false,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function sidebar/members/get */
			get: (state) => (chatId: number): number[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].users];
			},
			/** @function sidebar/members/getSize */
			getSize: (state) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].users.size;
			},
			/** @function sidebar/members/hasNextPage */
			hasNextPage: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].hasNextPage;
			},
			/** @function sidebar/members/getLastId */
			getLastId: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].lastId;
			},
			/** @function sidebar/members/getInited */
			getInited: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].inited;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/members/set */
			set: (store, payload) => {
				const { chatId, users, hasNextPage, lastId } = payload;

				if (!Type.isNil(hasNextPage))
				{
					store.commit('setHasNextPage', { chatId, hasNextPage });
				}

				if (!Type.isNil(lastId))
				{
					store.commit('setLastId', { chatId, lastId });
				}

				store.commit('setInited', { chatId, inited: true });

				if (users.length > 0)
				{
					store.commit('set', { chatId, users });
				}
			},
			/** @function sidebar/members/delete */
			delete: (store, payload) => {
				const { chatId, userId } = payload;
				if (!Type.isNumber(chatId) || !Type.isNumber(userId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('delete', { userId, chatId });
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state, payload) => {
				const { chatId, users } = payload;
				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				users.forEach((id: number) => {
					state.collection[chatId].users.add(id);
				});
			},
			setHasNextPage: (state, payload) => {
				const { chatId, hasNextPage } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].hasNextPage = hasNextPage;
			},
			setLastId: (state, payload) => {
				const { chatId, lastId } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].lastId = lastId;
			},
			setInited: (state, payload) => {
				const { chatId, inited } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].inited = inited;
			},
			delete: (state, payload: {chatId: number, userId: number}) => {
				const { chatId, userId } = payload;
				state.collection[chatId].users.delete(userId);
			},
		};
	}
}
