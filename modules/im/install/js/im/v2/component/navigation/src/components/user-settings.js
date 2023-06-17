import {Core} from 'im.v2.application.core';
import {UserStatus as UserStatusType} from 'im.v2.const';
import {Avatar, AvatarSize} from 'im.v2.component.elements';

import {UserSettingsPopup} from './settings/user-settings-popup';
import {UserStatusPopup} from './status/user-status-popup';

import '../css/user-settings.css';
import '../css/user-status.css';

// @vue/component
export const UserSettings = {
	name: 'UserSettings',
	components: {UserSettingsPopup, UserStatusPopup, Avatar},
	data()
	{
		return {
			showSettingsPopup: false,
			showStatusPopup: false
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		currentUserId()
		{
			return Core.getUserId();
		},
		currentUserStatus(): string
		{
			const status = this.$store.getters['users/getStatus'](this.currentUserId);
			if (status)
			{
				return status;
			}

			return UserStatusType.online;
		}
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
			<div @click="onAvatarClick" :class="{'--active': showSettingsPopup || showStatusPopup}" class="bx-im-navigation__user_avatar" ref="avatar">
				<Avatar :dialogId="currentUserId.toString()" :size="AvatarSize.M" />
				<div @click.stop="onStatusClick" :class="'--' + currentUserStatus" class="bx-im-navigation__user_status" ref="status"></div>
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
	`
};