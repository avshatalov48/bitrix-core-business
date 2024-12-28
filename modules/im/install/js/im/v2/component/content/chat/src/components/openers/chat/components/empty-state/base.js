import { Type } from 'main.core';

import { ThemeManager } from 'im.v2.lib.theme';
import { RecentService } from 'im.v2.provider.service';

import './css/empty-state.css';

import type { BackgroundStyle } from 'im.v2.lib.theme';

// @vue/component
export const BaseEmptyState = {
	props:
	{
		text: {
			type: String,
			default: '',
		},
		subtext: {
			type: String,
			default: '',
		},
		backgroundId: {
			type: [String, Number],
			default: '',
		},
	},
	computed:
	{
		iconClass(): string
		{
			return this.isEmptyRecent ? '--empty' : '--default';
		},
		preparedText(): string
		{
			if (this.text)
			{
				return this.text;
			}

			if (this.isEmptyRecent)
			{
				return this.loc('IM_CONTENT_CHAT_NO_CHATS_START_MESSAGE');
			}

			return this.loc('IM_CONTENT_CHAT_START_MESSAGE_V2');
		},
		preparedSubtext(): string
		{
			if (this.subtext)
			{
				return this.subtext;
			}

			return '';
		},
		isEmptyRecent(): boolean
		{
			return RecentService.getInstance().getCollection().length === 0;
		},
		backgroundStyle(): BackgroundStyle
		{
			if (Type.isStringFilled(this.backgroundId) || Type.isNumber(this.backgroundId))
			{
				return ThemeManager.getBackgroundStyleById(this.backgroundId);
			}

			return ThemeManager.getCurrentBackgroundStyle();
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-content-chat-start__container" :style="backgroundStyle">
			<div class="bx-im-content-chat-start__content">
				<div class="bx-im-content-chat-start__icon" :class="iconClass"></div>
				<div class="bx-im-content-chat-start__title">
					{{ preparedText }}
				</div>
				<div v-if="preparedSubtext" class="bx-im-content-chat-start__subtitle">
					{{ preparedSubtext }}
				</div>
			</div>
		</div>
	`,
};
