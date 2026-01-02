import { BaseMessage } from 'im.v2.component.message.base';
import { DefaultMessageContent, MessageHeader, MessageKeyboard } from 'im.v2.component.message.elements';

import './css/error.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const ErrorMessage = {
	name: 'ErrorMessage',
	components: {
		BaseMessage,
		DefaultMessageContent,
		MessageHeader,
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
	computed: {
		message(): ImModelMessage
		{
			return this.item;
		},
		hasKeyboard(): boolean
		{
			return this.message.keyboard.length > 0;
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withError="true"
			:withReactions="false"
			:withContextMenu="false"
			:withRetryButton="false"
		>
			<div class="bx-im-message-error__container">
				<MessageHeader :withTitle="withTitle" :item="item" />
				<DefaultMessageContent :dialogId="dialogId" :item="item" />
			</div>
			<template #after-message v-if="hasKeyboard">
				<MessageKeyboard :item="item" :dialogId="dialogId" />
			</template>
		</BaseMessage>
	`,
};
