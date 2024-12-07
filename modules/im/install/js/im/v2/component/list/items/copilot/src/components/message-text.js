import { Core } from 'im.v2.application.core';
import { Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';
import { AvatarSize, MessageAvatar } from 'im.v2.component.elements';

import type { JsonObject } from 'main.core';
import type { ImModelUser, ImModelChat, ImModelRecentItem, ImModelMessage } from 'im.v2.model';

// @vue/component
export const MessageText = {
	name: 'MessageText',
	components: { MessageAvatar },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		recentItem(): ImModelRecentItem
		{
			return this.item;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.recentItem.dialogId, true);
		},
		message(): ImModelMessage
		{
			return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
		hiddenMessageText(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
		},
		isLastMessageAuthor(): boolean
		{
			if (!this.message)
			{
				return false;
			}

			return this.message.authorId === Core.getUserId();
		},
		lastMessageAuthorAvatar(): string
		{
			const authorDialog = this.$store.getters['chats/get'](this.message.authorId);

			if (!authorDialog)
			{
				return '';
			}

			return authorDialog.avatar;
		},
		lastMessageAuthorAvatarStyle(): Object
		{
			return { backgroundImage: `url('${this.lastMessageAuthorAvatar}')` };
		},
		messageText(): string
		{
			const formattedText = Parser.purifyRecent(this.recentItem);
			if (!formattedText)
			{
				return this.hiddenMessageText;
			}

			return formattedText;
		},
		formattedMessageText(): string
		{
			const SPLIT_INDEX = 27;

			return Utils.text.insertUnseenWhitespace(this.messageText, SPLIT_INDEX);
		},
		preparedDraftContent(): string
		{
			const phrase = this.loc('IM_LIST_RECENT_MESSAGE_DRAFT_2');
			const PLACEHOLDER_LENGTH = '#TEXT#'.length;
			const prefix = phrase.slice(0, -PLACEHOLDER_LENGTH);

			return `
				<span class="bx-im-list-copilot-item__message_draft-prefix">${prefix}</span>
				<span class="bx-im-list-copilot-item__message_text_content">${this.formattedDraftText}</span>
			`;
		},
		formattedDraftText(): string
		{
			return Parser.purify({ text: this.recentItem.draft.text, showIconIfEmptyText: false });
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-list-copilot-item__message_container">
			<span class="bx-im-list-copilot-item__message_text">
				<span v-if="recentItem.draft.text && dialog.counter === 0" v-html="preparedDraftContent"></span>
				<span v-else-if="!showLastMessage">{{ hiddenMessageText }}</span>
				<template v-else>
					<span v-if="isLastMessageAuthor" class="bx-im-list-copilot-item__message_author-icon --self"></span>
					<span v-else-if="message.authorId" class="bx-im-list-copilot-item__message_author-icon --user">
						<MessageAvatar 
							:messageId="message.id"
							:authorId="message.authorId"
							:size="AvatarSize.XXS" 
						/>
					</span>
					<span class="bx-im-list-copilot-item__message_text_content">{{ formattedMessageText }}</span>
				</template>
			</span>
		</div>
	`,
};
