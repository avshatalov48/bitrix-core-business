import {Core} from 'im.v2.application.core';
import {DialogType, OwnMessageStatus, MessageStatus as MessageStatusType} from 'im.v2.const';

import type {ImModelDialog, ImModelRecentItem, ImModelUser, ImModelMessage} from 'im.v2.model';

const StatusIcon = {
	none: '',
	like: 'like',
	sending: 'sending',
	sent: 'sent',
	viewed: 'viewed'
};

// @vue/component
export const MessageStatus = {
	props:
	{
		item: {
			type: Object,
			required: true
		}
	},
	data()
	{
		return {};
	},
	computed:
	{
		recentItem(): ImModelRecentItem
		{
			return this.item;
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.recentItem.dialogId, true);
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.recentItem.dialogId, true);
		},
		messageStatus(): $Values<typeof OwnMessageStatus>
		{
			if (this.recentItem.message.sending)
			{
				return OwnMessageStatus.sending;
			}

			if (this.recentItem.message.status === MessageStatusType.delivered)
			{
				return OwnMessageStatus.viewed;
			}

			return OwnMessageStatus.sent;
		},
		statusIcon(): $Values<typeof StatusIcon>
		{
			if (!this.isLastMessageAuthor || this.isBot || this.needsBirthdayPlaceholder)
			{
				return StatusIcon.none;
			}

			if (this.isSelfChat)
			{
				return StatusIcon.none;
			}

			if (this.recentItem.liked)
			{
				return StatusIcon.like;
			}

			return this.messageStatus;
		},
		isLastMessageAuthor(): boolean
		{
			if (!this.recentItem.message)
			{
				return false;
			}

			return this.recentItem.message.senderId === Core.getUserId();
		},
		isSelfChat(): boolean
		{
			return this.isUser && this.user.id === Core.getUserId();
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
		},
		isBot(): boolean
		{
			if (this.isUser)
			{
				return this.user.bot;
			}

			return false;
		},
		needsBirthdayPlaceholder(): boolean
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
		}
	},
	template: `
		<div class="bx-im-list-recent-item__status-icon" :class="'--' + statusIcon"></div>
	`
};