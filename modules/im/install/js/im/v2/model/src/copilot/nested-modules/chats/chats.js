import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import { chatFieldsConfig } from './field-config';
import { formatFieldsWithConfig } from '../../../utils/validate';

import type { JsonObject } from 'main.core';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { CopilotRole } from '../../../type/copilot';

type ChatsState = {
	collection: {[dialogId: string]: CopilotChat},
}

type CopilotChat = {
	dialogId: string,
	role: string,
}

/* eslint-disable no-param-reassign */
export class ChatsModel extends BuilderModel
{
	getState(): ChatsState
	{
		return {
			collection: {},
		};
	}

	getElementState(): CopilotChat
	{
		return {
			dialogId: '',
			role: '',
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function copilot/chats/getRole */
			getRole: (state) => (dialogId: number): ?CopilotRole => {
				const chat = state.collection[dialogId];
				if (!chat)
				{
					return null;
				}

				return Core.getStore().getters['copilot/roles/getByCode'](chat.role);
			},
			/** @function copilot/chats/getRoleAvatar */
			getRoleAvatar: (state, getters) => (dialogId: number): string => {
				const role = getters.getRole(dialogId);
				if (!role)
				{
					return '';
				}

				return Core.getStore().getters['copilot/roles/getAvatar'](role.code);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function copilot/chats/add */
			add: (store, payload) => {
				if (!payload)
				{
					return;
				}

				const chatsToAdd = Type.isArrayFilled(payload) ? payload : [payload];

				chatsToAdd.forEach((chat) => {
					const preparedChat = { ...this.getElementState(), ...this.formatFields(chat) };
					store.commit('add', preparedChat);
				});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			add: (state, payload) => {
				const { dialogId } = payload;
				state.collection[dialogId] = payload;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, chatFieldsConfig);
	}
}
