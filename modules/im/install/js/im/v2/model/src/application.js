import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { BuilderModel } from 'ui.vue3.vuex';

import { Layout, EventType } from 'im.v2.const';

import { SettingsModel } from './application/settings';

import type { ActionTree, GetterTree, MutationTree } from 'ui.vue3.vuex';

export class ApplicationModel extends BuilderModel
{
	getName(): string
	{
		return 'application';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
	{
		return { settings: SettingsModel };
	}

	getState()
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
				const allowedLayouts = [Layout.chat.name, Layout.copilot.name];
				if (!allowedLayouts.includes(state.layout.name))
				{
					return false;
				}

				return state.layout.entityId === dialogId.toString();
			},
			isLinesChatOpen: (state) => (dialogId: string): boolean => {
				if (state.layout.name !== Layout.openlines.name)
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
			setLayout: (store, payload: {layoutName: string, entityId?: string, contextId?: number}) => {
				const { layoutName, entityId = '', contextId = 0 } = payload;
				if (!Type.isStringFilled(layoutName))
				{
					return;
				}

				const previousLayout = { ...store.state.layout };
				const newLayout = {
					name: this.validateLayout(layoutName),
					entityId: this.validateLayoutEntityId(layoutName, entityId),
					contextId,
				};
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

	validateLayout(layoutName: string): string
	{
		if (!Layout[layoutName])
		{
			return Layout.chat.name;
		}

		return layoutName;
	}

	validateLayoutEntityId(layoutName: string, entityId: string): string
	{
		if (!Layout[layoutName])
		{
			return '';
		}

		// TODO check `entityId` by layout name

		return entityId;
	}
}
