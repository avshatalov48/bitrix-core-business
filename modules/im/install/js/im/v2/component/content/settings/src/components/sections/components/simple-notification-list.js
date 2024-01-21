import { Core } from 'im.v2.application.core';
import { SettingsService } from 'im.v2.provider.service';
import { Settings } from 'im.v2.const';

import { CheckboxOption } from '../../elements/checkbox';

import type { JsonObject } from 'main.core';

type CurrentUser = { email: string };

// @vue/component
export const SimpleNotificationList = {
	name: 'SimpleNotificationList',
	components: { CheckboxOption },
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		enableWeb(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.notification.enableWeb);
		},
		enableMail(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.notification.enableMail);
		},
		enablePush(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.notification.enablePush);
		},
		enableMailText(): string
		{
			return this.loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_MAIL', {
				'#MAIL#': this.userEmail,
			});
		},
		userEmail(): string
		{
			const { currentUser: { email } }: { currentUser: CurrentUser } = Core.getApplicationData();

			return email;
		},
	},
	methods:
	{
		onEnableWebChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.notification.enableWeb, newValue);
		},
		onEnableMailChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.notification.enableMail, newValue);
		},
		onEnablePushChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.notification.enablePush, newValue);
		},
		getSettingsService(): SettingsService
		{
			if (!this.settingsService)
			{
				this.settingsService = new SettingsService();
			}

			return this.settingsService;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_BLOCK_SIMPLE_MODE_TITLE') }}
				</div>
				<CheckboxOption
					:value="enableWeb"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_WEB')"
					@change="onEnableWebChange"
				/>
				<CheckboxOption
					:value="enableMail"
					:text="enableMailText"
					@change="onEnableMailChange"
				/>
				<CheckboxOption
					:value="enablePush"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_PUSH_V1')"
					@change="onEnablePushChange"
				/>
			</div>
		</div>
	`,
};
