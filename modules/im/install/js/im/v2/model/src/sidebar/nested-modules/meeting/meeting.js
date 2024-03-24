import type { JsonObject } from 'main.core';
import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { formatFieldsWithConfig } from '../../../utils/validate';
import { sidebarMeetingFieldsConfig } from './format/field-config';

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

/* eslint-disable no-param-reassign */
export class MeetingsModel extends BuilderModel
{
	getState(): MeetingsState
	{
		return {
			collection: {},
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
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/meetings/set */
			set: (store, payload) => {
				const { chatId, meetings, hasNextPage, lastId } = payload;
				if (!Type.isArrayFilled(meetings) || !Type.isNumber(chatId))
				{
					return;
				}

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
			delete: (state, payload: {id: number, chatId: number}) => {
				const { id, chatId } = payload;
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
			setLastId: (state, payload) => {
				const { chatId, lastId } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].lastId = lastId;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, sidebarMeetingFieldsConfig);
	}
}
