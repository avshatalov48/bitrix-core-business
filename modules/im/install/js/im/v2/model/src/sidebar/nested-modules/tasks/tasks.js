import type { JsonObject } from 'main.core';
import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { sidebarTaskFieldsConfig } from './format/field-config';
import { formatFieldsWithConfig } from '../../../utils/validate';

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

/* eslint-disable no-param-reassign */
export class TasksModel extends BuilderModel
{
	getState(): TasksState
	{
		return {
			collection: {},
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
			/** @function sidebar/tasks/hasNextPage */
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
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/tasks/set */
			set: (store, payload) => {
				const { chatId, tasks, hasNextPage, lastId } = payload;
				if (!Type.isArrayFilled(tasks) || !Type.isNumber(chatId))
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

				tasks.forEach((task) => {
					const preparedTask = { ...this.getElementState(), ...this.formatFields(task) };
					store.commit('add', { chatId, task: preparedTask });
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
		return formatFieldsWithConfig(fields, sidebarTaskFieldsConfig);
	}
}
