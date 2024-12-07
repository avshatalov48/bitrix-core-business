import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import { formatFieldsWithConfig } from '../../../utils/validate';
import { sidebarMeetingFieldsConfig } from './format/field-config';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarMeetingItem } from '../../../registry';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

type MeetingsState = {
	collection: {
		[chatId: number]: ChatState
	},
};

type ChatState = {
	hasNextPage: boolean,
	items: Map<ImModelSidebarMeetingItem>,
	lastId: number
}

type MeetingsPayload = {
	chatId?: number,
	meetings?: Object[],
	hasNextPage?: boolean,
	lastId?: number,
}

/* eslint-disable no-param-reassign */
export class MeetingsModel extends BuilderModel
{
	getState(): MeetingsState
	{
		return {
			collection: {},
			collectionSearch: {},
			historyLimitExceededCollection: {},
		};
	}

	getElementState(): ImModelSidebarMeetingItem
	{
		return {
			id: 0,
			messageId: 0,
			chatId: 0,
			authorId: 0,
			date: new Date(),
			meeting: {
				id: 0,
				title: '',
				dateFrom: new Date(),
				dateTo: new Date(),
				source: '',
			},
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
			/** @function sidebar/meetings/get */
			get: (state) => (chatId: number): ImModelSidebarMeetingItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/meetings/getSearchResultCollection */
			getSearchResultCollection: (state) => (chatId: number): ImModelSidebarMeetingItem[] => {
				if (!state.collectionSearch[chatId])
				{
					return [];
				}

				return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/meetings/getSize */
			getSize: (state) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].items.size;
			},
			/** @function sidebar/meetings/hasNextPage */
			hasNextPage: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].hasNextPage;
			},
			/** @function sidebar/meetings/getLastId */
			getLastId: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].lastId;
			},
			/** @function sidebar/meetings/getSearchResultCollectionLastId */
			getSearchResultCollectionLastId: (state) => (chatId: number): boolean => {
				if (!state.collectionSearch[chatId])
				{
					return false;
				}

				return state.collectionSearch[chatId].lastId;
			},
			/** @function sidebar/meetings/isHistoryLimitExceeded */
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
			/** @function sidebar/meetings/set */
			set: (store, payload) => {
				const { chatId, meetings, hasNextPage, lastId, isHistoryLimitExceeded = false } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });
				if (!Type.isNil(hasNextPage))
				{
					store.commit('setHasNextPage', { chatId, hasNextPage });
				}

				if (!Type.isNil(lastId))
				{
					store.commit('setLastId', { chatId, lastId });
				}

				meetings.forEach((meeting) => {
					const preparedMeeting = { ...this.getElementState(), ...this.formatFields(meeting) };
					store.commit('add', { chatId, meeting: preparedMeeting });
				});
			},
			/** @function sidebar/meetings/setSearch */
			setSearch: (store, payload: MeetingsPayload) => {
				const { chatId, meetings, hasNextPage, lastId, isHistoryLimitExceeded = false } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });
				if (!Type.isNil(hasNextPage))
				{
					store.commit('setHasNextPageSearch', { chatId, hasNextPage });
				}

				if (!Type.isNil(lastId))
				{
					store.commit('setLastIdSearch', { chatId, lastId });
				}

				meetings.forEach((meeting) => {
					const preparedMeeting = { ...this.getElementState(), ...this.formatFields(meeting) };
					store.commit('addSearch', { chatId, meeting: preparedMeeting });
				});
			},
			/** @function sidebar/meetings/delete */
			delete: (store, payload) => {
				const { chatId, id } = payload;
				if (!Type.isNumber(chatId) || !Type.isNumber(id))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('delete', { id, chatId });
			},
			/** @function sidebar/meetings/clearSearch */
			clearSearch: (store) => {
				store.commit('clearSearch', {});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			add: (state, payload: {chatId: number, meeting: ImModelSidebarMeetingItem}) => {
				const { chatId, meeting } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].items.set(meeting.id, meeting);
			},
			addSearch: (state, payload: {chatId: number, meeting: ImModelSidebarMeetingItem}) => {
				const { chatId, meeting } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].items.set(meeting.id, meeting);
			},
			delete: (state, payload: {id: number, chatId: number}) => {
				const { id, chatId } = payload;
				const hasCollectionSearch = !Type.isNil(state.collectionSearch[chatId]);
				if (hasCollectionSearch)
				{
					state.collectionSearch[chatId].items.delete(id);
				}
				state.collection[chatId].items.delete(id);
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
			setHasNextPageSearch: (state, payload: MeetingsPayload) => {
				const { chatId, hasNextPage } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].hasNextPage = hasNextPage;
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
			setLastIdSearch: (state, payload: MeetingsPayload) => {
				const { chatId, lastId } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].lastId = lastId;
			},
			clearSearch: (state) => {
				state.collectionSearch = {};
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
		return formatFieldsWithConfig(fields, sidebarMeetingFieldsConfig);
	}
}
