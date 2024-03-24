import { Type } from 'main.core';

import { BuilderModel } from 'ui.vue3.vuex';

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

/* eslint-disable no-param-reassign */
export class LinksModel extends BuilderModel
{
	getState(): LinksState
	{
		return {
			collection: {},
			counters: {},
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
			set: (store, payload) => {
				const { chatId, links, hasNextPage } = payload;
				if (!Type.isArrayFilled(links) || !Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHasNextPage', { chatId, hasNextPage });

				links.forEach((link) => {
					const preparedLink = { ...this.getElementState(), ...this.formatFields(link) };
					store.commit('add', { chatId, link: preparedLink });
				});
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
			delete: (state, payload: {chatId: number, id: number}) => {
				const { chatId, id } = payload;
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
