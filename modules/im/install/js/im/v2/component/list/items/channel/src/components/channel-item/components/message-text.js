import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import { Core } from 'im.v2.application.core';
import { ChatTitle } from 'im.v2.component.elements';
import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelRecentItem, ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const MessageText = {
	name: 'MessageText',
	components: { ChatTitle },
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
		recentItem(): ImModelRecentItem
		{
			return this.item;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
		},
		message(): ImModelMessage
		{
			return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
		},
		formattedDate(): string
		{
			return this.formatDate(this.message.date);
		},
		isLastMessageAuthor(): boolean
		{
			return this.message.authorId === Core.getUserId();
		},
		lastMessageAuthorAvatar(): string
		{
			const author: ImModelUser = this.$store.getters['users/get'](this.message.authorId);

			if (!author)
			{
				return '';
			}

			return author.avatar;
		},
		lastMessageAuthorAvatarStyle(): Object
		{
			return { backgroundImage: `url('${this.lastMessageAuthorAvatar}')` };
		},
		messageText(): string
		{
			if (this.message.isDeleted)
			{
				return this.loc('IM_LIST_RECENT_DELETED_MESSAGE');
			}

			const formattedText = Parser.purifyRecent(this.recentItem);
			if (!formattedText)
			{
				return this.loc('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
			}

			return formattedText;
		},
		formattedMessageText(): string
		{
			const SPLIT_INDEX = 27;

			return Utils.text.insertUnseenWhitespace(this.messageText, SPLIT_INDEX);
		},
	},
	methods:
	{
		formatDate(date): string
		{
			return DateFormatter.formatByTemplate(date, DateTemplate.recent);
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-list-channel-item__message_container">
			<span class="bx-im-list-channel-item__message_text">
				<span v-if="isLastMessageAuthor" class="bx-im-list-channel-item__message_author-icon --self"></span>
				<template v-else-if="message.authorId">
					<span v-if="lastMessageAuthorAvatar" :style="lastMessageAuthorAvatarStyle" class="bx-im-list-channel-item__message_author-icon --user"></span>
					<span v-else class="bx-im-list-channel-item__message_author-icon --user --default"></span>
				</template>
				<span class="bx-im-list-channel-item__message_text_content">{{ formattedMessageText }}</span>
			</span>
		</div>
	`,
};
