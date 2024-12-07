import { Type } from 'main.core';
import { Reactions } from 'ui.vue3.components.reactions';

import { Parser } from 'im.v2.lib.parser';

import { MessageAttach, MessageStatus, ReactionList } from '../registry';

import './default-message-content.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const DefaultMessageContent = {
	name: 'DefaultMessageContent',
	components: {
		Reactions,
		MessageStatus,
		MessageAttach,
		ReactionList,
	},
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		withMessageStatus: {
			type: Boolean,
			default: true,
		},
		withText: {
			type: Boolean,
			default: true,
		},
		withAttach: {
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
		formattedText(): string
		{
			return Parser.decodeMessage(this.item);
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
	},
	template: `
		<div class="bx-im-message-default-content__container bx-im-message-default-content__scope" :class="{'--no-text': !withText}">
			<div v-if="withText" class="bx-im-message-default-content__text" v-html="formattedText"></div>
			<div v-if="withAttach && message.attach.length > 0" class="bx-im-message-default-content__attach">
				<MessageAttach :item="message" :dialogId="dialogId" />
			</div>
			<div class="bx-im-message-default-content__bottom-panel">
				<ReactionList 
					v-if="canSetReactions" 
					:messageId="message.id" 
					:contextDialogId="dialogId"
					class="bx-im-message-default-content__reaction-list" 
				/>
				<div v-if="withMessageStatus" class="bx-im-message-default-content__status-container">
					<MessageStatus :item="message" />
				</div>
			</div>
		</div>
	`,
};
