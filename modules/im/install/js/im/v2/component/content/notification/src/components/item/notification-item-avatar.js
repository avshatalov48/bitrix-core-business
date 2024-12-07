import { ChatAvatar, AvatarSize } from 'im.v2.component.elements';

import '../../css/notification-item-avatar.css';

import type { ImModelUser } from 'im.v2.model';

// @vue/component
export const NotificationItemAvatar = {
	name: 'NotificationItemAvatar',
	components: { ChatAvatar },
	props: {
		userId: {
			type: Number,
			required: true,
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		isSystem(): boolean
		{
			return this.userId === 0;
		},
		userDialogId(): string
		{
			return this.userId.toString();
		},
		user(): ?ImModelUser
		{
			// For now, we don't have a user if it is an OL user.
			return this.$store.getters['users/get'](this.userId);
		},
	},
	template: `
		<div class="bx-im-content-notification-item-avatar__container">
			<div 
				v-if="isSystem || !user"
				class="bx-im-content-notification-item-avatar__system-icon"
			></div>
			<ChatAvatar 
				v-else 
				:avatarDialogId="userDialogId" 
				:contextDialogId="userDialogId" 
				:size="AvatarSize.L" 
			/>
		</div>
	`,
};
