import { Core } from 'im.v2.application.core';
import { RestMethod, UserStatus } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

export class SettingsService
{
	changeSetting(settingName: string, value: any): void
	{
		Logger.warn('SettingsService: changeSetting', settingName, value);
		void Core.getStore().dispatch('application/settings/set', {
			[settingName]: value,
		});

		const payload = {
			data: {
				userId: Core.getUserId(),
				name: settingName,
				value,
			},
		};

		runAction(RestMethod.imV2SettingsGeneralUpdate, payload)
			.catch(([error]) => {
				console.error('SettingsService: changeSetting error', error);
			});
	}

	changeStatus(status: string): void
	{
		if (!UserStatus[status])
		{
			return;
		}

		Logger.warn(`SettingsService: changeStatus to ${status}`);
		void Core.getStore().dispatch('users/setStatus', { status });
		void Core.getStore().dispatch('application/settings/set', { status });

		const payload = { STATUS: status };
		Core.getRestClient().callMethod(RestMethod.imUserStatusSet, payload)
			.catch((result: RestResult) => {
				console.error('SettingsService: changeStatus error', result.error());
			});
	}
}
