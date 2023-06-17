import {Core} from 'im.v2.application.core';
import {Avatar, AvatarSize, UserStatus, UserStatusSize} from 'im.v2.component.elements';
import {Utils} from 'im.v2.lib.utils';

import {ButtonPanel} from './button-panel';
import {UserStatusPopup} from '../status/user-status-popup';
import {BackgroundPopup} from '../background/background-popup';
import {VersionService} from '../../classes/version-service';

import 'ui.buttons';
import 'ui.feedback.form';

import type {ImModelUser} from 'im.v2.model';
import {UserStatus as UserStatusType} from 'im.v2.const';

// @vue/component
export const UserSettingsContent = {
	name: 'UserSettingsContent',
	components: {Avatar, UserStatus, ButtonPanel, UserStatusPopup, BackgroundPopup},
	emits: ['closePopup', 'enableAutoHide', 'disableAutoHide'],
	data()
	{
		return {
			showStatusPopup: false,
			showBackgroundPopup: false,
			isChangingVersion: false
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
		currentUserStatus(): string
		{
			const status = this.$store.getters['users/getStatus'](this.currentUserId);
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
			return Utils.user.getStatusText(this.currentUser.status);
		},
		profileUri(): string
		{
			return Utils.user.getProfileLink(this.currentUserId);
		}
	},
	methods:
	{
		onBackToOldChatClick()
		{
			this.isChangingVersion = true;
			this.getVersionService().disableV2Version().then(() => {
				window.location.replace('/online/');
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
		onBackgroundSelectClick()
		{
			this.showBackgroundPopup = true;
			this.$emit('disableAutoHide');
		},
		onBackgroundPopupClose()
		{
			this.showBackgroundPopup = false;
			this.$emit('enableAutoHide');
		},
		onHelpClick()
		{
			const ARTICLE_CODE = 17373696;
			BX.Helper?.show(`redirect=detail&code=${ARTICLE_CODE}`);
			this.$emit('closePopup');
		},
		onFeedbackClick()
		{
			BX.UI.Feedback.Form.open({
				id: 'im-v2-feedback',
				forms: [
					{zones: ['ru'], id: 550, sec: '50my2x', lang: 'ru'},
					{zones: ['en'], id: 560, sec: '621lbr', lang: 'ru'},
				],
				presets: {
					sender_page: 'profile'
				},
			});
			this.$emit('closePopup');
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
		}
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
					<a :href="profileUri" target="_blank">
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
							<UserStatus :status="currentUserStatus" :size="UserStatusSize.M" />
						</div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ userStatusText }}</div>
					</div>
					<div class="bx-im-user-settings-popup__list-item_icon --chevron" ref="status-select"></div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Background select -->
				<div @click="onBackgroundSelectClick" class="bx-im-user-settings-popup__list-item --with-icon">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_icon --background"></div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ loc('IM_USER_SETTINGS_CHAT_BACKGROUND') }}</div>
					</div>
					<div class="bx-im-user-settings-popup__list-item_icon --chevron" ref="background-select"></div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Help -->
				<div @click="onHelpClick" class="bx-im-user-settings-popup__list-item">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_icon --help"></div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ loc('IM_USER_SETTINGS_HELP') }}</div>
					</div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Feedback -->
				<div @click="onFeedbackClick" class="bx-im-user-settings-popup__list-item">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_icon --feedback"></div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ loc('IM_USER_SETTINGS_FEEDBACK') }}</div>
					</div>
				</div>
			</div>
			<!-- Back to old chat -->
			<div :class="{'--loading': isChangingVersion}" class="bx-im-user-settings-popup__old-chat">
				<div class="bx-im-user-settings-popup__list-item_icon --arrow-left"></div>
				<div @click="onBackToOldChatClick" class="bx-im-user-settings-popup__old-chat_text">
					{{ loc('IM_USER_SETTINGS_OLD_CHAT') }}
				</div>
			</div>
		</div>
		<UserStatusPopup
			v-if="showStatusPopup"
			:bindElement="$refs['status-select'] || {}"
			@close="onStatusPopupClose"
		/>
		<BackgroundPopup
			v-if="showBackgroundPopup"
			:bindElement="$refs['background-select'] || {}"
			@close="onBackgroundPopupClose"
		/>
	`
};