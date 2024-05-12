import { EventEmitter } from 'main.core.events';
import 'ui.notification';

import { Avatar, AvatarSize } from 'im.v2.component.elements';
import { ChatService } from 'im.v2.provider.service';
import { ChatType, ChatActionType, UserRole, EventType, SidebarDetailBlock } from 'im.v2.const';
import { AddToChat } from 'im.v2.component.entity-selector';
import { Utils } from 'im.v2.lib.utils';
import { PermissionManager } from 'im.v2.lib.permission';

import { CallButton } from './components/call-button/call-button';
import { GroupChatTitle } from './components/group-chat-title';
import { UserTitle } from './components/user-title';

import '../../css/chat-header.css';

import type { JsonObject } from 'main.core';
import type { ImModelUser, ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatHeader = {
	name: 'ChatHeader',
	components: { Avatar, AddToChat, CallButton, GroupChatTitle, UserTitle },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		currentSidebarPanel: {
			type: String,
			default: '',
		},
	},
	emits: ['toggleRightPanel', 'toggleSearchPanel', 'toggleMembersPanel'],
	data(): JsonObject
	{
		return {
			showAddToChatPopup: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isInited(): boolean
		{
			return this.dialog.inited;
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isBot(): boolean
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.user.bot === true;
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		isGuest(): boolean
		{
			return this.dialog.role === UserRole.guest;
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		userLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
		showCallButton(): boolean
		{
			return !this.isBot;
		},
		showInviteButton(): boolean
		{
			if (this.isBot)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformAction(ChatActionType.extend, this.dialogId);
		},
		canChangeAvatar(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.avatar, this.dialogId);
		},
		isSidebarOpened(): boolean
		{
			return this.currentSidebarPanel.length > 0;
		},
		isMessageSearchActive(): boolean
		{
			return this.currentSidebarPanel === SidebarDetailBlock.messageSearch;
		},
	},
	methods:
	{
		toggleRightPanel()
		{
			if (this.currentSidebarPanel)
			{
				EventEmitter.emit(EventType.sidebar.close, { panel: '' });

				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.main,
				dialogId: this.dialogId,
			});
		},
		toggleSearchPanel()
		{
			if (this.isMessageSearchActive)
			{
				EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.messageSearch });

				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.messageSearch,
				dialogId: this.dialogId,
			});
		},
		onMembersClick()
		{
			if (!this.isInited)
			{
				return;
			}

			if (this.currentSidebarPanel === SidebarDetailBlock.members)
			{
				EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.members });

				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.members,
				dialogId: this.dialogId,
			});
		},
		onNewTitleSubmit(newTitle: string)
		{
			this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_CONTENT_CHAT_HEADER_RENAME_ERROR'),
				});
			});
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		openInvitePopup()
		{
			this.showAddToChatPopup = true;
		},
		onAvatarClick()
		{
			if (!this.isChat || !this.canChangeAvatar)
			{
				return;
			}
			this.$refs.avatarInput.click();
		},
		async onAvatarSelect(event: Event)
		{
			const input: HTMLInputElement = event.target;
			const file: File = input.files[0];
			if (!file)
			{
				return;
			}

			const preparedAvatar = await this.getChatService().prepareAvatar(file);
			if (!preparedAvatar)
			{
				return;
			}
			void this.getChatService().changeAvatar(this.dialog.chatId, preparedAvatar);
		},
		onContainerClick(event: PointerEvent)
		{
			if (this.isGuest)
			{
				event.stopPropagation();
			}
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div @click.capture="onContainerClick" class="bx-im-chat-header__scope bx-im-chat-header__container">
			<div class="bx-im-chat-header__left">
				<div class="bx-im-chat-header__avatar" :class="{'--can-change': canChangeAvatar}" @click="onAvatarClick">
					<Avatar v-if="isChat" :dialogId="dialogId" :size="AvatarSize.L" />
					<a v-else :href="userLink" target="_blank">
						<Avatar :dialogId="dialogId" :size="AvatarSize.L" />
					</a>
				</div>
				<input 
					type="file" 
					@change="onAvatarSelect" 
					accept="image/*" 
					class="bx-im-chat-header__avatar_input" 
					ref="avatarInput"
				>
				<GroupChatTitle
					v-if="isChat"
					:dialogId="dialogId"
					@membersClick="onMembersClick"
					@newTitle="onNewTitleSubmit"
				/>
				<UserTitle v-else :dialogId="dialogId" />
			</div>
			<div class="bx-im-chat-header__right">
				<CallButton v-if="showCallButton" :dialogId="dialogId" />
				<div
					v-if="showInviteButton"
					:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_INVITE_POPUP_TITLE')"
					:class="{'--active': showAddToChatPopup}"
					class="bx-im-chat-header__icon --add-people"
					@click="openInvitePopup" 
					ref="add-members"
				></div>
				<div 
					:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SEARCH')"
					:class="{'--active': isMessageSearchActive}"
					class="bx-im-chat-header__icon --search" 
					@click="toggleSearchPanel"
				></div>
				<div 
					class="bx-im-chat-header__icon --panel"
					:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SIDEBAR')"
					:class="{'--active': isSidebarOpened}"
					@click="toggleRightPanel" 
				></div>
			</div>
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 15, offsetLeft: -300}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`,
};
