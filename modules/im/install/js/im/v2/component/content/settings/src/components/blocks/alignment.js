import { Settings, DialogAlignment } from 'im.v2.const';
import { SettingsService } from 'im.v2.provider.service';
import { ThemeManager, BackgroundStyle } from 'im.v2.lib.theme';

import './css/alignment.css';

type AlignmentItem = $Keys<typeof DialogAlignment>;

// @vue/component
export const ChatAlignment = {
	name: 'ChatAlignment',
	emits: ['close'],
	computed:
	{
		DialogAlignment: () => DialogAlignment,
		currentOptionId(): AlignmentItem
		{
			return this.$store.getters['application/settings/get'](Settings.appearance.alignment);
		},
		backgroundStyle(): BackgroundStyle
		{
			return ThemeManager.getCurrentBackgroundStyle();
		},
	},
	methods:
	{
		onOptionClick(optionId: AlignmentItem)
		{
			this.getSettingsService().changeSetting(Settings.appearance.alignment, optionId);
		},
		getSettingsService(): SettingsService
		{
			if (!this.settingsService)
			{
				this.settingsService = new SettingsService();
			}

			return this.settingsService;
		},
	},
	template: `
		<div class="bx-im-settings-alignment__container">
			<div class="bx-im-settings-alignment__list">
				<div
					class="bx-im-settings-alignment__item --left"
					:class="{'--active': currentOptionId === DialogAlignment.left}"
					:style="backgroundStyle"
					@click="onOptionClick(DialogAlignment.left)"
				>
					<div class="bx-im-settings-alignment__item_content"></div>
					<div v-if="currentOptionId === DialogAlignment.left" class="bx-im-settings-alignment__item_checkmark"></div>
				</div>
				<div
					class="bx-im-settings-alignment__item --center"
					:class="{'--active': currentOptionId === DialogAlignment.center}"
					:style="backgroundStyle"
					@click="onOptionClick(DialogAlignment.center)"
				>
					<div class="bx-im-settings-alignment__item_content"></div>
					<div v-if="currentOptionId === DialogAlignment.center" class="bx-im-settings-alignment__item_checkmark"></div>
				</div>
			</div>
		</div>
	`,
};
