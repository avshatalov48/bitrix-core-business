import { MenuManager } from 'main.popup';

import { Core } from 'im.v2.application.core';
import { ChatAvatar, AvatarSize, UserStatus, UserStatusSize, ScrollWithGradient } from 'im.v2.component.elements';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';
import { Utils } from 'im.v2.lib.utils';
import { PopupType, Settings, UserStatus as UserStatusType } from 'im.v2.const';

import { ButtonPanel } from './button-panel';
import { UserStatusPopup } from '../status/user-status-popup';
import { DesktopAccountList } from './desktop-account-list/desktop-account-list';

import 'ui.buttons';
import 'ui.feedback.form';

import type { ImModelUser } from 'im.v2.model';

// @vue/component
export const UserSettingsContent = {
	name: 'UserSettingsContent',
	components: { ChatAvatar, UserStatus, ButtonPanel, UserStatusPopup, DesktopAccountList, ScrollWithGradient },
	emits: ['closePopup', 'enableAutoHide', 'disableAutoHide'],
	data(): Object
	{
		return {
			showStatusPopup: false,
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
		currentUserDialogId(): string
		{
			return this.currentUserId.toString();
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
			const status = this.$store.getters['application/settings/get'](Settings.user.status);
			if (status)
			{
				return status;
			}

			return UserStatusType.online;
		},
		currentHost(): string
		{
			return location.hostname;
		},
		userStatusText(): string
		{
			return Utils.user.getStatusText(this.userStatus);
		},
		isDesktopAccountManagementAvailable(): boolean
		{
			return DesktopApi.isFeatureSupported(DesktopFeature.accountManagement.id);
		},
	},
	methods:
	{
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onScroll()
		{
			MenuManager.getMenuById(PopupType.desktopItemMenu)?.close();
		},
	},
	template: `
		<div class="bx-im-user-settings-popup__scope bx-im-user-settings-popup__container">
			<div class="bx-im-user-settings-popup__header">
				<div class="bx-im-user-settings-popup__header_left">
					<ChatAvatar 
						:avatarDialogId="currentUserDialogId" 
						:contextDialogId="currentUserDialogId" 
						:size="AvatarSize.XL" 
					/>
				</div>
				<div class="bx-im-user-settings-popup__header_right">
					<div class="bx-im-user-settings-popup__domain">{{ currentHost }}</div>
					<div class="bx-im-user-settings-popup__user_name" :title="currentUser.name">{{ currentUser.name }}</div>
					<div class="bx-im-user-settings-popup__user_title" :title="currentUserPosition">{{ currentUserPosition }}</div>
					<ButtonPanel @openProfile="$emit('closePopup')" />
				</div>
			</div>
			<ScrollWithGradient :containerMaxHeight="328" :gradientHeight="24" @scroll="onScroll">
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
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<DesktopAccountList 
					v-if="isDesktopAccountManagementAvailable"
					@openContextMenu="$emit('disableAutoHide')"
				/>
			</ScrollWithGradient>
		</div>
		<UserStatusPopup
			v-if="showStatusPopup"
			:bindElement="$refs['status-select'] || {}"
			@close="onStatusPopupClose"
		/>
	`,
};
