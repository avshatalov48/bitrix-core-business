import type { JsonObject } from 'main.core';
import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

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

type FavoritesPayload = {
	chatId?: number,
	favorites?: Object[],
	hasNextPage?: boolean,
	lastId?: number,
}

/* eslint-disable no-param-reassign */
export class FavoritesModel extends BuilderModel
{
	getState(): FavoritesState
	{
		return {
			collection: {},
			counters: {},
			collectionSearch: {},
			historyLimitExceededCollection: {},
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
			/** @function sidebar/favorites/getSearchResultCollectionLastId */
			getSearchResultCollectionLastId: (state) => (chatId: number): number => {
				if (!state.collectionSearch[chatId])
				{
					return 0;
				}

				return state.collectionSearch[chatId].lastId;
			},
			/** @function sidebar/favorites/hasNextPageSearch */
			hasNextPageSearch: (state) => (chatId: number): boolean => {
				if (!state.collectionSearch[chatId])
				{
					return false;
				}

				return state.collectionSearch[chatId].hasNextPage;
			},
			/** @function sidebar/favorites/getSearchResultCollection */
			getSearchResultCollection: (state) => (chatId: number): ImModelSidebarFavoriteItem[] => {
				if (!state.collectionSearch[chatId])
				{
					return [];
				}

				return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/favorites/isHistoryLimitExceeded */
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
				const { chatId, favorites, hasNextPage, lastId, isHistoryLimitExceeded = false } = payload;

				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });
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
			/** @function sidebar/favorites/setSearch */
			setSearch: (store, payload: FavoritesPayload) => {
				const { chatId, favorites, hasNextPage, lastId, isHistoryLimitExceeded = false } = payload;

				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });
				store.commit('setHasNextPageSearch', { chatId, hasNextPage });
				store.commit('setLastIdSearch', { chatId, lastId });

				favorites.forEach((favorite) => {
					const preparedFavoriteMessage = { ...this.getElementState(), ...this.formatFields(favorite) };
					store.commit('addSearch', { chatId, favorite: preparedFavoriteMessage });
				});
			},
			/** @function sidebar/favorites/clearSearch */
			clearSearch: (store) => {
				store.commit('clearSearch', {});
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
			setHasNextPageSearch: (state, payload: FavoritesPayload) => {
				const { chatId, hasNextPage } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].hasNextPage = hasNextPage;
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
			addSearch: (state, payload: {chatId: number, favorite: ImModelSidebarFavoriteItem}) => {
				const { chatId, favorite } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].items.set(favorite.id, favorite);
			},
			clearSearch: (state) => {
				state.collectionSearch = {};
			},
			setLastIdSearch: (state, payload: FavoritesPayload) => {
				const { chatId, lastId } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].lastId = lastId;
			},
			delete: (state, payload: {chatId: number, id: number}) => {
				const { chatId, id } = payload;
				const hasCollectionSearch = !Type.isNil(state.collectionSearch[chatId]);
				if (hasCollectionSearch)
				{
					state.collectionSearch[chatId].items.delete(id);
				}
				state.collection[chatId].items.delete(id);
				state.counters[chatId]--;
			},
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

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, sidebarFavoritesFieldsConfig);
	}
}
