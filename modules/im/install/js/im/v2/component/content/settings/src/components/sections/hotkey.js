import { Settings } from 'im.v2.const';
import { SettingsService } from 'im.v2.provider.service';
import { Utils } from 'im.v2.lib.utils';

import { RadioOption, type RadioOptionItem } from '../elements/radio';

// @vue/component
export const HotkeySection = {
	name: 'HotkeySection',
	components: { RadioOption },
	data(): {}
	{
		return {};
	},
	computed:
	{
		sendByEnter(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.hotkey.sendByEnter);
		},
		sendCombinationItems(): RadioOptionItem[]
		{
			const ctrlKey = Utils.platform.isMac() ? '&#8984;' : 'Ctrl';
			const enterSubtext = this.loc('IM_CONTENT_SETTINGS_OPTION_HOTKEY_NEW_LINE', {
				'#HOTKEY#': 'Shift + Enter',
			});
			const ctrlEnterSubtext = this.loc('IM_CONTENT_SETTINGS_OPTION_HOTKEY_NEW_LINE', {
				'#HOTKEY#': 'Enter',
			});

			return [
				{
					value: true,
					text: 'Enter',
					subtext: enterSubtext,
					selected: this.sendByEnter === true,
				},
				{
					value: false,
					text: `${ctrlKey} + Enter`,
					subtext: ctrlEnterSubtext,
					html: true,
					selected: this.sendByEnter === false,
				},
			];
		},
		isMac(): boolean
		{
			return Utils.platform.isMac();
		},
	},
	methods:
	{
		onSendByEnterChange(newValue: boolean)
		{
			this.getSettingsService().changeSetting(Settings.hotkey.sendByEnter, newValue);
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
					{{ loc('IM_CONTENT_SETTINGS_OPTION_HOTKEY_SEND_COMBINATION') }}
				</div>
				<RadioOption
					:items="sendCombinationItems"
					@change="onSendByEnterChange"
				/>
			</div>
		</div>
	`,
};
