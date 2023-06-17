import {BuilderModel} from 'ui.vue3.vuex';
import {Type} from 'main.core';

import {Utils} from 'im.v2.lib.utils';

import type {ImModelSidebarLinkItem} from '../registry';

type LinksState = {
	collection: {[chatId: number]: Map<number, ImModelSidebarLinkItem>},
	counters: {[chatId: number]: number},
};

export class LinksModel extends BuilderModel
{
	getState(): LinksState
	{
		return {
			collection: {},
			counters: {}
		};
	}

	getGetters()
	{
		return {
			get: (state) => (chatId: number): ImModelSidebarLinkItem[] => {
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
				if (!state.counters[chatId])
				{
					return 0;
				}

				return state.counters[chatId];
			}
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
				const {chatId, links} = payload;
				if (!Type.isArrayFilled(links) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					store.state.collection[chatId] = new Map();
				}

				links.forEach(link => {
					const preparedLink = {...this.getElementState(), ...this.validate(link)};
					store.commit('add', {chatId, link: preparedLink});
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
			add: (state, payload: {chatId: number, link: ImModelSidebarLinkItem}) => {
				const {chatId, link} = payload;
				state.collection[chatId].set(link.id, link);
			},
			delete: (state, payload: {chatId: number, id: number}) => {
				const {chatId, id} = payload;
				state.collection[chatId].delete(id);
				state.counters[chatId]--;
			}
		};
	}

	validate(fields: Object): ImModelSidebarLinkItem
	{
		const result = {
			richData: {}
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

		if (Type.isString(fields.url.source))
		{
			result.source = fields.url.source;
		}

		if (Type.isString(fields.dateCreate))
		{
			result.date = Utils.date.cast(fields.dateCreate);
		}

		if (Type.isPlainObject(fields.url.richData))
		{
			result.richData = this.validateRichData(fields.url.richData);
		}

		return result;
	}

	validateRichData(richData: Object): Object
	{
		const result = {};

		if (Type.isNumber(richData.id))
		{
			result.id = richData.id;
		}

		if (Type.isString(richData.description))
		{
			result.description = richData.description;
		}

		if (Type.isString(richData.link))
		{
			result.link = richData.link;
		}

		if (Type.isString(richData.name))
		{
			result.name = richData.name;
		}

		if (Type.isString(richData.previewUrl))
		{
			result.previewUrl = richData.previewUrl;
		}

		if (Type.isString(richData.type))
		{
			result.type = richData.type;
		}

		return result;
	}
}