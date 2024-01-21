import { RecentService } from 'im.v2.provider.service';

import type { JsonObject } from 'main.core';

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

			return this.loc('IM_CONTENT_CHAT_START_MESSAGE_V2');
		},
		subtext(): string
		{
			return '';
		},
		isEmptyRecent(): boolean
		{
			return RecentService.getInstance().getCollection().length === 0;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-chat-start__container">
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
