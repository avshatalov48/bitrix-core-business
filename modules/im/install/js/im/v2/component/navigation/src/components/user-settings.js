import { Core } from 'im.v2.application.core';
import { Settings, UserStatus as UserStatusType } from 'im.v2.const';
import { ChatAvatar, AvatarSize } from 'im.v2.component.elements';

import { UserSettingsPopup } from './settings/user-settings-popup';
import { UserStatusPopup } from './status/user-status-popup';

import '../css/user-settings.css';
import '../css/user-status.css';

// @vue/component
export const UserSettings = {
	name: 'UserSettings',
	components: { UserSettingsPopup, UserStatusPopup, ChatAvatar },
	data(): Object
	{
		return {
			showSettingsPopup: false,
			showStatusPopup: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		currentUserDialogId(): string
		{
			return Core.getUserId().toString();
		},
		userStatus(): string
		{
			const status = this.$store.getters['application/settings/get'](Settings.user.status);
			if (status)
			{
				return status;
			}

			return UserStatusType.online;
		},
	},
	methods:
	{
		onAvatarClick()
		{
			this.showSettingsPopup = true;
		},
		onStatusClick()
		{
			this.showStatusPopup = true;
		},
	},
	template: `
		<div class="bx-im-navigation__user">
			<div @click="onAvatarClick" class="bx-im-navigation__user_avatar" ref="avatar">
				<ChatAvatar 
					:avatarDialogId="currentUserDialogId"
					:contextDialogId="currentUserDialogId" 
					:size="AvatarSize.M" 
				/>
				<div @click.stop="onStatusClick" :class="'--' + userStatus" class="bx-im-navigation__user_status" ref="status"></div>
			</div>
			<UserStatusPopup
				v-if="showStatusPopup"
				:bindElement="$refs['status'] || {}"
				@close="showStatusPopup = false"
			/>
			<UserSettingsPopup
				v-if="showSettingsPopup"
				:bindElement="$refs['avatar'] || {}"
				@close="showSettingsPopup = false" 
			/>
		</div>
	`,
};
