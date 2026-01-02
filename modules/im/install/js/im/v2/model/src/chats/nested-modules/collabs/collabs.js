import { BuilderModel } from 'ui.vue3.vuex';
import { Type } from 'main.core';

import { formatFieldsWithConfig } from 'im.v2.model';

import { collabFieldsConfig } from './format/field-config';

import type { JsonObject } from 'main.core';
import type { ActionTree, MutationTree, GetterTree } from 'ui.vue3.vuex';
import type { ImModelCollabInfo } from 'im.v2.model';

type CollabsState = {
	collection: {
		[chatId: string]: ImModelCollabInfo
	},
};

type CollabCounterEntity = 'tasks' | 'calendar';

export class CollabsModel extends BuilderModel
{
	getState(): CollabsState
	{
		return {
			collection: {},
		};
	}

	getElementState(): ImModelCollabInfo
	{
		return {
			collabId: 0,
			guestCount: 0,
			entities: {
				tasks: {
					counter: 0,
					url: '',
				},
				files: {
					counter: 0,
					url: '',
				},
				calendar: {
					counter: 0,
					url: '',
				},
			},
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function chats/collabs/getByChatId */
			getByChatId: (state: CollabsState) => (chatId: number): ?ImModelCollabInfo => {
				return state.collection[chatId] ?? this.getElementState();
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function chats/collabs/set */
			set: (store, payload: { chatId: string, collabInfo: ImModelCollabInfo }) => {
				const { chatId, collabInfo } = payload;
				if (!Type.isPlainObject(collabInfo))
				{
					return;
				}

				store.commit('set', {
					chatId,
					collabInfo: this.#formatFields(collabInfo),
				});
			},
			/** @function chats/collabs/setCounter */
			setCounter: (store, payload: { chatId: string, entity: CollabCounterEntity, counter: number }) => {
				const { chatId, entity, counter } = payload;
				const state: CollabsState = store.state;
				const currentRecord = state.collection[chatId];
				if (!currentRecord || !currentRecord.entities[entity])
				{
					return;
				}

				store.commit('setCounter', { chatId, entity, counter });
			},
			/** @function chats/collabs/setGuestCount */
			setGuestCount: (store, payload: { chatId: string, guestCount: number }) => {
				const { chatId, guestCount } = payload;
				const state: CollabsState = store.state;
				const currentRecord = state.collection[chatId];
				if (!currentRecord)
				{
					return;
				}

				store.commit('setGuestCount', { chatId, guestCount });
			},
		};
	}

	/* eslint-disable no-param-reassign */
	getMutations(): MutationTree
	{
		return {
			set: (state: CollabsState, payload: { chatId: string, collabInfo: ImModelCollabInfo }) => {
				const { chatId, collabInfo } = payload;

				state.collection[chatId] = collabInfo;
			},
			setCounter: (state: CollabsState, payload: { chatId: string, entity: CollabCounterEntity, counter: number }) => {
				const { chatId, entity, counter } = payload;

				const currentRecord = state.collection[chatId];
				currentRecord.entities[entity].counter = counter;
			},
			setGuestCount: (state: CollabsState, payload: { chatId: string, guestCount: number }) => {
				const { chatId, guestCount } = payload;

				const currentRecord = state.collection[chatId];
				currentRecord.guestCount = guestCount;
			},
		};
	}

	#formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, collabFieldsConfig);
	}
}
