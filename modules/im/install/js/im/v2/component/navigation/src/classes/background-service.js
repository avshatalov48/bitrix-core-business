import {Core} from 'im.v2.application.core';
import {RestMethod, Settings} from 'im.v2.const';
import {runAction} from 'im.v2.lib.rest';
import {Logger} from 'im.v2.lib.logger';

export const BackgroundService = {
	changeBackground(backgroundId: string)
	{
		Logger.warn('Navigation: BackgroundService: changeBackground', backgroundId);

		const preparedBackgroundId = Number.parseInt(backgroundId, 10);
		Core.getStore().dispatch('application/settings/set', {
			[Settings.dialog.background]: preparedBackgroundId
		});

		runAction(RestMethod.imV2SettingsGeneralUpdate, {
			data: {
				userId: Core.getUserId(),
				name: Settings.dialog.background,
				value: preparedBackgroundId
			}
		}).catch(error => {
			console.error('Navigation: BackgroundService: error changing background', error);
		});
	}
};