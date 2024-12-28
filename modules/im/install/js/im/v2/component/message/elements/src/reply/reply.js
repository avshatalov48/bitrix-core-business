import { Type } from 'main.core';

import { Parser } from 'im.v2.lib.parser';

import type { ImModelChat, ImModelMessage, ImModelUser } from 'im.v2.model';

const NO_CONTEXT_TAG = 'none';

// @vue/component
export const Reply = {
	name: 'ReplyComponent',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		replyId: {
			type: Number,
			required: true,
		},
		isForward: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		replyMessage(): ?ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.replyId);
		},
		replyMessageChat(): ?ImModelChat
		{
			return this.$store.getters['chats/getByChatId'](this.replyMessage?.chatId);
		},
		replyAuthor(): ?ImModelUser
		{
			return this.$store.getters['users/get'](this.replyMessage.authorId);
		},
		replyTitle(): string
		{
			return this.replyAuthor ? this.replyAuthor.name : this.loc('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
		},
		replyText(): string
		{
			let text = Parser.prepareQuote(this.replyMessage);
			text = Parser.decodeText(text);

			return text;
		},
		isQuoteFromTheSameChat(): boolean
		{
			return this.replyMessage?.chatId === this.dialog.chatId;
		},
		replyContext(): string
		{
			if (!this.isQuoteFromTheSameChat)
			{
				return NO_CONTEXT_TAG;
			}

			if (!this.isForward)
			{
				return `${this.dialogId}/${this.replyId}`;
			}

			return `${this.replyMessageChat.dialogId}/${this.replyId}`;
		},
		canShowReply(): boolean
		{
			return !Type.isNil(this.replyMessage);
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
		<div v-if="canShowReply" class="bx-im-message-quote" :data-context="replyContext">
			<div class="bx-im-message-quote__wrap">
				<div class="bx-im-message-quote__name">
					<div class="bx-im-message-quote__name-text">{{ replyTitle }}</div>
				</div>
				<div class="bx-im-message-quote__text" v-html="replyText"></div>
			</div>
		</div>
	`,
};
