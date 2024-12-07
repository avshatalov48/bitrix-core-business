import { ChatType, ChatActionType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';

import { PinnedMessage } from './pinned-message';

import '../../css/pinned-messages.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const PinnedMessages = {
	name: 'PinnedMessages',
	components: { PinnedMessage },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		messages: {
			type: Array,
			required: true,
		},
	},
	emits: ['messageClick', 'messageUnpin'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		firstMessage(): ImModelMessage
		{
			return this.messagesToShow[0];
		},
		messagesToShow(): ImModelMessage[]
		{
			return this.messages.slice(-1);
		},
		canUnpin(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.pinMessage, this.dialogId);
		},
		showUnpin(): boolean
		{
			return !this.isCommentChat && this.canUnpin;
		},
		isCommentChat(): boolean
		{
			return this.dialog.type === ChatType.comment;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div @click="$emit('messageClick', firstMessage.id)" class="bx-im-dialog-chat__pinned_container">
			<div class="bx-im-dialog-chat__pinned_title">{{ loc('IM_DIALOG_CHAT_PINNED_TITLE') }}</div>
			<PinnedMessage
				v-for="message in messagesToShow"
				:message="message"
				:key="message.id"
			/>
			<div v-if="showUnpin" @click.stop="$emit('messageUnpin', firstMessage.id)" class="bx-im-dialog-chat__pinned_unpin"></div>
		</div>
	`,
};
