import { DesktopApi, DesktopFeature, DesktopSettingsKey } from 'im.v2.lib.desktop-api';
import { showDesktopConfirm, showDesktopRestartConfirm } from 'im.v2.lib.confirm';
import { Settings } from 'im.v2.const';
import { SettingsService } from 'im.v2.provider.service';

import { CheckboxOption } from '../elements/checkbox';

// @vue/component
export const DesktopSection = {
	name: 'DesktopSection',
	components: { CheckboxOption },
	data(): {}
	{
		return {};
	},
	computed:
	{
		twoWindowMode(): boolean
		{
			return DesktopApi.isTwoWindowMode();
		},
		autoStartDesktop(): boolean
		{
			return DesktopApi.getAutostartStatus();
		},
		openChatInDesktop(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.desktop.enableRedirect);
		},
		openLinksInSlider(): boolean
		{
			const sliderBindingStatus = DesktopApi.getCustomSetting(DesktopSettingsKey.sliderBindingsStatus, '1');

			return sliderBindingStatus === '1';
		},
		sendTelemetry(): boolean
		{
			return DesktopApi.getTelemetryStatus();
		},
	},
	methods:
	{
		async onTwoWindowModeChange(newValue: boolean)
		{
			DesktopApi.setTwoWindowMode(newValue);
			if (!DesktopApi.isFeatureSupported(DesktopFeature.restart.id))
			{
				void showDesktopConfirm();

				return;
			}

			const userChoice = await showDesktopRestartConfirm();
			if (userChoice === true)
			{
				DesktopApi.restart();
			}
		},
		onAutoStartDesktopChange(newValue: boolean)
		{
			DesktopApi.setAutostartStatus(newValue);
		},
		onOpenChatInDesktopChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.desktop.enableRedirect, newValue);
		},
		onOpenLinksInSliderChange(newValue: boolean)
		{
			this.setSliderBindingStatus(newValue);
			DesktopApi.setCustomSetting(DesktopSettingsKey.sliderBindingsStatus, newValue ? '1' : '0');
		},
		onSendTelemetryChange(newValue: boolean)
		{
			DesktopApi.setTelemetryStatus(newValue);
		},
		setSliderBindingStatus(flag: boolean)
		{
			if (flag === true)
			{
				BX.SidePanel.Instance.enableAnchorBinding();

				return;
			}

			BX.SidePanel.Instance.disableAnchorBinding();
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
					{{ loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_BLOCK_STARTUP') }}
				</div>
				<CheckboxOption
					:value="twoWindowMode"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_TWO_WINDOW_MODE')"
					@change="onTwoWindowModeChange"
				/>
				<CheckboxOption
					:value="autoStartDesktop"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_AUTO_START')"
					@change="onAutoStartDesktopChange"
				/>
			</div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_BLOCK_LINKS') }}
				</div>
				<CheckboxOption
					:value="openChatInDesktop"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_ALWAYS_OPEN_CHAT')"
					@change="onOpenChatInDesktopChange"
				/>
				<CheckboxOption
					:value="openLinksInSlider"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_OPEN_LINKS_IN_SLIDER')"
					@change="onOpenLinksInSliderChange"
				/>
			</div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_BLOCK_REST') }}
				</div>
				<CheckboxOption
					:value="sendTelemetry"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_SEND_TELEMETRY')"
					@change="onSendTelemetryChange"
				/>
			</div>
		</div>
	`,
};
