import { hint } from 'ui.vue3.directives.hint';

import { Avatar, AvatarSize, ChatTitle, Button as MessengerButton, ButtonColor, ButtonSize } from 'im.v2.component.elements';
import { ChatActionType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { AddToChat } from 'im.v2.component.entity-selector';
import { PermissionManager } from 'im.v2.lib.permission';

import { Settings } from './settings';

import './css/user-preview.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelUser } from 'im.v2.model';

// @vue/component
export const UserPreview = {
	name: 'UserPreview',
	directives: { hint },
	components: { Avatar, ChatTitle, MessengerButton, AddToChat, Settings },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		isLoading: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			showAddToChatPopup: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		userPosition(): string
		{
			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		canInviteMembers(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.extend, this.dialogId);
		},
		showInviteButton(): boolean
		{
			if (this.isBot)
			{
				return false;
			}

			return this.canInviteMembers;
		},
		userLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
		isBot(): boolean
		{
			return this.user.bot === true;
		},
	},
	methods:
	{
		onAddClick()
		{
			this.showAddToChatPopup = true;
		},
	},
	template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-personal-chat__avatar-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-personal-chat__avatar-container">
				<Avatar
					:size="AvatarSize.XXXL"
					:dialogId="dialogId"
					class="bx-im-sidebar-main-preview-personal-chat__avatar"
				/>
				<a :href="userLink" target="_blank">
					<ChatTitle :dialogId="dialogId" class="bx-im-sidebar-main-preview-personal-chat__user-name" />
				</a>
				<div class="bx-im-sidebar-main-preview-personal-chat__user-position" :title="userPosition">
					{{ userPosition }}
				</div>
			</div>
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-personal-chat__invite-button-skeleton"></div>
			<div 
				v-else-if="showInviteButton" 
				class="bx-im-sidebar-main-preview-personal-chat__invite-button-container" 
				ref="add-members"
			>
				<MessengerButton
					v-if="canInviteMembers"
					:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_CREATE_GROUP_CHAT')"
					:size="ButtonSize.S"
					:color="ButtonColor.PrimaryLight"
					:isRounded="true"
					:isUppercase="false"
					icon="plus"
					@click="onAddClick"
				/>
			</div>
			<Settings :isLoading="isLoading" :dialogId="dialogId" />
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: -220, offsetLeft: -320}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`,
};
