import { Core } from 'im.v2.application.core';
import { NotificationSettingsMode, RestMethod, Settings } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';

export const NotificationService = {
	async switchScheme(newScheme: $Keys<typeof NotificationSettingsMode>): Promise
	{
		void Core.getStore().dispatch('application/settings/set', {
			[Settings.notification.mode]: newScheme,
		});

		const newNotificationsSettings = await runAction(RestMethod.imV2SettingsNotifySwitchScheme, {
			data: {
				userId: Core.getUserId(),
				scheme: newScheme,
			},
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('NotificationService: switchScheme error', error);
		});

		return Core.getStore().dispatch('application/settings/set', {
			notifications: newNotificationsSettings,
		});
	},

	changeExpertOption(payload: { moduleId: string, optionName: string, type: string, value: boolean }): Promise
	{
		const { moduleId, optionName, type, value } = payload;
		Core.getStore().dispatch('application/settings/setNotificationOption', {
			moduleId,
			optionName,
			type,
			value,
		});

		return runAction(RestMethod.imV2SettingsNotifyUpdate, {
			data: {
				userId: Core.getUserId(),
				moduleId,
				name: optionName,
				type,
				value,
			},
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('NotificationService: changeExpertOption error', error);
		});
	},
};
