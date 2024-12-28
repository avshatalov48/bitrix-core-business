import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { BuilderModel } from 'ui.vue3.vuex';

import { Layout, EventType } from 'im.v2.const';

import { SettingsModel } from './nested-modules/settings/settings';
import { TariffRestrictionsModel } from './nested-modules/tariff-restrictions/tariff-restrictions';

import type { ActionTree, GetterTree, MutationTree } from 'ui.vue3.vuex';

type ApplicationState = {
	layout: {
		name: string,
		entityId: string,
		contextId: number,
	},
};

export class ApplicationModel extends BuilderModel
{
	getName(): string
	{
		return 'application';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
	{
		return {
			settings: SettingsModel,
			tariffRestrictions: TariffRestrictionsModel,
		};
	}

	getState(): ApplicationState
	{
		return {
			layout:
			{
				name: Layout.chat.name,
				entityId: '',
				contextId: 0,
			},
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function application/getLayout */
			getLayout: (state) => {
				return state.layout;
			},
			/** @function application/isChatOpen */
			isChatOpen: (state) => (dialogId: string): boolean => {
				const allowedLayouts = [Layout.chat.name, Layout.copilot.name, Layout.channel.name, Layout.collab.name];
				if (!allowedLayouts.includes(state.layout.name))
				{
					return false;
				}

				return state.layout.entityId === dialogId.toString();
			},
			isLinesChatOpen: (state) => (dialogId: string): boolean => {
				if (state.layout.name !== Layout.openlines.name && state.layout.name !== Layout.openlinesV2.name)
				{
					return false;
				}

				return state.layout.entityId === dialogId.toString();
			},
			/** @function application/areNotificationsOpen */
			areNotificationsOpen: (state) => {
				return state.layout.name === Layout.notification.name;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function application/setLayout */
			setLayout: (store, payload: {name: string, entityId?: string, contextId?: number}) => {
				const { name, entityId = '', contextId = 0 } = payload;
				if (!Type.isStringFilled(name))
				{
					return;
				}

				const previousLayout = { ...store.state.layout };
				const newLayout = {
					name: this.validateLayout(name),
					entityId: this.validateLayoutEntityId(name, entityId),
					contextId,
				};
				if (previousLayout.name === newLayout.name && previousLayout.entityId === newLayout.entityId)
				{
					return;
				}

				store.commit('updateLayout', {
					layout: newLayout,
				});

				EventEmitter.emit(EventType.layout.onLayoutChange, {
					from: previousLayout,
					to: newLayout,
				});
			},
		};
	}

	/* eslint-disable no-param-reassign */
	getMutations(): MutationTree
	{
		return {
			updateLayout: (state, payload) => {
				state.layout = { ...state.layout, ...payload.layout };
			},
		};
	}

	validateLayout(name: string): string
	{
		if (!Layout[name])
		{
			return Layout.chat.name;
		}

		return name;
	}

	validateLayoutEntityId(name: string, entityId: string): string
	{
		if (!Layout[name])
		{
			return '';
		}

		// TODO check `entityId` by layout name

		return entityId;
	}
}
