import { Core } from 'im.v2.application.core';
import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { formatFieldsWithConfig } from '../../../utils/validate';
import { messagesFieldsConfig } from './field-config';

import type { JsonObject } from 'main.core';
import type { CopilotPrompt, CopilotRole, CopilotRoleCode } from '../../../type/copilot';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

type MessagesState = {
	collection: { [key: number]: CopilotRoleCode }
}

type CopilotMessage = {
	role: string
}

/* eslint-disable no-param-reassign */
export class MessagesModel extends BuilderModel
{
	getState(): MessagesState
	{
		return {
			collection: {},
		};
	}

	getElementState(): CopilotMessage
	{
		return {
			id: 0,
			roleCode: '',
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function copilot/messages/getRole */
			getRole: (state) => (messageId: number): ?CopilotRole => {
				const message = state.collection[messageId];
				if (!message)
				{
					return Core.getStore().getters['copilot/roles/getDefault'];
				}

				return Core.getStore().getters['copilot/roles/getByCode'](message.roleCode);
			},
			/** @function copilot/messages/getPrompts */
			getPrompts: (state) => (messageId: number): CopilotPrompt[] => {
				const message = state.collection[messageId];
				if (!message)
				{
					return [];
				}

				return Core.getStore().getters['copilot/roles/getPrompts'](message.roleCode);
			},
			getAvatar: (state, getters) => (messageId: number): string => {
				const role = getters.getRole(messageId);
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
			/** @function copilot/messages/add */
			add: (store, payload) => {
				if (!Type.isArrayFilled(payload))
				{
					return;
				}

				payload.forEach((message) => {
					const preparedMessage = {
						...this.getElementState(),
						...this.formatFields(message),
					};
					store.commit('add', preparedMessage);
				});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			add: (state, payload) => {
				state.collection[payload.id] = payload;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, messagesFieldsConfig);
	}
}
