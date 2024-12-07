import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import { sidebarTaskFieldsConfig } from './format/field-config';
import { formatFieldsWithConfig } from '../../../utils/validate';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarTaskItem } from '../../../registry';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

type TasksState = {
	collection: {
		[chatId: number]: ChatState
	},
};

type ChatState = {
	hasNextPage: boolean,
	items: Map<ImModelSidebarTaskItem>,
	lastId: number
}

type TasksPayload = {
	chatId?: number,
	tasks?: Object[],
	hasNextPage?: boolean,
	lastId?: number,
}

/* eslint-disable no-param-reassign */
export class TasksModel extends BuilderModel
{
	getState(): TasksState
	{
		return {
			collection: {},
			collectionSearch: {},
			historyLimitExceededCollection: {},
		};
	}

	getElementState(): ImModelSidebarTaskItem
	{
		return {
			id: 0,
			messageId: 0,
			chatId: 0,
			authorId: 0,
			date: new Date(),
			task: {
				id: 0,
				title: '',
				creatorId: 0,
				responsibleId: 0,
				status: 0,
				statusTitle: '',
				deadline: new Date(),
				state: '',
				color: '',
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
			/** @function sidebar/tasks/get */
			get: (state) => (chatId: number): ImModelSidebarTaskItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/tasks/getSearchResultCollection */
			getSearchResultCollection: (state) => (chatId: number): ImModelSidebarTaskItem[] => {
				if (!state.collectionSearch[chatId])
				{
					return [];
				}

				return [...state.collectionSearch[chatId].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/tasks/getSize */
			getSize: (state) => (chatId: number): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].items.size;
			},
			/** @function sidebar/tasks/hasNextPage */
			hasNextPage: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].hasNextPage;
			},
			/** @function sidebar/tasks/getLastId */
			getLastId: (state) => (chatId: number): boolean => {
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].lastId;
			},
			/** @function sidebar/tasks/getSearchResultCollectionLastId */
			getSearchResultCollectionLastId: (state) => (chatId: number): boolean => {
				if (!state.collectionSearch[chatId])
				{
					return false;
				}

				return state.collectionSearch[chatId].lastId;
			},
			/** @function sidebar/tasks/isHistoryLimitExceeded */
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
			/** @function sidebar/tasks/set */
			set: (store, payload) => {
				const { chatId, tasks, hasNextPage, lastId, isHistoryLimitExceeded = false } = payload;
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

				tasks.forEach((task) => {
					const preparedTask = { ...this.getElementState(), ...this.formatFields(task) };
					store.commit('add', { chatId, task: preparedTask });
				});
			},
			/** @function sidebar/tasks/clearSearch */
			clearSearch: (store) => {
				store.commit('clearSearch', {});
			},
			/** @function sidebar/tasks/setSearch */
			setSearch: (store, payload: TasksPayload) => {
				const { chatId, tasks, hasNextPage, lastId, isHistoryLimitExceeded = false } = payload;
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

				tasks.forEach((task) => {
					const preparedTask = { ...this.getElementState(), ...this.formatFields(task) };
					store.commit('addSearch', { chatId, task: preparedTask });
				});
			},
			/** @function sidebar/tasks/delete */
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
			add: (state, payload: {chatId: number, task: ImModelSidebarTaskItem}) => {
				const { chatId, task } = payload;

				const hasCollection = !Type.isNil(state.collection[chatId]);
				if (!hasCollection)
				{
					state.collection[chatId] = this.getChatState();
				}

				state.collection[chatId].items.set(task.id, task);
			},
			addSearch: (state, payload: {chatId: number, task: ImModelSidebarTaskItem}) => {
				const { chatId, task } = payload;

				const hasCollection = !Type.isNil(state.collectionSearch[chatId]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId] = this.getChatState();
				}

				state.collectionSearch[chatId].items.set(task.id, task);
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
			setHasNextPageSearch: (state, payload: TasksPayload) => {
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
			setLastIdSearch: (state, payload: TasksPayload) => {
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
		return formatFieldsWithConfig(fields, sidebarTaskFieldsConfig);
	}
}
