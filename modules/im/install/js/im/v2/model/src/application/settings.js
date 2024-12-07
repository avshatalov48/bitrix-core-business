import { Type } from 'main.core';
import { BuilderModel, GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

import { Settings, DialogAlignment } from 'im.v2.const';

type SettingsState = {[settingName: string]: any};

/* eslint-disable no-param-reassign */
export class SettingsModel extends BuilderModel
{
	getState()
	{
		return {
			[Settings.appearance.background]: 1,
			[Settings.appearance.alignment]: DialogAlignment.left,

			[Settings.notification.enableSound]: true,

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
				store.commit('set', this.validate(payload));
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
		};
	}

	validate(fields): {[settingName: string]: any}
	{
		const result = {};

		if (Type.isBoolean(fields[Settings.notification.enableSound]))
		{
			result[Settings.notification.enableSound] = fields[Settings.notification.enableSound];
		}

		if (Type.isBoolean(fields[Settings.message.bigSmiles]))
		{
			result[Settings.message.bigSmiles] = fields[Settings.message.bigSmiles];
		}

		if (
			Type.isStringFilled(fields[Settings.appearance.background])
			|| Type.isNumber(fields[Settings.appearance.background])
		)
		{
			result[Settings.appearance.background] = Number.parseInt(fields[Settings.appearance.background], 10);
		}

		if (Type.isString(fields[Settings.appearance.alignment]))
		{
			result[Settings.appearance.alignment] = fields[Settings.appearance.alignment];
		}

		if (Type.isBoolean(fields[Settings.recent.showBirthday]))
		{
			result[Settings.recent.showBirthday] = fields[Settings.recent.showBirthday];
		}

		if (Type.isBoolean(fields[Settings.recent.showInvited]))
		{
			result[Settings.recent.showInvited] = fields[Settings.recent.showInvited];
		}

		if (Type.isBoolean(fields[Settings.recent.showLastMessage]))
		{
			result[Settings.recent.showLastMessage] = fields[Settings.recent.showLastMessage];
		}

		if (Type.isStringFilled(fields[Settings.hotkey.sendByEnter]))
		{
			result[Settings.hotkey.sendByEnter] = fields[Settings.hotkey.sendByEnter] === 'Y' || fields[Settings.hotkey.sendByEnter] === '1';
		}

		if (Type.isBoolean(fields[Settings.hotkey.sendByEnter]))
		{
			result[Settings.hotkey.sendByEnter] = fields[Settings.hotkey.sendByEnter];
		}

		if (Type.isBoolean(fields[Settings.desktop.enableRedirect]))
		{
			result[Settings.desktop.enableRedirect] = fields[Settings.desktop.enableRedirect];
		}

		return result;
	}
}
