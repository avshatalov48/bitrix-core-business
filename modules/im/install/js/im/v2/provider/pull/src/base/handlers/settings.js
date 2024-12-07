import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';

import { SettingsUpdateParams } from '../../types/settings';

export class SettingsPullHandler
{
	handleSettingsUpdate(params: SettingsUpdateParams)
	{
		Logger.warn('SettingsPullHandler: handleSettingsUpdate', params);
		Object.entries(params).forEach(([optionName, optionValue]) => {
			Core.getStore().dispatch('application/settings/set', {
				[optionName]: optionValue,
			});
		});
	}
}
