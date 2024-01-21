import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Parser } from 'im.v2.lib.parser';

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
		forwardAuthorName(): string
		{
			return this.$store.getters['users/get'](this.forwardAuthorId, true).name;
		},
		isSystemMessage(): boolean
		{
			return this.message.forward.userId === 0;
		},
		forwardAuthorTitle(): { prefix: string, name: string }
		{
			const [prefix] = this.loc('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_FROM').split('#NAME#');

			return {
				prefix,
				name: this.forwardAuthorName,
			};
		},
	},
	methods:
	{
		loc(code: string): string
		{
			return this.$Bitrix.Loc.getMessage(code);
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
			<span v-else>
				{{ forwardAuthorTitle.prefix }}
				<span class="bx-im-message-header__author-name">{{ forwardAuthorTitle.name }}</span> 
			</span>
		</div>
		<AuthorTitle v-else-if="withTitle" :item="item" />
	`,
};
