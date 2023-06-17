import {Core} from 'im.v2.application.core';
import {UserListPopup} from 'im.v2.component.elements';

import {UserService} from '../classes/user-servlce';

import type {ImModelDialog} from 'im.v2.model';

// @vue/component
export const AdditionalUsers = {
	components: {UserListPopup},
	props: {
		dialogId: {
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
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
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
			this.getUserService().loadReadUsers(this.dialog.lastMessageId)
				.then(userIds => {
					this.additionalUsers = this.prepareAdditionalUsers(userIds);
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
			id="bx-im-dialog-read-users"
			:showPopup="showPopup"
			:loading="loadingAdditionalUsers"
			:userIds="additionalUsers"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`
};