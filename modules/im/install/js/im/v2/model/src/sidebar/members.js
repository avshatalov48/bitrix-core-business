import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

type MembersState = {
	collection: {[chatId: number]: Set<number>},
};

export class MembersModel extends BuilderModel
{
	getState(): MembersState
	{
		return {
			collection: {},
		};
	}

	getGetters()
	{
		return {
			get: (state) => (chatId: number): Array =>
			{
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId]];
			},
			getSize: (state) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].size;
			},
		};
	}

	getActions(): Object
	{
		return {
			set: (store, payload) =>
			{
				const {chatId, users} = payload;
				if (!Type.isArray(users) || !Type.isNumber(chatId))
				{
					return;
				}

				if (users.length > 0)
				{
					store.commit('set', {chatId, users});
				}
			},
			delete: (store, payload) => {
				const {chatId, userId} = payload;
				if (!Type.isNumber(chatId) || !Type.isNumber(userId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('delete', {userId, chatId});
			}
		};
	}

	getMutations(): Object
	{
		return {
			set: (state, payload) =>
			{
				if (!state.collection[payload.chatId])
				{
					state.collection[payload.chatId] = new Set(payload.users);
				}
				else
				{
					payload.users.forEach(id => {
						state.collection[payload.chatId].add(id);
					});
				}
			},
			delete: (state, payload: {chatId: number, userId: number}) => {
				const {chatId, userId} = payload;
				state.collection[chatId].delete(userId);
			}
		};
	}
}