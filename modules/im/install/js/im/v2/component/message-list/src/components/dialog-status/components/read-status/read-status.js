import { ChatType } from 'im.v2.const';

import { AdditionalUsers } from './components/additional-users';
import { UserRead } from './components/user-read';
import { ChatSingleRead } from './components/chat-single-read';
import { ChatMultipleRead } from './components/chat-multiple-read';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ReadStatus = {
	name: 'ReadStatus',
	components: { UserRead, ChatSingleRead, ChatMultipleRead, AdditionalUsers },
	props:
	{
		dialogId: {
			required: true,
			type: String,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		lastMessageViews(): ImModelChat['lastMessageViews']
		{
			return this.dialog.lastMessageViews;
		},
		countOfViewers(): number
		{
			return this.lastMessageViews.countOfViewers;
		},
	},
	template: `
		<div class="bx-im-dialog-chat-status__content">
			<div class="bx-im-dialog-chat-status__icon --read"></div>
			<UserRead v-if="isUser" :dialogId="dialogId" />
			<ChatSingleRead v-else-if="countOfViewers === 1" :dialogId="dialogId" />
			<ChatMultipleRead v-else :dialogId="dialogId" />
		</div>
	`,
};
