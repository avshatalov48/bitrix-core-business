import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { EventType, ChatType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import { MessageAuthorTitle } from 'im.v2.component.elements';

import './author-title.css';

import type { ImModelMessage, ImModelUser, ImModelChat } from 'im.v2.model';

// @vue/component
export const AuthorTitle = {
	name: 'AuthorTitle',
	components: { MessageAuthorTitle },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/getByChatId'](this.message.chatId);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.message.authorId, true);
		},
		isSystemMessage(): boolean
		{
			return this.message.authorId === 0;
		},
		isSelfMessage(): boolean
		{
			return this.message.authorId === Core.getUserId();
		},
		isUserChat(): boolean
		{
			return this.dialog.type === ChatType.user && !this.isBotWithFakeAuthorNames;
		},
		isBotWithFakeAuthorNames(): boolean
		{
			return this.isSupportBot || this.isNetworkBot;
		},
		isNetworkBot(): boolean
		{
			return this.$store.getters['users/bots/isNetwork'](this.dialog.dialogId);
		},
		isSupportBot(): boolean
		{
			return this.$store.getters['users/bots/isSupport'](this.dialog.dialogId);
		},
		showTitle(): boolean
		{
			return !this.isSystemMessage && !this.isSelfMessage && !this.isUserChat;
		},
		authorDialogId(): string
		{
			if (this.message.authorId)
			{
				return this.message.authorId.toString();
			}

			return this.dialogId;
		},
		isCopilot(): boolean
		{
			const authorId = Number.parseInt(this.authorDialogId, 10);

			return this.$store.getters['users/bots/isCopilot'](authorId);
		},
	},
	methods:
	{
		onAuthorNameClick()
		{
			const authorId = Number.parseInt(this.authorDialogId, 10);
			if (
				!authorId
				|| authorId === Core.getUserId()
				|| this.isCopilot
			)
			{
				return;
			}

			EventEmitter.emit(EventType.textarea.insertMention, {
				mentionText: this.user.name,
				mentionReplacement: Utils.text.getMentionBbCode(this.user.id, this.user.name),
				dialogId: this.dialog.dialogId,
			});
		},
	},
	template: `
		<div 
			v-if="showTitle" 
			@click="onAuthorNameClick" 
			class="bx-im-message-author-title__container" 
			:class="{'--clickable': !isCopilot}"
		>
			<MessageAuthorTitle
				:dialogId="authorDialogId"
				:messageId="message.id"
				:showItsYou="false"
				:withColor="true"
				:withLeftIcon="!isCopilot"
			/>
		</div>
	`,
};
