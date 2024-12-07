import { ChatType } from 'im.v2.const';
import { ChannelManager } from 'im.v2.lib.channel';

import { CommentsPanel } from './components/comments-panel';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const MessageFooter = {
	name: 'MessageFooter',
	components: { CommentsPanel },
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
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId);
		},
		message(): ImModelMessage
		{
			return this.item;
		},
		isChannelPost(): boolean
		{
			return ChannelManager.isChannel(this.dialogId);
		},
		isSystemMessage(): boolean
		{
			return this.message.authorId === 0;
		},
		showCommentsPanel(): boolean
		{
			return this.isChannelPost && !this.isSystemMessage;
		},
	},
	template: `
		<CommentsPanel v-if="showCommentsPanel" :dialogId="dialogId" :item="item" />
	`,
};
