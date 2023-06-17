import {BuilderModel} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';

import type {ImModelMessage} from 'im.v2.model';

export class PinModel extends BuilderModel
{
	getState()
	{
		return {
			collection: {}
		};
	}

	getGetters(): Object
	{
		return {
			getPinned: (state: PinState) => (chatId: number): ImModelMessage[] =>
			{
				if (!state.collection[chatId])
				{
					return [];
				}

				return [...state.collection[chatId]].map(pinnedMessageId => {
					return Core.getStore().getters['messages/getById'](pinnedMessageId);
				});
			},
			isPinned: (state: PinState) => (payload: {chatId: number, messageId: number}): boolean =>
			{
				const {chatId, messageId} = payload;
				if (!state.collection[chatId])
				{
					return false;
				}

				return state.collection[chatId].has(messageId);
			},
		};
	}

	getActions(): Object
	{
		return {
			setPinned: (store, payload: {chatId: number, pinnedMessages: number[]}) =>
			{
				const {chatId, pinnedMessages} = payload;
				if (pinnedMessages.length === 0)
				{
					return;
				}

				store.commit('setPinned', {
					chatId,
					pinnedMessageIds: pinnedMessages
				});
			},
			set: (store, payload: {chatId: number, messageId: number, action: boolean}) =>
			{
				store.commit('set', payload);
			},
			add: (store, payload: {chatId: number, messageId: number}) =>
			{
				store.commit('add', payload);
			},
			delete: (store, payload: {chatId: number, messageId: number}) =>
			{
				store.commit('delete', payload);
			},
		};
	}

	getMutations(): Object
	{
		return {
			setPinned: (state: PinState, payload: {chatId: number, pinnedMessageIds: number[]}) =>
			{
				Logger.warn('Messages/pin model: setPinned mutation', payload);
				const {chatId, pinnedMessageIds} = payload;
				state.collection[chatId] = new Set(pinnedMessageIds.reverse());
			},
			add: (state: PinState, payload: {chatId: number, messageId: number}) =>
			{
				Logger.warn('Messages/pin model: add pin mutation', payload);
				const {chatId, messageId} = payload;
				if (!state.collection[chatId])
				{
					state.collection[chatId] = new Set();
				}

				state.collection[chatId].add(messageId);
			},
			delete: (state: PinState, payload: {chatId: number, messageId: number}) =>
			{
				Logger.warn('Messages/pin model: delete pin mutation', payload);
				const {chatId, messageId} = payload;
				if (!state.collection[chatId])
				{
					return;
				}

				state.collection[chatId].delete(messageId);
			}
		};
	}
}

type PinState = {
	collection: {
		[chatId: string]: Set
	}
};