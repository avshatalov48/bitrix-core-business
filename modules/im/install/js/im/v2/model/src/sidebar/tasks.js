import {BuilderModel} from 'ui.vue3.vuex';
import {Type} from 'main.core';

import {Utils} from 'im.v2.lib.utils';

import type {ImModelSidebarTaskItem} from '../registry';

type TasksState = {
	collection: {[chatId: number]: Map<number, ImModelSidebarTaskItem>},
};

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
				source: ''
			}
		};
	}

	getGetters(): Object
	{
		return {
			get: (state) => (chatId: number): ImModelSidebarTaskItem[] => {
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
		};
	}

	getActions(): Object
	{
		return {
			set: (store, payload) => {
				const {chatId, tasks} = payload;
				if (!Type.isArrayFilled(tasks) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					store.state.collection[chatId] = new Map();
				}

				tasks.forEach(task => {
					const preparedTask = {...this.getElementState(), ...this.validate(task)};
					store.commit('add', {chatId, task: preparedTask});
				});
			},
			delete: (store, payload) => {
				const {chatId, id} = payload;
				if (!Type.isNumber(chatId) || !Type.isNumber(id))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('delete', {id, chatId});
			}
		};
	}

	getMutations(): Object
	{
		return {
			add: (state, payload: {chatId: number, task: ImModelSidebarTaskItem}) => {
				const {chatId, task} = payload;
				state.collection[chatId].set(task.id, task);
			},
			delete: (state, payload: {id: number, chatId: number}) => {
				const {id, chatId} = payload;
				state.collection[chatId].delete(id);
			}
		};
	}

	validate(fields: Object): ImModelSidebarTaskItem
	{
		const result = {
			task: {}
		};

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

		if (Type.isPlainObject(fields.task))
		{
			result.task = this.validateTask(fields.task);
		}

		return result;
	}

	validateTask(task)
	{
		const result = {};

		if (Type.isNumber(task.id))
		{
			result.id = task.id;
		}

		if (Type.isString(task.title))
		{
			result.title = task.title;
		}

		if (Type.isNumber(task.creatorId))
		{
			result.creatorId = task.creatorId;
		}

		if (Type.isNumber(task.responsibleId))
		{
			result.responsibleId = task.responsibleId;
		}

		if (Type.isNumber(task.status))
		{
			result.status = task.status;
		}

		if (Type.isString(task.statusTitle))
		{
			result.statusTitle = task.statusTitle;
		}

		if (Type.isString(task.deadline))
		{
			result.deadline = Utils.date.cast(task.deadline);
		}

		if (Type.isString(task.state))
		{
			result.state = task.state;
		}

		if (Type.isString(task.color))
		{
			result.color = task.color;
		}

		if (Type.isString(task.source))
		{
			result.source = task.source;
		}

		return result;
	}
}
