import { Analytics } from 'im.v2.lib.analytics';
import { Logger } from 'im.v2.lib.logger';
import { ChatService } from 'im.v2.provider.service.chat';

import { CommentsContent } from '../../content/comments/comments';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelCommentInfo } from 'im.v2.model';

// @vue/component
export const CommentsOpener = {
	name: 'CommentsOpener',
	components: { CommentsContent },
	props:
	{
		postId: {
			type: Number,
			required: true,
		},
		channelId: {
			type: String,
			required: true,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		dialog(): ?ImModelChat
		{
			return this.$store.getters['chats/getByChatId'](this.commentsChatId);
		},
		commentInfo(): ImModelCommentInfo
		{
			return this.$store.getters['messages/comments/getByMessageId'](this.postId);
		},
		commentsChatId(): number
		{
			return this.commentInfo.chatId;
		},
		commentsDialogId(): string
		{
			if (!this.dialog)
			{
				return '';
			}

			return this.dialog.dialogId;
		},
	},
	created()
	{
		this.onCreated();
	},
	methods:
	{
		async onCreated()
		{
			await this.loadChat();
			Analytics.getInstance().onOpenChat(this.dialog);
		},
		async loadChat(): Promise
		{
			Logger.warn(`CommentsContent: loading comments for post ${this.postId}`);

			await this.getChatService().loadComments(this.postId)
				.catch(() => {
					this.$emit('close');
				});

			Logger.warn(`CommentsContent: comments for post ${this.postId} are loaded`);
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
	},
	template: `
		<div class="bx-im-content-comments__container">
			<CommentsContent :dialogId="commentsDialogId" :channelId="channelId" />
		</div>
	`,
};
