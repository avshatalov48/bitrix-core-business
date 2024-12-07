import { PermissionManager } from 'im.v2.lib.permission';
import { AddToChat } from 'im.v2.component.entity-selector';
import { ChatActionType, EventType, Layout, SidebarDetailBlock } from 'im.v2.const';

import { EventEmitter } from 'main.core.events';
import {
	ChatAvatar,
	AvatarSize,
	Button as MessengerButton,
	ButtonSize,
	ButtonColor,
} from 'im.v2.component.elements';

import './chat-members-avatars.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatMembersAvatars = {
	name: 'ChatMembersAvatars',
	components: { ChatAvatar, MessengerButton, AddToChat },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		showMembers: {
			type: Boolean,
			default: true,
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
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogIds(): string[]
		{
			const PREVIEW_USERS_COUNT = 4;
			const userIds = this.$store.getters['sidebar/members/get'](this.chatId);

			return userIds.map((id: number) => id.toString()).slice(0, PREVIEW_USERS_COUNT);
		},
		canSeeMembers(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.userList, this.dialogId);
		},
		canInviteMembers(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.extend, this.dialogId);
		},
		usersInChatCount(): number
		{
			return this.dialog.userCounter;
		},
		moreUsersCount(): number
		{
			return Math.max(this.usersInChatCount - this.dialogIds.length, 0);
		},
		isCopilotLayout(): boolean
		{
			const { name: currentLayoutName } = this.$store.getters['application/getLayout'];

			return currentLayoutName === Layout.copilot.name;
		},
		addUsersButtonColor(): ButtonColor
		{
			if (this.isCopilotLayout)
			{
				return this.ButtonColor.Copilot;
			}

			return this.ButtonColor.PrimaryLight;
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-chat-members-avatars__container">
			<div v-if="canSeeMembers && showMembers" class="bx-im-sidebar-chat-members-avatars__members" @click="onOpenUsers">
				<div class="bx-im-sidebar-chat-members-avatars__avatars" >
					<ChatAvatar
						v-for="id in dialogIds"
						:size="AvatarSize.S"
						:avatarDialogId="id"
						:contextDialogId="dialogId"
						class="bx-im-sidebar-chat-members-avatars__avatar"
					/>
				</div>
				<div v-if="moreUsersCount > 0" class="bx-im-sidebar-chat-members-avatars__text">
					+{{ moreUsersCount }}
				</div>
			</div>
			<div ref="add-members">
				<MessengerButton
					v-if="canInviteMembers"
					:text="loc('IM_SIDEBAR_ADD_BUTTON_TEXT')"
					:size="ButtonSize.S"
					:color="addUsersButtonColor"
					:isRounded="true"
					:isUppercase="false"
					icon="plus"
					@click="onOpenInvitePopup"
				/>
			</div>
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
