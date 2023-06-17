import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {BuilderModel} from 'ui.vue3.vuex';

import {Layout, EventType} from 'im.v2.const';

import {SettingsModel} from './application/settings';

export class ApplicationModel extends BuilderModel
{
	getName()
	{
		return 'application';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
	{
		return {settings: SettingsModel};
	}

	getState()
	{
		return {
			layout:
			{
				name: Layout.chat.name,
				entityId: '',
				contextId: 0
			}
		};
	}

	getGetters()
	{
		return {
			getLayout: state =>
			{
				return state.layout;
			},
			isChatOpen: state => (dialogId: string): boolean =>
			{
				if (!state.layout.name === Layout.chat.name)
				{
					return false;
				}

				return state.layout.entityId === dialogId.toString();
			},
			areNotificationsOpen: state =>
			{
				return state.layout.name === Layout.notification.name;
			}
		};
	}

	getActions()
	{
		return {
			setLayout: (store, payload: {layoutName: string, entityId?: string, contextId?: number}) =>
			{
				const {layoutName, entityId = '', contextId = 0} = payload;
				if (!Type.isStringFilled(layoutName))
				{
					return false;
				}

				const previousLayout = {...store.state.layout};
				const newLayout = {
					name: this.validateLayout(layoutName),
					entityId: this.validateLayoutEntityId(layoutName, entityId),
					contextId: contextId
				};
				store.commit('update', {
					layout: newLayout
				});

				EventEmitter.emit(EventType.layout.onLayoutChange, {
					from: previousLayout,
					to: newLayout
				});
			}
		};
	}

	getMutations()
	{
		return {
			update: (state, payload) => {
				Object.keys(payload).forEach((group) => {
					Object.entries(payload[group]).forEach(([key, value]) => {
						state[group][key] = value;
					});
				});
			}
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