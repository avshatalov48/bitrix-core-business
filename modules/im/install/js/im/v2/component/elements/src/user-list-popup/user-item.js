import {Messenger} from 'im.public';
import {ImModelUser} from 'im.v2.model';
import {Layout} from 'im.v2.const';

import {Avatar, AvatarSize} from '../avatar/avatar';
import {ChatTitle} from '../chat-title/chat-title';

import './user-list-content.css';

// @vue/component
export const UserItem = {
	name: 'UserItem',
	components: {Avatar, ChatTitle},
	props: {
		userId: {
			type: Number,
			required: true
		}
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
		userStatusClasses(): string[]
		{
			if (this.user.bot)
			{
				return [];
			}

			const status = this.$store.getters['users/getStatus'](this.userId);
			if (status === '')
			{
				return [];
			}

			return [
				'bx-im-user-list-content__avatar-status',
				`--${status}`,
			];
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
			<div class="bx-im-user-list-content__avatar-container" :class="userStatusClasses">
				<Avatar :size="AvatarSize.XS" :dialogId="userDialogId" />
			</div>
			<ChatTitle class="bx-im-user-list-content__chat-title-container" :dialogId="userDialogId" />
		</div>
	`
};