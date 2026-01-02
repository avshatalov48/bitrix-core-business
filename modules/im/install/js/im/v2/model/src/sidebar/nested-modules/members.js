import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import type { JsonObject } from 'main.core';
import type { ActionTree, MutationTree, GetterTree } from 'ui.vue3.vuex';

type MembersState = {
	collection: { [chatId: number]: ChatState }
};

type ChatState = {
	users: Set<number>,
	inited: boolean,
	hasNextPage: boolean,
	nextCursor: MembersPaginationCursor | null,
};

type SetMembersPayload = {
	chatId: number,
	users: number[],
	hasNextPage?: boolean,
};

type SetCursorPayload = {
	chatId: number,
	nextCursor: MembersPaginationCursor,
};

type MembersPaginationCursor = JsonObject;

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
			nextCursor: null,
			inited: false,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function sidebar/members/get */
			get: (state: MembersState) => (chatId: number): number[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].users];
			},
			/** @function sidebar/members/getSize */
			getSize: (state: MembersState) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].users.size;
			},
			/** @function sidebar/members/hasNextPage */
			hasNextPage: (state: MembersState) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].hasNextPage;
			},
			/** @function sidebar/members/getNextCursor */
			getNextCursor: (state: MembersState) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].nextCursor;
			},
			/** @function sidebar/members/getInited */
			getInited: (state: MembersState) => (chatId: number): boolean => {
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
			set: (store, payload: SetMembersPayload) => {
				const { chatId, users, hasNextPage, nextCursor } = payload;

				if (!Type.isNil(hasNextPage))
				{
					store.commit('setHasNextPage', { chatId, hasNextPage });
				}

				store.commit('setInited', { chatId, inited: true });

				if (users.length > 0)
				{
					store.commit('set', { chatId, users });
				}
			},
			/** @function sidebar/members/setNextCursor */
			setNextCursor: (store, payload: SetCursorPayload) => {
				store.commit('setNextCursor', payload);
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
			set: (state: MembersState, payload) => {
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
			setHasNextPage: (state: MembersState, payload) => {
				const { chatId, hasNextPage } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].hasNextPage = hasNextPage;
			},
			setNextCursor: (state: MembersState, payload: { chatId: number, nextCursor: MembersPaginationCursor }) => {
				const { chatId, nextCursor } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].nextCursor = nextCursor;
			},
			setInited: (state: MembersState, payload) => {
				const { chatId, inited } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].inited = inited;
			},
			delete: (state: MembersState, payload: {chatId: number, userId: number}) => {
				const { chatId, userId } = payload;
				state.collection[chatId].users.delete(userId);
			},
		};
	}
}
