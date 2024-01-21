import { Messenger } from 'im.public';
import { ImModelUser } from 'im.v2.model';

import { Avatar, AvatarSize } from '../avatar/avatar';
import { ChatTitle } from '../chat-title/chat-title';

import './user-list-content.css';

// @vue/component
export const UserItem = {
	name: 'UserItem',
	components: { Avatar, ChatTitle },
	props: {
		userId: {
			type: Number,
			required: true,
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.userId, true);
		},
		userDialogId(): string
		{
			return this.userId.toString();
		},
	},
	methods:
	{
		onUserClick()
		{
			Messenger.openChat(this.userDialogId);
		},
	},
	template: `
		<div class="bx-im-user-list-content__user-container" @click="onUserClick">
			<div class="bx-im-user-list-content__avatar-container">
				<Avatar :size="AvatarSize.XS" :dialogId="userDialogId" />
			</div>
			<ChatTitle class="bx-im-user-list-content__chat-title-container" :dialogId="userDialogId" :showItsYou="false" />
		</div>
	`,
};
