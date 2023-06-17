import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {Utils} from 'im.v2.lib.utils';

import type {ImModelSidebarFavoriteItem} from '../registry';

type FavoritesState = {
	collection: {[chatId: number]: Map<number, ImModelSidebarFavoriteItem>},
	counters: {[chatId: number]: number},
};

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

	getGetters(): Object
	{
		return {
			get: (state) => (chatId: number): ImModelSidebarFavoriteItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].values()].sort((a, b) => b.id - a.id);
			},
			getSize: (state) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].size;
			},
			getCounter: (state) => (chatId: number): number => {
				if (state.counters[chatId])
				{
					return state.counters[chatId];
				}

				return 0;
			},
			isFavoriteMessage: (state) => (chatId: number, messageId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				const chatFavorites = Object.fromEntries(state.collection[chatId]);
				const targetMessage = Object.values(chatFavorites).find(element => element.messageId === messageId);

				return !!targetMessage;
			}
		};
	}

	getActions(): Object
	{
		return {
			setCounter: (store, payload) => {
				if (!Type.isNumber(payload.counter) || !Type.isNumber(payload.chatId))
				{
					return;
				}

				store.commit('setCounter', payload);
			},
			set: (store, payload) => {
				if (Type.isNumber(payload.favorites))
				{
					payload.favorites = [payload.favorites];
				}
				const {chatId, favorites} = payload;

				if (!Type.isArrayFilled(favorites) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					store.state.collection[chatId] = new Map();
				}

				favorites.forEach(favorite => {
					const preparedFavoriteMessage = {...this.getElementState(), ...this.validate(favorite)};
					store.commit('add', {chatId, favorite: preparedFavoriteMessage});
				});
			},
			delete: (store, payload) => {
				const {chatId, id} = payload;
				if (!Type.isNumber(id) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId] || !store.state.collection[chatId].has(id))
				{
					return;
				}

				store.commit('delete', {chatId, id});
			},
			deleteByMessageId: (store, payload: {chatId: number, messageId: number}) => {
				const {chatId, messageId} = payload;
				if (!store.state.collection[chatId])
				{
					return;
				}

				const chatCollection = store.state.collection[chatId];
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

				store.commit('delete', {chatId, id: targetLinkId});
			}
		};
	}

	getMutations(): Object
	{
		return {
			setCounter: (state, payload) => {
				const {chatId, counter} = payload;
				state.counters[chatId] = counter;
			},
			add: (state, payload: {chatId: number, favorite: ImModelSidebarFavoriteItem}) => {
				const {chatId, favorite} = payload;
				state.collection[chatId].set(favorite.id, favorite);
			},
			delete: (state, payload: {chatId: number, id: number}) => {
				const {chatId, id} = payload;
				state.collection[chatId].delete(id);
				state.counters[chatId]--;
			}
		};
	}

	validate(fields: Object): ImModelSidebarFavoriteItem
	{
		const result = {};

		if (Type.isNumber(fields.id))
		{
			result.id = fields.id;
		}

		if (Type.isNumber(fields.messageId))
		{
			result.messageId = fields.messageId;
		}

		if (Type.isNumber(fields.chatId))
		{
			result.chatId = fields.chatId;
		}

		if (Type.isNumber(fields.authorId))
		{
			result.authorId = fields.authorId;
		}

		if (Type.isString(fields.dateCreate))
		{
			result.date = Utils.date.cast(fields.dateCreate);
		}

		return result;
	}
}
