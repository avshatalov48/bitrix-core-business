import { EventEmitter } from 'main.core.events';
import { Text, Loc } from 'main.core';

import { EventType, ChatType } from 'im.v2.const';
import { Parser } from 'im.v2.lib.parser';
import { CopilotManager } from 'im.v2.lib.copilot';
import { ChannelManager } from 'im.v2.lib.channel';

import { AuthorTitle } from '../author-title/author-title';

import './message-header.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const MessageHeader = {
	name: 'MessageHeader',
	components: { AuthorTitle },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		withTitle: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		forwardAuthorId(): number
		{
			return this.message.forward.userId;
		},
		forwardContextId(): string
		{
			return this.message.forward.id;
		},
		isForwarded(): boolean
		{
			return this.$store.getters['messages/isForward'](this.message.id);
		},
		isChannelForward(): boolean
		{
			return ChannelManager.channelTypes.has(this.message.forward.chatType);
		},
		forwardAuthorName(): string
		{
			const copilotManager = new CopilotManager();
			if (copilotManager.isCopilotBot(this.forwardAuthorId))
			{
				const forwardMessageId = this.forwardContextId.split('/')[1];

				return copilotManager.getNameWithRole({
					dialogId: this.forwardAuthorId,
					messageId: forwardMessageId,
				});
			}

			return this.$store.getters['users/get'](this.forwardAuthorId, true).name;
		},
		forwardChatName(): string
		{
			return this.message.forward.chatTitle ?? this.loc('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_CLOSED_CHANNEL');
		},
		isSystemMessage(): boolean
		{
			return this.message.forward.userId === 0;
		},
		forwardAuthorTitle(): string
		{
			return Loc.getMessage('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_FROM_CHAT', {
				'[user_name]': '<span class="bx-im-message-header__author-name">',
				'#USER_NAME#': Text.encode(this.forwardAuthorName),
				'[/user_name]': '</span>',
			});
		},
		forwardChannelTitle(): string
		{
			return Loc.getMessage('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_FROM_CHANNEL', {
				'[user_name]': '<span class="bx-im-message-header__author-name">',
				'#USER_NAME#': Text.encode(this.forwardAuthorName),
				'[/user_name]': '</span>',
				'[channel_name]': '<span class="bx-im-message-header__author-name">',
				'#CHANNEL_NAME#': Text.encode(this.forwardChatName),
				'[/channel_name]': '</span>',
			});
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		onForwardClick()
		{
			const contextCode = Parser.getContextCodeFromForwardId(this.forwardContextId);
			if (contextCode.length === 0)
			{
				return;
			}

			const [dialogId, messageId] = contextCode.split('/');

			EventEmitter.emit(EventType.dialog.goToMessageContext, {
				messageId: Number.parseInt(messageId, 10),
				dialogId: dialogId.toString(),
			});
		},
	},
	template: `
		<div v-if="isForwarded" class="bx-im-message-header__container" @click="onForwardClick">
			<span v-if="isSystemMessage">{{ loc('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_FROM_SYSTEM')}}</span>
			<span v-else-if="isChannelForward" v-html="forwardChannelTitle"></span>
			<span v-else v-html="forwardAuthorTitle"></span>
		</div>
		<AuthorTitle v-else-if="withTitle" :item="item" />
	`,
};
