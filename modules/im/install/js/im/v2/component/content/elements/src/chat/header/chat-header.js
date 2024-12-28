import 'ui.notification';
import { EventEmitter } from 'main.core.events';

import { LineLoader, ChatAvatar } from 'im.v2.component.elements';
import { ChatService } from 'im.v2.provider.service';
import { ChatType, ActionByRole, EventType, SidebarDetailBlock, UserType, ActionByUserType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { FadeAnimation } from 'im.v2.component.animation';

import { CallHeaderButton } from './components/call-button/call-header-button';
import { GroupChatTitle } from './components/title/group-chat';
import { UserTitle as UserChatTitle } from './components/title/user';
import { HeaderAvatar } from './components/header-avatar';
import { AddToChatButton } from './components/add-to-chat-button';
import { SearchButton } from './components/search-button';
import { SidebarButton } from './components/sidebar-button';

import './css/chat-header.css';

import type { JsonObject } from 'main.core';
import type { ImModelUser, ImModelChat } from 'im.v2.model';

const HEADER_WIDTH_BREAKPOINT = 700;

// @vue/component
export const ChatHeader = {
	name: 'ChatHeader',
	components: {
		ChatAvatar,
		CallHeaderButton,
		GroupChatTitle,
		UserChatTitle,
		LineLoader,
		FadeAnimation,
		HeaderAvatar,
		AddToChatButton,
		SearchButton,
		SidebarButton,
	},
	inject: ['currentSidebarPanel', 'withSidebar'],
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
	},
	emits: ['buttonPanelReady', 'compactModeChange'],
	data(): JsonObject
	{
		return {
			compactMode: false,
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

			return this.user.type === UserType.bot;
		},
		showCallButton(): boolean
		{
			if (this.isBot || !this.withCallButton)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.call, this.dialogId);
		},
		showAddToChatButton(): boolean
		{
			if (this.isBot)
			{
				return false;
			}

			const hasCreateChatAccess = PermissionManager.getInstance().canPerformActionByUserType(
				ActionByUserType.createChat,
			);
			if (this.isUser && !hasCreateChatAccess)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.extend, this.dialogId);
		},
		showSearchButton(): boolean
		{
			return this.withSearchButton;
		},
		showSidebarButton(): boolean
		{
			if (!this.withSidebar)
			{
				return false;
			}

			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.openSidebar, this.dialogId);
		},
		isMembersPanelActive(): boolean
		{
			return this.currentSidebarPanel === SidebarDetailBlock.members;
		},
		chatTitleComponent(): string
		{
			return this.isUser ? UserChatTitle : GroupChatTitle;
		},
		containerClasses(): Record<string, boolean>
		{
			return { '--compact': this.compactMode };
		},
	},
	mounted()
	{
		this.initResizeObserver();
	},
	beforeUnmount()
	{
		this.getResizeObserver().disconnect();
	},
	methods:
	{
		initResizeObserver()
		{
			this.resizeObserver = new ResizeObserver(([entry]) => {
				this.onContainerResize(entry.contentRect.width);
			});
			this.resizeObserver.observe(this.$refs.container);
		},
		onContainerResize(newContainerWidth: number)
		{
			const newCompactMode = newContainerWidth <= HEADER_WIDTH_BREAKPOINT;
			if (newCompactMode !== this.compactMode)
			{
				this.$emit('compactModeChange', newCompactMode);
				this.compactMode = newCompactMode;
			}
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
		getResizeObserver(): ResizeObserver
		{
			return this.resizeObserver;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-chat-header__scope bx-im-chat-header__container" :class="containerClasses" ref="container">
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
					<CallHeaderButton v-if="showCallButton" :dialogId="dialogId" :compactMode="compactMode" />
					<slot v-if="showAddToChatButton" name="add-to-chat-button">
						<AddToChatButton :dialogId="dialogId" />
					</slot>
					<SearchButton v-if="showSearchButton" :dialogId="dialogId" />
					<SidebarButton v-if="showSidebarButton" :dialogId="dialogId" />
				</div>
			</FadeAnimation>
		</div>
	`,
};
