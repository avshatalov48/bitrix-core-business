import { Core } from 'im.v2.application.core';
import { Avatar, AvatarSize, UserStatus, UserStatusSize } from 'im.v2.component.elements';
import { Utils } from 'im.v2.lib.utils';
import { DesktopManager } from 'im.v2.lib.desktop';
import { Logger } from 'im.v2.lib.logger';
import { Extension } from 'main.core';

import { ButtonPanel } from './button-panel';
import { UserStatusPopup } from '../status/user-status-popup';
import { VersionService } from '../../classes/version-service';

import 'ui.buttons';
import 'ui.feedback.form';

import type { ImModelUser } from 'im.v2.model';
import { UserStatus as UserStatusType } from 'im.v2.const';

// @vue/component
export const UserSettingsContent = {
	name: 'UserSettingsContent',
	components: { Avatar, UserStatus, ButtonPanel, UserStatusPopup },
	emits: ['closePopup', 'enableAutoHide', 'disableAutoHide'],
	data(): Object
	{
		return {
			showStatusPopup: false,
			isChangingVersion: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		UserStatusSize: () => UserStatusSize,
		currentUserId(): number
		{
			return Core.getUserId();
		},
		currentUser(): ImModelUser
		{
			return this.$store.getters['users/get'](this.currentUserId, true);
		},
		currentUserPosition(): string
		{
			return this.$store.getters['users/getPosition'](this.currentUserId);
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
		currentHost(): string
		{
			return location.hostname;
		},
		userStatusText(): string
		{
			return Utils.user.getStatusText(this.currentUser.status);
		},
		profileUri(): string
		{
			return Utils.user.getProfileLink(this.currentUserId);
		},
		showOldChatButton(): boolean
		{
			const settings = Extension.getSettings('im.v2.component.navigation');

			return Boolean(settings.get('force_beta')) === false;
		},
	},
	methods:
	{
		onBackToOldChatClick()
		{
			this.isChangingVersion = true;
			this.getVersionService().disableBeta().then(() => {
				if (DesktopManager.isDesktop())
				{
					window.location.reload();
				}
				else
				{
					window.location.replace('/online/');
				}
			}).catch((error) => {
				Logger.error('Error while switching version', error);
			});
		},
		onStatusClick()
		{
			this.showStatusPopup = true;
			this.$emit('disableAutoHide');
		},
		onStatusPopupClose()
		{
			this.showStatusPopup = false;
			this.$emit('enableAutoHide');
		},
		getVersionService(): VersionService
		{
			if (!this.versionService)
			{
				this.versionService = new VersionService();
			}

			return this.versionService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-user-settings-popup__scope bx-im-user-settings-popup__container">
			<div class="bx-im-user-settings-popup__header">
				<div class="bx-im-user-settings-popup__header_left">
					<Avatar :dialogId="currentUserId" :size="AvatarSize.XL" />
				</div>
				<div class="bx-im-user-settings-popup__header_right">
					<div class="bx-im-user-settings-popup__domain">{{ currentHost }}</div>
					<div class="bx-im-user-settings-popup__user_name" :title="currentUser.name">{{ currentUser.name }}</div>
					<div class="bx-im-user-settings-popup__user_title" :title="currentUserPosition">{{ currentUserPosition }}</div>
					<a :href="profileUri" target="_blank" class="bx-im-user-settings-popup__user_link">
						<ButtonPanel @openProfile="$emit('closePopup')" />
					</a>
				</div>
			</div>
			<div class="bx-im-user-settings-popup__list">
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Status select -->
				<div @click="onStatusClick" class="bx-im-user-settings-popup__list-item --with-icon">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_status">
							<UserStatus :status="userStatus" :size="UserStatusSize.M" />
						</div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ userStatusText }}</div>
					</div>
					<div class="bx-im-user-settings-popup__list-item_icon --chevron" ref="status-select"></div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
			</div>
			<!-- Back to old chat -->
			<div v-if="showOldChatButton" class="bx-im-user-settings-popup__old-chat" :class="{'--loading': isChangingVersion}" @click="onBackToOldChatClick">
				<div class="bx-im-user-settings-popup__list-item_icon --arrow-left"></div>
				<div class="bx-im-user-settings-popup__old-chat_text">
					{{ loc('IM_USER_SETTINGS_OLD_CHAT') }}
				</div>
			</div>
		</div>
		<UserStatusPopup
			v-if="showStatusPopup"
			:bindElement="$refs['status-select'] || {}"
			@close="onStatusPopupClose"
		/>
	`,
};
