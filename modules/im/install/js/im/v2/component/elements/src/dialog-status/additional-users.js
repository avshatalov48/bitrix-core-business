import { Core } from 'im.v2.application.core';

import { UserService } from './user-service';
import { UserListPopup } from '../registry';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const AdditionalUsers = {
	components: { UserListPopup },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		show: {
			type: Boolean,
			required: true,
		},
		bindElement: {
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {
			showPopup: false,
			loadingAdditionalUsers: false,
			additionalUsers: [],
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
	},
	watch:
	{
		show(newValue, oldValue)
		{
			if (!oldValue && newValue)
			{
				this.showPopup = true;
				void this.loadUsers();
			}
		},
	},
	methods:
	{
		async loadUsers()
		{
			this.loadingAdditionalUsers = true;
			const userIds = await this.getUserService().loadReadUsers(this.dialog.lastMessageId)
				.catch(() => {
					this.loadingAdditionalUsers = false;
				});

			this.additionalUsers = this.prepareAdditionalUsers(userIds);
			this.loadingAdditionalUsers = false;
		},
		onPopupClose()
		{
			this.showPopup = false;
			this.$emit('close');
		},
		prepareAdditionalUsers(userIds: number[]): number[]
		{
			const firstViewerId = this.dialog.lastMessageViews.firstViewer.userId;

			return userIds.filter((userId) => {
				return userId !== Core.getUserId() && userId !== firstViewerId;
			});
		},
		getUserService(): UserService
		{
			if (!this.userService)
			{
				this.userService = new UserService();
			}

			return this.userService;
		},
	},
	template: `
		<UserListPopup
			id="bx-im-dialog-read-users"
			:showPopup="showPopup"
			:loading="loadingAdditionalUsers"
			:userIds="additionalUsers"
			:contextDialogId="dialogId"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`,
};
