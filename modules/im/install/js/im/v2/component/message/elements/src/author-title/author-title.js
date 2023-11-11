import { Core } from 'im.v2.application.core';
import { EventType, DialogType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { EventEmitter } from 'main.core.events';

import { ChatTitle } from 'im.v2.component.elements';

import './author-title.css';

import type { ImModelMessage, ImModelUser, ImModelDialog } from 'im.v2.model';

// @vue/component
export const AuthorTitle = {
	name: 'AuthorTitle',
	components: {
		ChatTitle,
	},
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
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/getByChatId'](this.message.chatId);
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
			return this.dialog.type === DialogType.user;
		},
		showTitle(): boolean
		{
			return !this.isSystemMessage && !this.isSelfMessage && !this.isUserChat;
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.message.authorId, true);
		},
		authorDialogId(): string
		{
			if (this.message.authorId)
			{
				return this.message.authorId.toString();
			}

			return this.dialogId;
		},
	},
	methods:
	{
		onAuthorNameClick()
		{
			const authorId = Number.parseInt(this.authorDialogId, 10);
			if (authorId === Core.getUserId())
			{
				return;
			}

			EventEmitter.emit(EventType.textarea.insertMention, {
				mentionText: this.user.name,
				mentionReplacement: Utils.text.getMentionBbCode(this.user.id, this.user.name),
			});
		},
	},
	template: `
		<div v-if="showTitle" @click="onAuthorNameClick" class="bx-im-message-author-title__container">
			<ChatTitle
				:dialogId="authorDialogId"
				:showItsYou="false"
				:withColor="true"
				:withLeftIcon="false"
			/>
		</div>
	`,
};
