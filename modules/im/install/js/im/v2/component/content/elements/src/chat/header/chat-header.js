import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import 'ui.notification';

import { LineLoader, ChatAvatar } from 'im.v2.component.elements';
import { ChatService } from 'im.v2.provider.service';
import { ChatType, ChatActionType, EventType, SidebarDetailBlock } from 'im.v2.const';
import { AddToChat } from 'im.v2.component.entity-selector';
import { PermissionManager } from 'im.v2.lib.permission';
import { FadeAnimation } from 'im.v2.component.animation';

import { CallButton } from './components/call-button/call-button';
import { GroupChatTitle } from './components/title/group-chat';
import { UserTitle as UserChatTitle } from './components/title/user';
import { HeaderAvatar } from './components/header-avatar';

import './css/chat-header.css';

import type { JsonObject } from 'main.core';
import type { ImModelUser, ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatHeader = {
	name: 'ChatHeader',
	components: {
		ChatAvatar,
		AddToChat,
		CallButton,
		GroupChatTitle,
		UserChatTitle,
		LineLoader,
		FadeAnimation,
		HeaderAvatar,
	},
	inject: ['currentSidebarPanel'],
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		withCallButton: {
			type: Boolean,
			default: true,
		},
		withSearchButton: {
			type: Boolean,
			default: true,
		},
		withSidebarButton: {
			type: Boolean,
			default: true,
		},
	},
	emits: ['toggleRightPanel', 'toggleSearchPanel', 'toggleMembersPanel', 'buttonPanelReady'],
	data(): JsonObject
	{
		return {
			showAddToChatPopup: false,
		};
	},
	computed:
	{
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
		showCallButton(): boolean
		{
			if (this.isBot || !this.withCallButton)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformAction(ChatActionType.call, this.dialogId);
		},
		showInviteButton(): boolean
		{
			if (this.isBot)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformAction(ChatActionType.extend, this.dialogId);
		},
		showSearchButton(): boolean
		{
			return this.withSearchButton;
		},
		showSidebarButton(): boolean
		{
			if (!this.withSidebarButton)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformAction(ChatActionType.openSidebar, this.dialogId);
		},
		isSidebarOpened(): boolean
		{
			return Type.isStringFilled(this.currentSidebarPanel);
		},
		isMessageSearchActive(): boolean
		{
			return this.currentSidebarPanel === SidebarDetailBlock.messageSearch;
		},
		isMembersPanelActive(): boolean
		{
			return this.currentSidebarPanel === SidebarDetailBlock.members;
		},
		chatTitleComponent(): string
		{
			return this.isUser ? UserChatTitle : GroupChatTitle;
		},
	},
	methods:
	{
		toggleRightPanel()
		{
			if (this.isSidebarOpened)
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

			if (this.isMembersPanelActive)
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
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-chat-header__scope bx-im-chat-header__container">
			<div class="bx-im-chat-header__left">
				<slot name="left">
					<HeaderAvatar :dialogId="dialogId" />
					<slot name="title" :onNewTitleHandler="onNewTitleSubmit">
						<component
							:is="chatTitleComponent"
							:dialogId="dialogId"
							@membersClick="onMembersClick"
							@newTitle="onNewTitleSubmit"
						/>
					</slot>
				</slot>
			</div>
			<LineLoader v-if="!isInited" :width="45" :height="22" />
			<FadeAnimation @afterEnter="$emit('buttonPanelReady')" :duration="100">
				<div v-if="isInited" class="bx-im-chat-header__right">
					<slot name="before-actions"></slot>
					<CallButton v-if="showCallButton" :dialogId="dialogId" />
					<div
						v-if="showInviteButton"
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_INVITE_POPUP_TITLE')"
						:class="{'--active': showAddToChatPopup}"
						class="bx-im-chat-header__icon --add-people"
						@click="showAddToChatPopup = true" 
						ref="add-members"
					>
						<slot name="invite-hint" :inviteButtonRef="$refs['add-members']"></slot>
					</div>
					<div
						v-if="showSearchButton"
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SEARCH')"
						:class="{'--active': isMessageSearchActive}"
						class="bx-im-chat-header__icon --search" 
						@click="toggleSearchPanel"
					></div>
					<div
						v-if="showSidebarButton"
						class="bx-im-chat-header__icon --panel"
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SIDEBAR')"
						:class="{'--active': isSidebarOpened}"
						@click="toggleRightPanel" 
					></div>
				</div>
			</FadeAnimation>
			<AddToChat
				:bindElement="$refs['add-members'] ?? {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{ offsetTop: 15, offsetLeft: -300 }"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`,
};
