import { Settings } from 'im.v2.const';
import { SettingsService } from 'im.v2.provider.service';

import { CheckboxOption } from '../elements/checkbox';

// @vue/component
export const RecentSection = {
	name: 'RecentSection',
	components: { CheckboxOption },
	data()
	{
		return {};
	},
	computed:
	{
		showBirthday(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showBirthday);
		},
		showInvited(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showInvited);
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
	},
	methods:
	{
		onShowBirthdayChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.recent.showBirthday, newValue);
		},
		onShowInvitedChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.recent.showInvited, newValue);
		},
		onShowLastMessageChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.recent.showLastMessage, newValue);
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
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<CheckboxOption
					:value="showBirthday"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_RECENT_SHOW_BIRTHDAY')"
					@change="onShowBirthdayChange"
				/>
				<CheckboxOption
					:value="showInvited"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_RECENT_SHOW_INVITED')"
					@change="onShowInvitedChange"
				/>
				<CheckboxOption
					:value="showLastMessage"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_RECENT_SHOW_TEXT')"
					@change="onShowLastMessageChange"
				/>
			</div>
		</div>
	`,
};
