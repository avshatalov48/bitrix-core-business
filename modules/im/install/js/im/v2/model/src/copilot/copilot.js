import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { formatFieldsWithConfig } from 'im.v2.model';

import { copilotFieldsConfig } from './format/field-config';
import { ChatsModel } from './nested-modules/chats/chats';
import { MessagesModel } from './nested-modules/messages/messages';
import { RolesModel } from './nested-modules/roles/roles';

import type { JsonObject } from 'main.core';
import type { CopilotRole } from '../type/copilot';
import type { GetterTree, ActionTree, MutationTree, NestedModuleTree } from 'ui.vue3.vuex';

type CopilotModelState = {
	recommendedRoles: string[],
	aiProvider: string,
};

const RECOMMENDED_ROLES_LIMIT = 4;

/* eslint-disable no-param-reassign */
export class CopilotModel extends BuilderModel
{
	getNestedModules(): NestedModuleTree
	{
		return {
			roles: RolesModel,
			messages: MessagesModel,
			chats: ChatsModel,
		};
	}

	getName(): string
	{
		return 'copilot';
	}

	getState(): CopilotModelState
	{
		return {
			recommendedRoles: [],
			aiProvider: '',
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function copilot/getProvider */
			getProvider: (state): string => {
				return state.aiProvider;
			},
			/** @function copilot/getRecommendedRoles */
			getRecommendedRoles: (state) => (): CopilotRole[] => {
				const roles = state.recommendedRoles.map((roleCode) => {
					return Core.getStore().getters['copilot/roles/getByCode'](roleCode);
				});

				return roles.slice(0, RECOMMENDED_ROLES_LIMIT);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function copilot/setRecommendedRoles */
			setRecommendedRoles: (store, payload) => {
				if (!Type.isArrayFilled(payload))
				{
					return;
				}

				store.commit('setRecommendedRoles', payload);
			},
			/** @function copilot/setProvider */
			setProvider: (store, payload) => {
				if (!Type.isStringFilled(payload))
				{
					return;
				}

				store.commit('setProvider', payload);
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			setRecommendedRoles: (state, payload) => {
				state.recommendedRoles = payload;
			},
			setProvider: (state, payload) => {
				state.aiProvider = payload;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, copilotFieldsConfig);
	}
}
