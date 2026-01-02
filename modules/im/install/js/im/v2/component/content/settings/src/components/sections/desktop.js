import { DesktopApi, DesktopFeature, DesktopSettingsKey } from 'im.v2.lib.desktop-api';
import { Settings } from 'im.v2.const';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { SettingsService } from 'im.v2.provider.service.settings';

import { CheckboxOption } from '../elements/checkbox';

// @vue/component
export const DesktopSection = {
	name: 'DesktopSection',
	components: { CheckboxOption },
	computed:
	{
		autoStartDesktop(): boolean
		{
			return DesktopApi.getAutostartStatus();
		},
		openPortalLinkInDesktop(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.desktop.enableRedirect);
		},
		openPortalLinkInDesktopPhrase(): string
		{
			if (!DesktopApi.isFeatureSupported(DesktopFeature.openPage.id))
			{
				return this.loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_ALWAYS_OPEN_CHAT');
			}

			return this.loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_ALWAYS_OPEN_PORTAL_LINK_V2');
		},
		openLinksInSlider(): boolean
		{
			const sliderBindingStatus = DesktopApi.getCustomSetting(DesktopSettingsKey.sliderBindingsStatus, '1');

			return sliderBindingStatus === '1';
		},
		isRedirectAvailable(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.isDesktopRedirectAvailable);
		},
	},
	methods:
	{
		onAutoStartDesktopChange(newValue: boolean)
		{
			DesktopApi.setAutostartStatus(newValue);
		},
		onOpenPortalLinkInDesktopChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.desktop.enableRedirect, newValue);
		},
		onOpenLinksInSliderChange(newValue: boolean)
		{
			this.setSliderBindingStatus(newValue);
			DesktopApi.setCustomSetting(DesktopSettingsKey.sliderBindingsStatus, newValue ? '1' : '0');
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
					v-if="isRedirectAvailable"
					:value="openPortalLinkInDesktop"
					:text="openPortalLinkInDesktopPhrase"
					@change="onOpenPortalLinkInDesktopChange"
				/>
				<CheckboxOption
					:value="openLinksInSlider"
					:text="loc('IM_CONTENT_SETTINGS_OPTION_DESKTOP_OPEN_LINKS_IN_SLIDER_V2')"
					@change="onOpenLinksInSliderChange"
				/>
			</div>
		</div>
	`,
};
