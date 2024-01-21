import { ChatDialog } from 'im.v2.component.dialog.chat';
import { Settings } from 'im.v2.const';
import { ThemeManager } from 'im.v2.lib.theme';

import { ChatBackground } from '../blocks/background';
import { ChatAlignment } from '../blocks/alignment';
import { DemoManager } from './classes/demo-manager';

import type { BackgroundStyle } from 'im.v2.lib.theme';

import './css/appearance.css';

// @vue/component
export const AppearanceSection = {
	name: 'AppearanceSection',
	components: { ChatDialog, ChatBackground, ChatAlignment },
	data(): {}
	{
		return {};
	},
	computed:
	{
		containerClasses(): string[]
		{
			const alignment = this.$store.getters['application/settings/get'](Settings.appearance.alignment);

			return [`--${alignment}-align`];
		},
		backgroundStyle(): BackgroundStyle
		{
			return ThemeManager.getCurrentBackgroundStyle();
		},
	},
	created()
	{
		DemoManager.initModels();
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-content-chat__container bx-im-settings-appearance__demo-chat_container" :class="containerClasses" :style="backgroundStyle">
					<ChatDialog :dialogId="'settings'" />
				</div>
			</div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_APPEARANCE_BACKGROUND') }}
				</div>
				<ChatBackground />
			</div>
			<div class="bx-im-settings-section-content__separator"></div>
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ loc('IM_CONTENT_SETTINGS_OPTION_APPEARANCE_ALIGNMENT') }}
				</div>
				<ChatAlignment />
			</div>
		</div>
	`,
};
