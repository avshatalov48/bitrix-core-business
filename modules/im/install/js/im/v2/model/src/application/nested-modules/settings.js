import { BuilderModel, GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

import { Settings, DialogAlignment, NotificationSettingsMode, NotificationSettingsType, NotificationSettingsBlock } from 'im.v2.const';
import { settingsFieldsConfig } from './format/field-config';
import { formatFieldsWithConfig } from '../../utils/validate';

import type { JsonObject } from 'main.core';

type SettingsState = {
	[settingName: string]: any,
	notifications: {
		[moduleId: string]: NotificationSettingsBlock
	}
};
type NotificationOptionPayload = {
	moduleId: string,
	optionName: string,
	type: $Values<typeof NotificationSettingsType>,
	value: boolean
};

/* eslint-disable no-param-reassign */
export class SettingsModel extends BuilderModel
{
	getState(): SettingsState
	{
		return {
			[Settings.appearance.background]: 1,
			[Settings.appearance.alignment]: DialogAlignment.left,

			[Settings.notification.enableSound]: true,
			[Settings.notification.enableAutoRead]: true,
			[Settings.notification.mode]: NotificationSettingsMode.simple,
			[Settings.notification.enableWeb]: true,
			[Settings.notification.enableMail]: true,
			[Settings.notification.enablePush]: true,
			notifications: {},

			[Settings.message.bigSmiles]: true,

			[Settings.recent.showBirthday]: true,
			[Settings.recent.showInvited]: true,
			[Settings.recent.showLastMessage]: true,

			[Settings.desktop.enableRedirect]: true,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function application/settings/get */
			get: (state: SettingsState) => (key: string): any => {
				return state[key];
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function application/settings/set */
			set: (store, payload: {[settingName: string]: any}) => {
				store.commit('set', this.formatFields(payload));
			},
			/** @function application/settings/setNotificationOption */
			setNotificationOption: (store, payload: NotificationOptionPayload) => {
				store.commit('setNotificationOption', payload);
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state: SettingsState, payload: {[settingName: string]: any}) => {
				Object.entries(payload).forEach(([key, value]) => {
					state[key] = value;
				});
			},
			setNotificationOption: (state: SettingsState, payload: NotificationOptionPayload) => {
				const { moduleId, optionName, type, value } = payload;
				const moduleOptions = state.notifications[moduleId];
				if (!moduleOptions?.items?.[optionName])
				{
					return;
				}

				moduleOptions.items[optionName][type] = value;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, settingsFieldsConfig);
	}
}
