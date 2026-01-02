import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { rolesFieldsConfig } from './field-config';
import { formatFieldsWithConfig } from '../../../utils/validate';

import type { JsonObject } from 'main.core';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { ImModelCopilotPrompt, ImModelCopilotRole, ImModelCopilotRoleCode, ImModelCopilotAvatarSize } from 'im.v2.model';

type RolesState = {
	roles: {[ImModelCopilotRoleCode]: ImModelCopilotRole}
}
export const AvatarSizes: {[ImModelCopilotAvatarSize]: string} = Object.freeze({
	S: 'small',
	M: 'medium',
	L: 'large',
});

/* eslint-disable no-param-reassign */
export class RolesModel extends BuilderModel
{
	getState(): RolesState
	{
		return {
			roles: {},
		};
	}

	getElementState(): ImModelCopilotRole
	{
		return {
			code: '',
			name: '',
			desc: '',
			default: false,
			avatar: {
				small: '',
				medium: '',
				large: '',
			},
			prompts: [],
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function copilot/roles/get */
			get: (state) => (): ImModelCopilotRole[] => {
				return Object.values(state.roles);
			},
			/** @function copilot/roles/getByCode */
			getByCode: (state, getters) => (code: string): ImModelCopilotRole[] => {
				return state.roles[code] ?? getters.getDefault;
			},
			/** @function copilot/roles/getPrompts */
			getPrompts: (state, getters) => (roleCode: string): ImModelCopilotPrompt[] => {
				if (!state.roles[roleCode])
				{
					return getters.getDefault?.prompts ?? [];
				}

				return state.roles[roleCode].prompts;
			},
			/** @function copilot/roles/getDefault */
			getDefault: (state): ImModelCopilotRole => {
				return Object.values(state.roles).find((role: ImModelCopilotRole) => role.default);
			},
			/** @function copilot/roles/getAvatar */
			getAvatar: (state, getters) => (roleCode: string, size: ImModelCopilotAvatarSize = 'M'): string => {
				if (!state.roles[roleCode])
				{
					return getters.getDefault?.prompts ?? [];
				}

				return state.roles[roleCode].avatar[AvatarSizes[size]];
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function copilot/roles/add */
			add: (store, payload) => {
				const roles = Object.values(payload);
				if (!Type.isArrayFilled(roles))
				{
					return;
				}

				roles.forEach((role) => {
					const preparedRole = { ...this.getElementState(), ...this.formatFields(role) };
					store.commit('add', preparedRole);
				});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			add: (state, payload) => {
				state.roles[payload.code] = payload;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, rolesFieldsConfig);
	}
}
