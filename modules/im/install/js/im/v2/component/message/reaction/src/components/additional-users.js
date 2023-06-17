import {Core} from 'im.v2.application.core';
import {UserListPopup} from 'im.v2.component.elements';

import {UserService} from '../classes/user-service';

// @vue/component
export const AdditionalUsers = {
	components: {UserListPopup},
	props: {
		messageId: {
			type: Number,
			required: true
		},
		reaction: {
			type: String,
			required: true
		},
		show: {
			type: Boolean,
			required: true
		},
		bindElement: {
			type: Object,
			required: true
		}
	},
	emits: ['close'],
	data()
	{
		return {
			showPopup: false,
			loadingAdditionalUsers: false,
			additionalUsers: []
		};
	},
	watch:
	{
		show(newValue, oldValue)
		{
			if (!oldValue && newValue)
			{
				this.showPopup = true;
				this.loadUsers();
			}
		}
	},
	methods:
	{
		loadUsers()
		{
			this.loadingAdditionalUsers = true;
			this.getUserService().loadReactionUsers(this.messageId, this.reaction)
				.then(userIds => {
					this.additionalUsers = userIds;
					this.loadingAdditionalUsers = false;
				})
				.catch(() => {
					this.loadingAdditionalUsers = false;
				});
		},
		onPopupClose()
		{
			this.showPopup = false;
			this.$emit('close');
		},
		prepareAdditionalUsers(userIds: number[]): number[]
		{
			const firstViewerId = this.dialog.lastMessageViews.firstViewer.userId;

			return userIds.filter(userId => {
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
			id="bx-im-message-reaction-users"
			:showPopup="showPopup"
			:loading="loadingAdditionalUsers"
			:userIds="additionalUsers"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:offsetLeft="-112"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`
};