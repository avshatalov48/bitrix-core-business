import { Loc } from 'main.core';

import { ThemeManager } from 'im.v2.lib.theme';
import { RecentService } from 'im.v2.provider.service';
import { Layout } from 'im.v2.const';

import '../css/empty-state.css';

import type { JsonObject } from 'main.core';
import type { ImModelLayout } from 'im.v2.model';
import type { BackgroundStyle } from 'im.v2.lib.theme';

// @vue/component
export const EmptyState = {
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		iconClass(): string
		{
			return this.isEmptyRecent ? '--empty' : '--default';
		},
		text(): string
		{
			if (this.isEmptyRecent)
			{
				return this.loc('IM_CONTENT_CHAT_NO_CHATS_START_MESSAGE');
			}

			if (this.isChannelLayout)
			{
				return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_V3');
			}

			return this.loc('IM_CONTENT_CHAT_START_MESSAGE_V2');
		},
		subtext(): string
		{
			if (this.isChannelLayout)
			{
				return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_SUBTITLE');
			}

			return '';
		},
		isEmptyRecent(): boolean
		{
			return RecentService.getInstance().getCollection().length === 0;
		},
		isChannelLayout(): boolean
		{
			return this.layout.name === Layout.channel.name;
		},
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		backgroundStyle(): BackgroundStyle
		{
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
					{{ text }}
				</div>
				<div v-if="subtext" class="bx-im-content-chat-start__subtitle">
					{{ subtext }}
				</div>
			</div>
		</div>
	`,
};
