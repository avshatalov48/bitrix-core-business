import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelRecentItem, ImModelUser } from 'im.v2.model';

// @vue/component
export const ItemCounter = {
	name: 'ItemCounter',
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		isChatMuted: {
			type: Boolean,
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
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isSelfChat(): boolean
		{
			return this.isUser && this.user.id === Core.getUserId();
		},
		invitation(): { isActive: boolean, originator: number, canResend: boolean }
		{
			return this.recentItem.invitation;
		},
		totalCounter(): number
		{
			return this.dialog.counter + this.channelCommentsCounter;
		},
		channelCommentsCounter(): number
		{
			return this.$store.getters['counters/getChannelCommentsCounter'](this.dialog.chatId);
		},
		formattedCounter(): string
		{
			return this.formatCounter(this.totalCounter);
		},
		showCounterContainer(): boolean
		{
			return !this.needsBirthdayPlaceholder && !this.invitation.isActive;
		},
		showPinnedIcon(): boolean
		{
			const noCounters = this.totalCounter === 0;

			return this.recentItem.pinned && noCounters && !this.recentItem.unread;
		},
		showUnreadWithoutCounter(): boolean
		{
			return this.recentItem.unread && this.totalCounter === 0;
		},
		showUnreadWithCounter(): boolean
		{
			return this.recentItem.unread && this.totalCounter > 0;
		},
		showCounter(): boolean
		{
			return !this.recentItem.unread && this.totalCounter > 0 && !this.isSelfChat;
		},
		needsBirthdayPlaceholder(): boolean
		{
			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
		},
		containerClasses(): { [className: string]: boolean }
		{
			const commentsOnly = this.dialog.counter === 0 && this.channelCommentsCounter > 0;
			const withComments = this.dialog.counter > 0 && this.channelCommentsCounter > 0;

			return {
				'--muted': this.isChatMuted,
				'--extended': this.totalCounter > 99,
				'--comments-only': commentsOnly,
				'--with-comments': withComments,
			};
		},
	},
	methods:
	{
		formatCounter(counter: number): string
		{
			return counter > 99 ? '99+' : counter.toString();
		},
	},
	template: `
		<div v-if="showCounterContainer" :class="containerClasses" class="bx-im-list-recent-item__counter_wrap">
			<div class="bx-im-list-recent-item__counter_container">
				<div v-if="showPinnedIcon" class="bx-im-list-recent-item__pinned-icon"></div>
				<div v-else-if="showUnreadWithoutCounter" class="bx-im-list-recent-item__counter_number --no-counter"></div>
				<div v-else-if="showUnreadWithCounter" class="bx-im-list-recent-item__counter_number --with-unread">
					{{ formattedCounter }}
				</div>
				<div v-else-if="showCounter" class="bx-im-list-recent-item__counter_number">
					{{ formattedCounter }}
				</div>
			</div>
		</div>
	`,
};
