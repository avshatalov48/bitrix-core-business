import { Core } from 'im.v2.application.core';
import { UserStatus as UserStatusType } from 'im.v2.const';
import { Avatar, AvatarSize } from 'im.v2.component.elements';

import { UserSettingsPopup } from './settings/user-settings-popup';
import { UserStatusPopup } from './status/user-status-popup';

import '../css/user-settings.css';
import '../css/user-status.css';

// @vue/component
export const UserSettings = {
	name: 'UserSettings',
	components: { UserSettingsPopup, UserStatusPopup, Avatar },
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
		currentUserId(): number
		{
			return Core.getUserId();
		},
		userStatus(): string
		{
			const user = this.$store.getters['users/get'](this.currentUserId, true);
			if (user)
			{
				return user.status;
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
				<Avatar :dialogId="currentUserId.toString()" :size="AvatarSize.M" />
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
