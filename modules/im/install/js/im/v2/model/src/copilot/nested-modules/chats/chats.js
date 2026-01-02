import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import { chatFieldsConfig } from './field-config';
import { formatFieldsWithConfig } from '../../../utils/validate';

import type { JsonObject } from 'main.core';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { ImModelCopilotAIModel, ImModelCopilotRole } from '../../../registry';

type ChatsState = {
	collection: {[dialogId: string]: CopilotChat},
}

type CopilotChat = {
	dialogId: string,
	role: string,
	aiModel: string,
}

const AI_MODEL_DEFAULT_NAME = 'none';

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
			aiModel: '',
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function copilot/chats/getRole */
			getRole: (state) => (dialogId: string): ?ImModelCopilotRole => {
				const chat = state.collection[dialogId];
				if (!chat)
				{
					return null;
				}

				return Core.getStore().getters['copilot/roles/getByCode'](chat.role);
			},
			/** @function copilot/chats/getRoleAvatar */
			getRoleAvatar: (state, getters) => (dialogId: string): string => {
				const role = getters.getRole(dialogId);
				if (!role)
				{
					return '';
				}

				return Core.getStore().getters['copilot/roles/getAvatar'](role.code);
			},
			/** @function copilot/chats/getAIModel */
			getAIModel: (state) => (dialogId: string): ?ImModelCopilotAIModel => {
				const chat = state.collection[dialogId];
				if (!chat)
				{
					return null;
				}

				const aiModelList = Core.getStore().getters['copilot/getAIModels'];

				const currentAiModel = aiModelList.find((aiModel) => aiModel.code === chat.aiModel);

				return currentAiModel ?? AI_MODEL_DEFAULT_NAME;
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
			/** @function copilot/chats/updateModel */
			updateModel: (store, payload: { dialogId: string, aiModel: string }) => {
				if (!payload || !store.state.collection[payload.dialogId])
				{
					return;
				}

				store.commit('updateModel', payload);
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
			updateModel: (state, payload) => {
				const { dialogId, aiModel } = payload;
				state.collection[dialogId].aiModel = aiModel;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, chatFieldsConfig);
	}
}
