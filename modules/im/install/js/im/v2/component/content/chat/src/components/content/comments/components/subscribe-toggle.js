import { Toggle, ToggleSize } from 'im.v2.component.elements';
import { CommentsService } from 'im.v2.provider.service';

import '../css/follow-toggle.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const SubscribeToggle = {
	name: 'SubscribeToggle',
	components: { Toggle },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		ToggleSize: () => ToggleSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		postMessageId(): ?number
		{
			return this.$store.getters['messages/comments/getMessageIdByChatId'](this.dialog.chatId);
		},
		isSubscribed(): boolean
		{
			return this.$store.getters['messages/comments/isUserSubscribed'](this.postMessageId);
		},
	},
	methods:
	{
		onToggleClick(): void
		{
			if (this.isSubscribed)
			{
				CommentsService.unsubscribe(this.postMessageId);

				return;
			}

			CommentsService.subscribe(this.postMessageId);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div @click="onToggleClick" class="bx-im-comments-header-follow__container">
			<div class="bx-im-comments-header-follow__text">{{ loc('IM_CONTENT_COMMENTS_FOLLOW_TOGGLE_TEXT') }}</div>
			<Toggle :size="ToggleSize.M" :isEnabled="isSubscribed" />
		</div>
	`,
};
