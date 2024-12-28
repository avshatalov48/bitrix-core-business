import {
	MessageList,
	AuthorGroup,
	MessageComponents,
	CollectionManager,
} from 'im.v2.component.message-list';
import { MessageComponentManager } from 'im.v2.lib.message-component-manager';
import { MessageComponent } from 'im.v2.const';

import { CommentsDialogLoader } from './dialog-loader';
import { CommentsMessageMenu } from '../classes/message-menu';

import '../css/message-list.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const CommentsMessageList = {
	name: 'CommentsMessageList',
	components: { MessageList, CommentsDialogLoader, AuthorGroup, ...MessageComponents },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		CommentsMessageMenu: () => CommentsMessageMenu,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		showPostMessage(): boolean
		{
			return this.dialog.inited && !this.dialog.hasPrevPage;
		},
		postMessageId(): number
		{
			return this.$store.getters['messages/comments/getMessageIdByChatId'](this.dialog.chatId);
		},
		postMessage(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.postMessageId);
		},
		postAuthorGroup(): JsonObject | null
		{
			if (!this.dialog.inited)
			{
				return null;
			}

			const collectionManager = new CollectionManager(this.dialogId);

			return collectionManager.formatAuthorGroup(this.postMessage);
		},
	},
	methods:
	{
		onPostMessageMouseUp(message: ImModelMessage, event: MouseEvent)
		{
			this.$refs.messageList.onMessageMouseUp(message, event);
		},
		getMessageComponentName(message: ImModelMessage): $Values<typeof MessageComponent>
		{
			return (new MessageComponentManager(message)).getName();
		},
	},
	template: `
		<MessageList
			:dialogId="dialogId"
			:messageMenuClass="CommentsMessageMenu"
			ref="messageList"
		>
			<template #loader>
				<CommentsDialogLoader />
			</template>
			<template v-if="showPostMessage" #before-messages>
				<div class="bx-im-comments-message-list__channel-post">
					<AuthorGroup :item="postAuthorGroup" :contextDialogId="dialogId" :withAvatarMenu="false">
						<template #message>
							<component
								:is="getMessageComponentName(postMessage)"
								:item="postMessage"
								:dialogId="dialogId"
								:key="postMessage.id"
								@mouseup="onPostMessageMouseUp(postMessage, $event)"
							>
							</component>
						</template>
					</AuthorGroup>
				</div>
			</template>
		</MessageList>
	`,
};
