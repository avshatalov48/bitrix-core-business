import { EventEmitter } from 'main.core.events';

import { ChatActionType, EventType, SidebarDetailBlock } from 'im.v2.const';
import { AddToChat } from 'im.v2.component.entity-selector';
import { PermissionManager } from 'im.v2.lib.permission';
import {
	Avatar,
	AvatarSize,
	ChatTitle,
	Button as MessengerButton,
	ButtonSize,
	ButtonColor,
} from 'im.v2.component.elements';

import { Settings } from './settings';

import './css/chat-preview.css';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

// @vue/component
export const ChatPreview = {
	name: 'ChatPreview',
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
	emits: ['openDetail'],
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
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogIds(): string[]
		{
			const PREVIEW_USERS_COUNT = 4;
			const userIds = this.$store.getters['sidebar/members/get'](this.chatId);

			return userIds.map((id) => id.toString()).slice(0, PREVIEW_USERS_COUNT);
		},
		usersInChatCount(): number
		{
			return this.dialog.userCounter;
		},
		moreUsersCount(): number
		{
			return Math.max(this.usersInChatCount - this.dialogIds.length, 0);
		},
		canSeeMembers(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.userList, this.dialogId);
		},
		canInviteMembers(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.extend, this.dialogId);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	methods:
	{
		onOpenUsers()
		{
			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.members,
				dialogId: this.dialogId,
			});
		},
		onOpenInvitePopup()
		{
			this.showAddToChatPopup = true;
		},
	},
	template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-group-chat__avatar-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-group-chat__avatar-container">
				<div class="bx-im-sidebar-main-preview-group-chat__avatar">
					<Avatar :size="AvatarSize.XXXL" :dialogId="dialogId" />
				</div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-main-preview-group-chat__title" />
			</div>
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-group-chat__members-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-group-chat__members-container">
				<div v-if="canSeeMembers" class="bx-im-sidebar-main-preview-group-chat__members" @click="onOpenUsers">
					<div class="bx-im-sidebar-main-preview-group-chat__members-avatars" >
						<Avatar
							class="bx-im-sidebar-main-preview-group-chat__chat-user-avatar"
							v-for="id in dialogIds"
							:size="AvatarSize.S"
							:dialogId="id"
						/>
					</div>
					<div v-if="moreUsersCount > 0" class="bx-im-sidebar-main-preview-group-chat__more-users-count-text">
						+{{ moreUsersCount }}
					</div>
				</div>
				<div ref="add-members">
					<MessengerButton
						v-if="canInviteMembers"
						:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
						:size="ButtonSize.S"
						:color="ButtonColor.PrimaryLight"
						:isRounded="true"
						:isUppercase="false"
						icon="plus"
						@click="onOpenInvitePopup"
					/>
				</div>
			</div>
			<Settings :isLoading="isLoading" :dialogId="dialogId" />
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: -220, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`,
};
