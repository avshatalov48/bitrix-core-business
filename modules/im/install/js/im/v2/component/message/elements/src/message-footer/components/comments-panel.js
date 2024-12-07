import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType, ChatActionType } from 'im.v2.const';
import { ChatAvatar, AvatarSize } from 'im.v2.component.elements';
import { PermissionManager } from 'im.v2.lib.permission';
import { FadeAnimation } from 'im.v2.component.animation';
import { CommentsService } from 'im.v2.provider.service';

import './css/comments-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelCommentInfo, ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const CommentsPanel = {
	name: 'CommentsPanel',
	components: { ChatAvatar, FadeAnimation },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
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
		AvatarSize: () => AvatarSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId);
		},
		message(): ImModelMessage
		{
			return this.item;
		},
		commentInfo(): ImModelCommentInfo
		{
			return this.$store.getters['messages/comments/getByMessageId'](this.message.id);
		},
		commentsChatId(): number
		{
			return this.commentInfo.chatId;
		},
		commentsCount(): number
		{
			// remove first system message from count
			if (this.commentInfo.messageCount > 0)
			{
				return this.commentInfo.messageCount - 1;
			}

			return this.commentInfo.messageCount;
		},
		commentsCountText(): string
		{
			return Loc.getMessagePlural('IM_MESSAGE_COMMENTS_PANEL_COMMENT_COUNT', this.commentsCount, {
				'#COUNT#': this.commentsCount,
			});
		},
		noComments(): boolean
		{
			return this.commentsCount === 0;
		},
		lastUsers(): ImModelUser[]
		{
			return [...this.commentInfo.lastUserIds].map((userId) => {
				return this.$store.getters['users/get'](userId);
			}).reverse();
		},
		unreadCount(): string
		{
			const counter = this.$store.getters['counters/getSpecificCommentsCounter']({
				channelId: this.dialog.chatId,
				commentChatId: this.commentsChatId,
			});

			if (!counter)
			{
				return '';
			}

			return `+${counter}`;
		},
		isSubscribed(): boolean
		{
			return this.$store.getters['messages/comments/isUserSubscribed'](this.message.id);
		},
		showSubscribeIcon(): boolean
		{
			const permissionManager = PermissionManager.getInstance();

			return permissionManager.canPerformAction(ChatActionType.subscribeToComments, this.dialogId);
		},
		subscribeIconTitle(): string
		{
			if (this.isSubscribed)
			{
				return this.loc('IM_MESSAGE_COMMENTS_PANEL_ICON_UNSUBSCRIBE');
			}

			return this.loc('IM_MESSAGE_COMMENTS_PANEL_ICON_SUBSCRIBE');
		},
	},
	methods:
	{
		onCommentsClick()
		{
			const permissionManager = PermissionManager.getInstance();
			if (!permissionManager.canPerformAction(ChatActionType.openComments, this.dialogId))
			{
				return;
			}

			EventEmitter.emit(EventType.dialog.openComments, { messageId: this.message.id });
		},
		onSubscribeIconClick()
		{
			if (this.isSubscribed)
			{
				CommentsService.unsubscribe(this.message.id);

				return;
			}

			CommentsService.subscribe(this.message.id);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-comments-panel__container" @click="onCommentsClick">
			<div class="bx-im-message-comments-panel__left">
				<div v-if="noComments" class="bx-im-message-comments-panel__empty_container">
					<div class="bx-im-message-comments-panel__empty_icon"></div>
					<div class="bx-im-message-comments-panel__text">{{ loc('IM_MESSAGE_COMMENTS_PANEL_EMPTY_TEXT') }}</div>
				</div>
				<div v-else class="bx-im-message-comments-panel__meta_container">
					<div class="bx-im-message-comments-panel__user_container">
						<TransitionGroup name="bx-im-message-comments-panel__user_animation">
							<div v-for="(user, index) in lastUsers" :key="user.id" class="bx-im-message-comments-panel__user_avatar" :class="'--image-' + (index + 1)">
								<ChatAvatar
									:avatarDialogId="user.id"
									:contextDialogId="dialogId"
									:size="AvatarSize.S"
									:withTooltip="false"
								/>
							</div>
						</TransitionGroup>
					</div>
					<div class="bx-im-message-comments-panel__text">{{ commentsCountText }}</div>
					<FadeAnimation :duration="200">
						<div v-if="unreadCount" class="bx-im-message-comments-panel__unread-counter">{{ unreadCount }}</div>
					</FadeAnimation>
				</div>
			</div>
			<div v-if="showSubscribeIcon" :title="subscribeIconTitle" class="bx-im-message-comments-panel__right">
				<div
					@click.stop="onSubscribeIconClick"
					class="bx-im-message-comments-panel__subscribe-icon"
					:class="{'--active': isSubscribed}"
				></div>
			</div>
		</div>
	`,
};
