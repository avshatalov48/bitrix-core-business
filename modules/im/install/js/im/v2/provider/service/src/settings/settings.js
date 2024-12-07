import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

export class SettingsService
{
	changeSetting(settingName: string, value: any): Promise
	{
		Logger.warn('SettingsService: changeSetting', settingName, value);
		Core.getStore().dispatch('application/settings/set', {
			[settingName]: value,
		});

		return runAction(RestMethod.imV2SettingsGeneralUpdate, {
			data: {
				userId: Core.getUserId(),
				name: settingName,
				value,
			},
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('SettingsService: changeSetting error', error);
		});
	}
}
