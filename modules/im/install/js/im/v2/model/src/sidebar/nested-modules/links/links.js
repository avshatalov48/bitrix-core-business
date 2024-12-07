import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import { sidebarLinksFieldsConfig } from './format/field-config';
import { formatFieldsWithConfig } from '../../../utils/validate';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarLinkItem } from '../../../registry';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

type LinksState = {
	collection: {
		[chatId: number]: ChatState
	},
	counters: {[chatId: number]: number},
};

type ChatState = {
	hasNextPage: boolean,
	items: Map<ImModelSidebarLinkItem>,
}

type LinksPayload = {
	chatId?: number,
	links?: Object[],
	hasNextPage?: boolean,
	isHistoryLimitExceeded: boolean,
}

/* eslint-disable no-param-reassign */
export class LinksModel extends BuilderModel
{
	getState(): LinksState
	{
		return {
			collection: {},
			collectionSearch: {},
			counters: {},
			historyLimitExceededCollection: {},
		};
	}

	getElementState(): ImModelSidebarLinkItem
	{
		return {
			id: 0,
			messageId: 0,
			chatId: 0,
			authorId: 0,
			source: '',
			date: new Date(),
			richData: {
				id: null,
				description: null,
				link: null,
				name: null,
				previewUrl: null,
				type: null,
			},
		};
	}

	getChatState(): ChatState
	{
		return {
			items: new Map(),
			hasNextPage: true,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function sidebar/links/get */
			get: (state) => (chatId: number): ImModelSidebarLinkItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/links/getSize */
			getSize: (state) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].items.size;
			},
			/** @function sidebar/links/getCounter */
			getCounter: (state) => (chatId: number): number => {
				if (!state.counters[chatId])
				{
					return 0;
				}

				return state.counters[chatId];
			},
			/** @function sidebar/links/hasNextPage */
			hasNextPage: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].hasNextPage;
			},
			/** @function sidebar/links/hasNextPageSearch */
			hasNextPageSearch: (state) => (chatId: number): boolean => {
				if (!state.collectionSearch[chatId])
				{
					return false;
				}

				return state.collectionSearch[chatId].hasNextPage;
			},
			/** @function sidebar/links/getSearchResultCollectionSize */
			getSearchResultCollectionSize: (state) => (chatId: number): number => {
				if (!state.collectionSearch[chatId])
				{
					return 0;
				}

				return state.collectionSearch[chatId].items.size;
			},
			/** @function sidebar/links/getSearchResultCollection */
			getSearchResultCollection: (state) => (chatId: number): ImModelSidebarLinkItem[] => {
				if (!state.collectionSearch[chatId])
				{
					return [];
				}

				return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/links/isHistoryLimitExceeded */
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
			/** @function sidebar/links/setCounter */
			setCounter: (store, payload) => {
				if (!Type.isNumber(payload.counter) || !Type.isNumber(payload.chatId))
				{
					return;
				}

				store.commit('setCounter', payload);
			},
			/** @function sidebar/links/set */
			set: (store, payload: LinksPayload) => {
				const { chatId, links, hasNextPage, isHistoryLimitExceeded = false } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHasNextPage', { chatId, hasNextPage });
				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });

				links.forEach((link) => {
					const preparedLink = { ...this.getElementState(), ...this.formatFields(link) };
					store.commit('add', { chatId, link: preparedLink });
				});
			},
			/** @function sidebar/links/setSearch */
			setSearch: (store, payload: LinksPayload) => {
				const { chatId, links, hasNextPage, isHistoryLimitExceeded = false } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHasNextPageSearch', { chatId, hasNextPage });
				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });

				links.forEach((link) => {
					const preparedLink = { ...this.getElementState(), ...this.formatFields(link) };
					store.commit('addSearch', { chatId, link: preparedLink });
				});
			},
			/** @function sidebar/links/clearSearch */
			clearSearch: (store) => {
				store.commit('clearSearch', {});
			},
			/** @function sidebar/links/delete */
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
			setHasNextPageSearch: (state, payload: LinksPayload) => {
				const { chatId, hasNextPage } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].hasNextPage = hasNextPage;
			},
			setHistoryLimitExceeded: (state, payload) => {
				const { chatId, isHistoryLimitExceeded } = payload;
				if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded)
				{
					return;
				}

				state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
			},
			setCounter: (state, payload) => {
				const { chatId, counter } = payload;
				state.counters[chatId] = counter;
			},
			add: (state, payload: {chatId: number, link: ImModelSidebarLinkItem}) => {
				const { chatId, link } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].items.set(link.id, link);
			},
			addSearch: (state, payload: {chatId: number, link: ImModelSidebarLinkItem}) => {
				const { chatId, link } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].items.set(link.id, link);
			},
			clearSearch: (state) => {
				state.collectionSearch = {};
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
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, sidebarLinksFieldsConfig);
	}
}
