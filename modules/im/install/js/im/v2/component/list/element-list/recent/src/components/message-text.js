import {DateTimeFormat} from 'main.date';

import {Core} from 'im.v2.application.core';
import {DialogType, Settings} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';
import {Parser} from 'im.v2.lib.parser';

import type {ImModelUser, ImModelDialog} from 'im.v2.model';

// @vue/component
export const MessageText = {
	props: {
		item: {
			type: Object,
			required: true
		}
	},
	data() {
		return {};
	},
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.item.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.item.dialogId, true);
		},
		needsBirthdayPlaceholder(): boolean
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.item.dialogId);
		},
		needsVacationPlaceholder(): boolean
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.$store.getters['recent/needsVacationPlaceholder'](this.item.dialogId);
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
		hiddenMessageText(): string
		{
			if (this.isUser)
			{
				return this.$store.getters['users/getPosition'](this.item.dialogId);
			}

			return this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
		},
		isLastMessageAuthor(): boolean
		{
			if (!this.item.message)
			{
				return false;
			}

			return this.item.message.senderId === Core.getUserId();
		},
		lastMessageAuthorAvatar(): string
		{
			const authorDialog = this.$store.getters['dialogues/get'](this.item.message.senderId);

			if (!authorDialog)
			{
				return '';
			}

			return authorDialog.avatar;
		},
		lastMessageAuthorAvatarStyle(): Object
		{
			return {backgroundImage: `url('${this.lastMessageAuthorAvatar}')`};
		},
		messageText(): string
		{
			const formattedText = Parser.purifyRecent(this.item);
			if (!formattedText)
			{
				return this.isUser ? this.$store.getters['users/getPosition'](this.item.dialogId) : this.hiddenMessageText;
			}

			return formattedText;
		},
		formattedMessageText(): string
		{
			const SPLIT_INDEX = 27;

			return Utils.text.insertUnseenWhitespace(this.messageText, SPLIT_INDEX);
		},
		formattedDraftText(): string
		{
			return Parser.purify({text: this.item.draft.text, showIconIfEmptyText: false});
		},
		formattedVacationEndDate(): string
		{
			return DateTimeFormat.format('d.m.Y', this.user.absent);
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
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
		}
	},
	template: `
		<div class="bx-im-list-recent-item__message_container">
			<span class="bx-im-list-recent-item__message_text">
				<template v-if="item.draft.text && dialog.counter === 0">
					<span class="bx-im-list-recent-item__message_draft-prefix">{{ loc('IM_LIST_RECENT_MESSAGE_DRAFT') }}</span>
					<span class="bx-im-list-recent-item__message_text_content">{{ formattedDraftText }}</span>
				</template>
				<div v-else-if="item.invitation.isActive" class="bx-im-list-recent-item__balloon_container --invitation">
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
					<template v-else-if="isChat && item.message.senderId">
						<span v-if="lastMessageAuthorAvatar" :style="lastMessageAuthorAvatarStyle" class="bx-im-list-recent-item__message_author-icon --user"></span>
						<span v-else class="bx-im-list-recent-item__message_author-icon --user --default"></span>
					</template>
					<span class="bx-im-list-recent-item__message_text_content">{{ formattedMessageText }}</span>
				</template>
			</span>
		</div>
	`
};