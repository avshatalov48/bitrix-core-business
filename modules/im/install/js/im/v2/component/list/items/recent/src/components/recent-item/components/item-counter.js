import { Core } from 'im.v2.application.core';
import { ChatType, AnchorType } from 'im.v2.const';

import type { ImModelChat, ImModelRecentItem, ImModelUser } from 'im.v2.model';

// @vue/component
export const ItemCounters = {
	name: 'ItemCounters',
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
			return !this.invitation.isActive;
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
		showMention(): boolean
		{
			return this.$store.getters['messages/anchors/isChatHasAnchorsWithType'](this.dialog.chatId, AnchorType.mention) && !this.isSelfChat;
		},
		showCounter(): boolean
		{
			const isSingleMessageWithMention = this.showMention && this.totalCounter === 1;

			return !isSingleMessageWithMention && !this.recentItem.unread && this.totalCounter > 0 && !this.isSelfChat;
		},
		containerClasses(): { [className: string]: boolean }
		{
			const commentsOnly = this.dialog.counter === 0 && this.channelCommentsCounter > 0;
			const withComments = this.dialog.counter > 0 && this.channelCommentsCounter > 0;
			const withMentionAndCounter = this.dialog.counter > 0 && this.showMention;

			return {
				'--muted': this.isChatMuted,
				'--comments-only': commentsOnly,
				'--with-comments': withComments,
				'--with-mention-and-counter': withMentionAndCounter,
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
		<div v-if="showCounterContainer" :class="containerClasses" class="bx-im-list-recent-item__counters_wrap">
			<div class="bx-im-list-recent-item__counters_container">
				<div v-if="showPinnedIcon" class="bx-im-list-recent-item__pinned-icon"></div>
				<div v-else class="bx-im-list-recent-item__counters">
					<div v-if="showMention" class="bx-im-list-recent-item__mention">
						<div class="bx-im-list-recent-item__mention-icon"></div>
					</div>
					<div v-if="showUnreadWithoutCounter" class="bx-im-list-recent-item__counter_number --no-counter"></div>
					<div v-else-if="showUnreadWithCounter" class="bx-im-list-recent-item__counter_number --with-unread">
						{{ formattedCounter }}
					</div>
					<div v-else-if="showCounter" class="bx-im-list-recent-item__counter_number">
						{{ formattedCounter }}
					</div>
				</div>
			</div>
		</div>
	`,
};
