import { Messenger } from 'im.public';
import { ImModelUser } from 'im.v2.model';

import { ChatAvatar } from '../avatar/chat-avatar/chat-avatar';
import { AvatarSize } from '../avatar/base-avatar/avatar';
import { ChatTitle } from '../chat-title/chat-title';

import './user-list-content.css';

// @vue/component
export const UserItem = {
	name: 'UserItem',
	components: { ChatAvatar, ChatTitle },
	props: {
		userId: {
			type: Number,
			required: true,
		},
		contextDialogId: {
			type: String,
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
			void Messenger.openChat(this.userDialogId);
		},
	},
	template: `
		<div class="bx-im-user-list-content__user-container" @click="onUserClick">
			<div class="bx-im-user-list-content__avatar-container">
				<ChatAvatar
					:avatarDialogId="userDialogId"
					:contextDialogId="contextDialogId"
					:size="AvatarSize.XS"
				/>
			</div>
			<ChatTitle 
				:dialogId="userDialogId" 
				:showItsYou="false" 
				class="bx-im-user-list-content__chat-title-container" 
			/>
		</div>
	`,
};
