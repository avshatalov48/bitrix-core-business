import { Type } from 'main.core';

import { DefaultMessageContent, AuthorTitle, ReactionSelector } from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';

import { Reply } from './components/reply';

import './css/default.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const DefaultMessage = {
	name: 'DefaultMessage',
	components: {
		AuthorTitle,
		BaseMessage,
		DefaultMessageContent,
		ReactionSelector,
		Reply,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		withTitle: {
			type: Boolean,
			default: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
		isReply(): boolean
		{
			return this.message.replyId !== 0;
		},
	},
	template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-default__container">
				<AuthorTitle v-if="withTitle" :item="item" />
				<Reply v-if="isReply" :dialogId="dialogId" :replyId="message.replyId" />
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
				<ReactionSelector :messageId="message.id" />
			</div>
		</BaseMessage>
	`,
};
