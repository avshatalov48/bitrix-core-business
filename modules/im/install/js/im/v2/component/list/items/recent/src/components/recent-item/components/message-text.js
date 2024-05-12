import { Text, type JsonObject } from 'main.core';
import { DateTimeFormat } from 'main.date';

import { Core } from 'im.v2.application.core';
import { ChatType, Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';

import type { ImModelUser, ImModelChat, ImModelRecentItem, ImModelMessage } from 'im.v2.model';

// @vue/component
export const MessageText = {
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
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.recentItem.dialogId, true);
		},
		message(): ImModelMessage
		{
			return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
		},
		needsBirthdayPlaceholder(): boolean
		{
			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
		},
		needsVacationPlaceholder(): boolean
		{
			return this.$store.getters['recent/needsVacationPlaceholder'](this.recentItem.dialogId);
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
		hiddenMessageText(): string
		{
			if (this.isUser)
			{
				return this.$store.getters['users/getPosition'](this.recentItem.dialogId);
			}

			return this.loc('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
		},
		isLastMessageAuthor(): boolean
		{
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
			if (this.message.isDeleted)
			{
				return this.loc('IM_LIST_RECENT_DELETED_MESSAGE');
			}

			const formattedText = Parser.purifyRecent(this.recentItem);
			if (!formattedText)
			{
				return this.isUser ? this.$store.getters['users/getPosition'](this.recentItem.dialogId) : this.hiddenMessageText;
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
			const text = Text.encode(this.formattedDraftText);

			return `
				<span class="bx-im-list-recent-item__message_draft-prefix">${prefix}</span>
				<span class="bx-im-list-recent-item__message_text_content">${text}</span>
			`;
		},
		formattedDraftText(): string
		{
			return Parser.purify({ text: this.recentItem.draft.text, showIconIfEmptyText: false });
		},
		formattedVacationEndDate(): string
		{
			return DateTimeFormat.format('d.m.Y', this.user.absent);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isChat(): boolean
		{
			return !this.isUser;
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
		<div class="bx-im-list-recent-item__message_container">
			<span class="bx-im-list-recent-item__message_text">
				<span v-if="recentItem.draft.text" v-html="preparedDraftContent"></span>
				<div v-else-if="recentItem.invitation.isActive" class="bx-im-list-recent-item__balloon_container --invitation">
					<div class="bx-im-list-recent-item__balloon">{{ loc('IM_LIST_RECENT_INVITATION_NOT_ACCEPTED') }}</div>
				</div>
				<div v-else-if="needsBirthdayPlaceholder" class="bx-im-list-recent-item__balloon_container --birthday">
					<div class="bx-im-list-recent-item__balloon">{{ loc('IM_LIST_RECENT_BIRTHDAY') }}</div>
				</div>
				<div v-else-if="needsVacationPlaceholder" class="bx-im-list-recent-item__balloon_container --vacation">
					<div class="bx-im-list-recent-item__balloon">
						{{ loc('IM_LIST_RECENT_VACATION', {'#VACATION_END_DATE#': formattedVacationEndDate}) }}
					</div>
				</div>
				<template v-else-if="!showLastMessage">
					{{ hiddenMessageText }}
				</template>
				<template v-else>
					<span v-if="isLastMessageAuthor" class="bx-im-list-recent-item__message_author-icon --self"></span>
					<template v-else-if="isChat && message.authorId">
						<span v-if="lastMessageAuthorAvatar" :style="lastMessageAuthorAvatarStyle" class="bx-im-list-recent-item__message_author-icon --user"></span>
						<span v-else class="bx-im-list-recent-item__message_author-icon --user --default"></span>
					</template>
					<span class="bx-im-list-recent-item__message_text_content">{{ formattedMessageText }}</span>
				</template>
			</span>
		</div>
	`
};