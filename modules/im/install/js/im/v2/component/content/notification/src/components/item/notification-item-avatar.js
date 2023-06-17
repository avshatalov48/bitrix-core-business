import {Avatar} from 'im.v2.component.elements';
import '../../css/notification-item-avatar.css';
import type {ImModelUser} from 'im.v2.model';

// @vue/component
export const NotificationItemAvatar = {
	name: 'NotificationItemAvatar',
	components: {Avatar},
	props: {
		userId: {
			type: Number,
			required: true
		}
	},
	computed:
	{
		isSystem(): boolean
		{
			return this.userId === 0;
		},
		dialogId(): string
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
			<template v-if="isSystem || !user">
				<div class="bx-im-content-notification-item-avatar__system-icon"></div>
			</template>
			<template v-else>
				<Avatar :dialogId="dialogId" size="L" :withStatus="false"></Avatar>
			</template>
		</div>
	`
};