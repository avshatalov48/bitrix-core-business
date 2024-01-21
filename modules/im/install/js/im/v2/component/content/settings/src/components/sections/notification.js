import { Settings, NotificationSettingsMode } from 'im.v2.const';
import { SettingsService } from 'im.v2.provider.service';
import { showNotificationsModeSwitchConfirm } from 'im.v2.lib.confirm';

import { CheckboxOption } from '../elements/checkbox';
import { RadioOption, type RadioOptionItem } from '../elements/radio';
import { SimpleNotificationList } from './components/simple-notification-list';
import { ExpertNotificationList } from './components/expert-mode/expert-notification-list';
import { NotificationService } from './classes/notification-service';

import type { JsonObject } from 'main.core';

type NotificationMode = $Keys<typeof NotificationSettingsMode>;

// @vue/component
export const NotificationSection = {
	name: 'NotificationSection',
	components: { CheckboxOption, RadioOption, SimpleNotificationList, ExpertNotificationList },
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		enableSound(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.notification.enableSound);
		},
		enableAutoRead(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.notification.enableAutoRead);
		},
		notificationMode(): NotificationMode
		{
			return this.$store.getters['application/settings/get'](Settings.notification.mode);
		},
		notificationModeOptions(): RadioOptionItem[]
		{
			return [
				{
					value: NotificationSettingsMode.simple,
					text: this.loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_SIMPLE_MODE'),
					selected: this.notificationMode === NotificationSettingsMode.simple,
				},
				{
					value: NotificationSettingsMode.expert,
					text: this.loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_EXPERT_MODE'),
					selected: this.notificationMode === NotificationSettingsMode.expert,
				},
			];
		},
		notificationListComponent(): SimpleNotificationList | ExpertNotificationList
		{
			return this.notificationMode === 'simple' ? SimpleNotificationList : ExpertNotificationList;
		},
	},
	methods:
	{
		onEnableSoundChange(newValue: boolean): void
		{
			this.getSettingsService().changeSetting(Settings.notification.enableSound, newValue);
		},
		onEnableAutoReadChange(newValue: boolean): void
		{
			this.getSettingsService().changeSetting(Settings.notification.enableAutoRead, newValue);
		},
		async onNotificationModeChange(newValue: NotificationMode): void
		{
			const isChangingToSimple = newValue === NotificationSettingsMode.simple;
			if (isChangingToSimple)
			{
				this.changeLocalNotificationMode(NotificationSettingsMode.simple);
				const confirmResult = await showNotificationsModeSwitchConfirm();
				if (!confirmResult)
				{
					this.changeLocalNotificationMode(NotificationSettingsMode.expert);

					return;
				}
			}

			void NotificationService.switchScheme(newValue);
		},
		async changeLocalNotificationMode(newValue: NotificationMode): void
		{
			this.$store.dispatch('application/settings/set', {
				[Settings.notification.mode]: newValue,
			});
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
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_BLOCK_FOCUS') }}
				</div>
				<CheckboxOption
					:value="enableSound"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_ENABLE_SOUND')"
					@change="onEnableSoundChange"
				/>
				<CheckboxOption
					:value="enableAutoRead"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_AUTO_READ')"
					@change="onEnableAutoReadChange"
				/>
			</div>
		</div>
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_NOTIFICATION_BLOCK_MODE') }}
				</div>
				<RadioOption :items="notificationModeOptions" @change="onNotificationModeChange" />
			</div>
		</div>
		<component :is="notificationListComponent" />
	`,
};
