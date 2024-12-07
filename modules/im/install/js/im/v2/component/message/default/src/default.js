import { DefaultMessageContent, MessageHeader, ReactionSelector, MessageKeyboard, MessageFooter } from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';

import { Reply } from './components/reply';

import './css/default.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const DefaultMessage = {
	name: 'DefaultMessage',
	components: {
		MessageHeader,
		MessageFooter,
		BaseMessage,
		DefaultMessageContent,
		ReactionSelector,
		Reply,
		MessageKeyboard,
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
		isReply(): boolean
		{
			return this.message.replyId !== 0;
		},
		isForward(): boolean
		{
			return this.$store.getters['messages/isForward'](this.message.id);
		},
		hasKeyboard(): boolean
		{
			return this.message.keyboard.length > 0;
		},
	},
	template: `
		<BaseMessage :item="item" :dialogId="dialogId" :afterMessageWidthLimit="false">
			<template #before-message v-if="$slots['before-message']">
				<slot name="before-message"></slot>
			</template>
			<div class="bx-im-message-default__container">
				<MessageHeader :withTitle="withTitle" :item="item" />
				<Reply v-if="isReply" :dialogId="dialogId" :replyId="message.replyId" :isForward="isForward" />
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
			<template #after-message v-if="hasKeyboard">
				<MessageKeyboard :item="item" :dialogId="dialogId" />
			</template>
		</BaseMessage>
	`,
};
