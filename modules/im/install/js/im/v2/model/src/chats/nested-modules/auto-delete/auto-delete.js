import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { formatFieldsWithConfig } from 'im.v2.model';
import { AutoDeleteDelay } from 'im.v2.const';

import { autoDeleteFieldsConfig } from './format/fields-config';

import type { JsonObject } from 'main.core';
import type { ActionTree, MutationTree, GetterTree } from 'ui.vue3.vuex';

type ChatId = number;
type Delay = number;
type AutoDeleteState = {
	collection: Map<ChatId, Delay>
};

type AutoDeletePayload = {
	chatId: number,
	delay: number,
};

export class AutoDeleteModel extends BuilderModel
{
	getState(): AutoDeleteState
	{
		return {
			collection: new Map(),
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function chats/autoDelete/isEnabled */
			isEnabled: (state: AutoDeleteState) => (chatId: ChatId): boolean => {
				return state.collection.has(chatId) ?? false;
			},
			/** @function chats/autoDelete/getDelay */
			getDelay: (state: AutoDeleteState) => (chatId: ChatId): Delay => {
				return state.collection.get(chatId) ?? AutoDeleteDelay.Off;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function chats/autoDelete/set */
			set: (store, rawPayload: AutoDeletePayload | AutoDeletePayload[]) => {
				let payload = rawPayload;
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.forEach((element: AutoDeletePayload) => {
					const formattedElement = this.#formatFields(element);
					const { delay } = formattedElement;
					if (delay === AutoDeleteDelay.Off)
					{
						store.commit('delete', formattedElement);

						return;
					}

					store.commit('set', formattedElement);
				});
			},
		};
	}

	/* eslint-disable no-param-reassign */
	getMutations(): MutationTree
	{
		return {
			set: (state: AutoDeleteState, payload: { chatId: ChatId, delay: Delay }) => {
				const { chatId, delay } = payload;

				state.collection.set(chatId, delay);
			},
			delete: (state: AutoDeleteState, payload: { chatId: ChatId, delay: Delay }) => {
				const { chatId } = payload;

				state.collection.delete(chatId);
			},
		};
	}

	#formatFields(fields: JsonObject): AutoDeletePayload
	{
		return formatFieldsWithConfig(fields, autoDeleteFieldsConfig);
	}
}
