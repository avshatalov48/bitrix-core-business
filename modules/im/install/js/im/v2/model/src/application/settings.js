import {Type} from 'main.core';
import {BuilderModel, GetterTree, ActionTree, MutationTree} from 'ui.vue3.vuex';

import {Settings} from 'im.v2.const';

type SettingsState = {[settingName: string]: any};

export class SettingsModel extends BuilderModel
{
	getState()
	{
		return {
			[Settings.application.darkTheme]: false,
			[Settings.application.enableSound]: true,

			[Settings.dialog.bigSmiles]: true,
			[Settings.dialog.background]: 1,

			[Settings.recent.showBirthday]: true,
			[Settings.recent.showInvited]: true,
			[Settings.recent.showLastMessage]: true,
		};
	}

	getGetters(): GetterTree
	{
		return {
			get: (state: SettingsState) => (key: string): any =>
			{
				return state[key];
			}
		};
	}

	getActions(): ActionTree
	{
		return {
			set: (store, payload: {[settingName: string]: any}) =>
			{
				store.commit('set', this.validate(payload));
			}
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state: SettingsState, payload: {[settingName: string]: any}) => {
				Object.entries(payload).forEach(([key, value]) => {
					state[key] = value;
				});
			}
		};
	}

	validate(fields): {[settingName: string]: any}
	{
		const result = {};

		if (Type.isBoolean(fields[Settings.application.darkTheme]))
		{
			result[Settings.application.darkTheme] = fields[Settings.application.darkTheme];
		}

		if (Type.isBoolean(fields[Settings.application.enableSound]))
		{
			result[Settings.application.enableSound] = fields[Settings.application.enableSound];
		}

		if (Type.isBoolean(fields[Settings.dialog.bigSmiles]))
		{
			result[Settings.dialog.bigSmiles] = fields[Settings.dialog.bigSmiles];
		}

		if (Type.isStringFilled(fields[Settings.dialog.background]))
		{
			fields[Settings.dialog.background] = Number.parseInt(fields[Settings.dialog.background], 10);
		}
		if (Type.isNumber(fields[Settings.dialog.background]))
		{
			result[Settings.dialog.background] = fields[Settings.dialog.background];
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

		return result;
	}
}