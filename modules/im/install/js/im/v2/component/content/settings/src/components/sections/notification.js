import { Settings } from 'im.v2.const';
import { SettingsService } from 'im.v2.provider.service';

import { CheckboxOption } from '../elements/checkbox';

// @vue/component
export const NotificationSection = {
	name: 'NotificationSection',
	components: { CheckboxOption },
	data()
	{
		return {};
	},
	computed:
	{
		enableSound(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.notification.enableSound);
		},
	},
	methods:
	{
		onEnableSoundChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.notification.enableSound, newValue);
		},
		getSettingsService(): SettingsService
		{
			if (!this.settingsService)
			{
				this.settingsService = new SettingsService();
			}

			return this.settingsService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-settings-section-content__block">
			<div class="bx-im-settings-section-content__block_title">
				{{ loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_BLOCK_SOUND') }}
			</div>
			<CheckboxOption
				:value="enableSound"
				:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_SOUND')"
				@change="onEnableSoundChange"
			/>
		</div>
	`,
};
