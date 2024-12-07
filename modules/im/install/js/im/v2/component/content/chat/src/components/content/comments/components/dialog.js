import { Logger } from 'im.v2.lib.logger';
import { ImModelMessage } from 'im.v2.model';

import { ChatDialog, PinnedMessages } from 'im.v2.component.dialog.chat';

import { CommentsMessageList } from './message-list';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CommentsDialog = {
	name: 'CommentsDialog',
	components: { ChatDialog, CommentsMessageList, PinnedMessages },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		postMessageId(): number
		{
			return this.$store.getters['messages/comments/getMessageIdByChatId'](this.dialog.chatId);
		},
		postMessage(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.postMessageId);
		},
	},
	methods:
	{
		async goToPostMessageContext()
		{
			const dialog = this.$refs.dialog;

			const postMessageIsShown = this.dialogInited && !this.dialog.hasPrevPage;
			if (postMessageIsShown)
			{
				await dialog.getScrollManager().animatedScrollToMessage(this.postMessageId);
				dialog.highlightMessage(this.postMessageId);

				return;
			}

			dialog.showLoadingBar();
			await dialog.getMessageService().loadFirstPage()
				.catch((error) => {
					Logger.error('goToMessageContext error', error);
				});
			await this.$nextTick();
			dialog.hideLoadingBar();
			dialog.getScrollManager().scrollToMessage(this.postMessageId);
			await this.$nextTick();
			dialog.highlightMessage(this.postMessageId);
		},
		onPinnedPostMessageClick()
		{
			this.goToPostMessageContext();
		},
	},
	template: `
		<ChatDialog ref="dialog" :dialogId="dialogId" :saveScrollOnExit="false" :resetOnExit="true">
			<template v-if="dialogInited" #pinned-panel>
				<PinnedMessages
					:dialogId="dialogId"
					:messages="[postMessage]"
					@messageClick="onPinnedPostMessageClick"
				/>
			</template>
			<template #message-list>
				<CommentsMessageList :dialogId="dialogId" />
			</template>
		</ChatDialog>
	`,
};
