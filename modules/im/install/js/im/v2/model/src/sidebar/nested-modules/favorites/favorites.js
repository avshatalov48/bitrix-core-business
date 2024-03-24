import type { JsonObject } from 'main.core';
import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { formatFieldsWithConfig } from '../../../utils/validate';
import { sidebarFavoritesFieldsConfig } from './format/field-config';

import type { ImModelSidebarFavoriteItem } from '../../../registry';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

type FavoritesState = {
	collection: {
		[chatId: number]: ChatState
	},
	counters: {[chatId: number]: number},
};

type ChatState = {
	hasNextPage: boolean,
	items: Map<ImModelSidebarFavoriteItem>,
	lastId: number
}

/* eslint-disable no-param-reassign */
export class FavoritesModel extends BuilderModel
{
	getState(): FavoritesState
	{
		return {
			collection: {},
			counters: {},
		};
	}

	getElementState(): ImModelSidebarFavoriteItem
	{
		return {
			id: 0,
			messageId: 0,
			chatId: 0,
			authorId: 0,
			date: new Date(),
		};
	}

	getChatState(): ChatState
	{
		return {
			items: new Map(),
			hasNextPage: true,
			lastId: 0,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function sidebar/favorites/get */
			get: (state) => (chatId: number): ImModelSidebarFavoriteItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/favorites/getSize */
			getSize: (state) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].items.size;
			},
			/** @function sidebar/favorites/getCounter */
			getCounter: (state) => (chatId: number): number => {
				if (state.counters[chatId])
				{
					return state.counters[chatId];
				}

				return 0;
			},
			/** @function sidebar/favorites/isFavoriteMessage */
			isFavoriteMessage: (state) => (chatId: number, messageId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				const chatFavorites = Object.fromEntries(state.collection[chatId].items);
				const targetMessage = Object.values(chatFavorites).find((element) => element.messageId === messageId);

				return Boolean(targetMessage);
			},
			/** @function sidebar/favorites/hasNextPage */
			hasNextPage: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].hasNextPage;
			},
			/** @function sidebar/favorites/getLastId */
			getLastId: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].lastId;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/favorites/setCounter */
			setCounter: (store, payload) => {
				if (!Type.isNumber(payload.counter) || !Type.isNumber(payload.chatId))
				{
					return;
				}

				store.commit('setCounter', payload);
			},
			/** @function sidebar/favorites/set */
			set: (store, payload) => {
				if (Type.isNumber(payload.favorites))
				{
					payload.favorites = [payload.favorites];
				}
				const { chatId, favorites, hasNextPage, lastId } = payload;

				if (!Type.isArrayFilled(favorites) || !Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHasNextPage', { chatId, hasNextPage });
				store.commit('setLastId', { chatId, lastId });

				favorites.forEach((favorite) => {
					const preparedFavoriteMessage = { ...this.getElementState(), ...this.formatFields(favorite) };
					store.commit('add', { chatId, favorite: preparedFavoriteMessage });
				});
			},
			/** @function sidebar/favorites/delete */
			delete: (store, payload) => {
				const { chatId, id } = payload;
				if (!Type.isNumber(id) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId] || !store.state.collection[chatId].items.has(id))
				{
					return;
				}

				store.commit('delete', { chatId, id });
			},
			/** @function sidebar/favorites/deleteByMessageId */
			deleteByMessageId: (store, payload: {chatId: number, messageId: number}) => {
				const { chatId, messageId } = payload;
				if (!store.state.collection[chatId])
				{
					return;
				}

				const chatCollection = store.state.collection[chatId].items;
				let targetLinkId = null;
				for (const [linkId, linkObject] of chatCollection)
				{
					if (linkObject.messageId === messageId)
					{
						targetLinkId = linkId;
						break;
					}
				}

				if (!targetLinkId)
				{
					return;
				}

				store.commit('delete', { chatId, id: targetLinkId });
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			setHasNextPage: (state, payload) => {
				const { chatId, hasNextPage } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].hasNextPage = hasNextPage;
			},
			setCounter: (state, payload) => {
				const { chatId, counter } = payload;
				state.counters[chatId] = counter;
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
			add: (state, payload: {chatId: number, favorite: ImModelSidebarFavoriteItem}) => {
				const { chatId, favorite } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].items.set(favorite.id, favorite);
			},
			delete: (state, payload: {chatId: number, id: number}) => {
				const { chatId, id } = payload;
				state.collection[chatId].items.delete(id);
				state.counters[chatId]--;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, sidebarFavoritesFieldsConfig);
	}
}
