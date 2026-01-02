import { Messenger } from 'im.public';
import { ImModelUser } from 'im.v2.model';
import { AvatarSize, ChatAvatar } from 'im.v2.component.elements.avatar';
import { ChatTitle } from 'im.v2.component.elements.chat-title';

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
