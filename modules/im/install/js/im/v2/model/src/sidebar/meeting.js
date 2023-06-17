import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {Utils} from 'im.v2.lib.utils';

import type {ImModelSidebarMeetingItem} from '../registry';

type MeetingsState = {
	collection: {[chatId: number]: Map<number, ImModelSidebarMeetingItem>},
};

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
				source: ''
			}
		};
	}

	getGetters(): Object
	{
		return {
			get: (state) => (chatId: number): ImModelSidebarMeetingItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId].values()].sort((a, b) => b.id - a.id);
			},
			getSize: (state) => (chatId: string): number => {
				if (!state.collection[chatId])
				{
					return 0;
				}

				return state.collection[chatId].size;
			}
		};
	}

	getActions(): Object
	{
		return {
			set: (store, payload) => {
				const {chatId, meetings} = payload;
				if (!Type.isArrayFilled(meetings) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					store.state.collection[chatId] = new Map();
				}

				meetings.forEach(meeting => {
					const preparedMeeting = {...this.getElementState(), ...this.validate(meeting)};
					store.commit('add', {chatId, meeting: preparedMeeting});
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
			add: (state, payload: {chatId: number, meeting: ImModelSidebarMeetingItem}) => {
				const {chatId, meeting} = payload;
				state.collection[chatId].set(meeting.id, meeting);
			},
			delete: (state, payload: {id: number, chatId: number}) => {
				const {id, chatId} = payload;
				state.collection[chatId].delete(id);
			}
		};
	}

	validate(fields: Object): ImModelSidebarMeetingItem
	{
		const result = {
			meeting: {}
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

		if (Type.isPlainObject(fields.calendar))
		{
			result.meeting = this.validateMeeting(fields.calendar);
		}

		return result;
	}

	validateMeeting(meeting)
	{
		const result = {};

		if (Type.isNumber(meeting.id))
		{
			result.id = meeting.id;
		}

		if (Type.isString(meeting.title))
		{
			result.title = meeting.title;
		}

		if (Type.isString(meeting.dateFrom))
		{
			result.dateFrom = Utils.date.cast(meeting.dateFrom);
		}

		if (Type.isString(meeting.dateTo))
		{
			result.dateTo = Utils.date.cast(meeting.dateTo);
		}

		if (Type.isString(meeting.source))
		{
			result.source = meeting.source;
		}

		return result;
	}
}
