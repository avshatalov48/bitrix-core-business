import { BaseEvent } from 'main.core.events';
import { getFilesFromDataTransfer, hasDataTransferOnlyFiles } from 'ui.uploader.core';

import { Messenger } from 'im.public';
import { ChatDialog } from 'im.v2.component.dialog.chat';
import { ChatTextarea } from 'im.v2.component.textarea';
import { ChatService, UploadingService } from 'im.v2.provider.service';
import { Logger } from 'im.v2.lib.logger';
import { ThemeManager } from 'im.v2.lib.theme';
import { Utils } from 'im.v2.lib.utils';
import { PermissionManager } from 'im.v2.lib.permission';
import { ResizeManager } from 'im.v2.lib.textarea';
import { ChatSidebar } from 'im.v2.component.sidebar';
import { LayoutManager } from 'im.v2.lib.layout';
import { Layout, ChatActionType, Settings, UserRole, ChatType, SidebarDetailBlock } from 'im.v2.const';

import { UserService } from './classes/user-service';
import { ChatHeader } from './components/chat-header/chat-header';
import { DropArea } from './components/drop-area';
import { EmptyState } from './components/empty-state';
import { MutePanel } from './components/mute-panel';
import { JoinPanel } from './components/join-panel';

import './css/chat-content.css';

import 'ui.notification';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelLayout } from 'im.v2.model';
import type { BackgroundStyle } from 'im.v2.lib.theme';

const CHAT_HEADER_HEIGHT = 64;

// @vue/component
export const ChatContent = {
	name: 'ChatContent',
	components:
	{
		ChatHeader,
		ChatDialog,
		ChatTextarea,
		ChatSidebar,
		DropArea,
		EmptyState,
		MutePanel,
		JoinPanel,
	},
	directives:
	{
		'textarea-observer': {
			mounted(element, binding)
			{
				binding.instance.textareaResizeManager.observeTextarea(element);
			},
			beforeUnmount(element, binding)
			{
				binding.instance.textareaResizeManager.unobserveTextarea(element);
			},
		},
	},
	props:
	{
		entityId: {
			type: String,
			default: '',
		},
		contextMessageId: {
			type: Number,
			default: 0,
		},
	},
	data(): JsonObject
	{
		return {
			currentSidebarPanel: '',

			textareaHeight: 0,
			showDropArea: false,
			lastDropAreaEnterTarget: null,
		};
	},
	computed:
	{
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.entityId, true);
		},
		hasPinnedMessages(): boolean
		{
			return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId).length > 0;
		},
		canPost(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.send, this.dialog.dialogId);
		},
		isGuest(): boolean
		{
			return this.dialog.role === UserRole.guest;
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		containerClasses(): string[]
		{
			const alignment = this.$store.getters['application/settings/get'](Settings.appearance.alignment);

			return [`--${alignment}-align`];
		},
		backgroundStyle(): BackgroundStyle
		{
			return ThemeManager.getCurrentBackgroundStyle();
		},
		dialogContainerStyle(): Object
		{
			const TEXTAREA_PLACEHOLDER_HEIGHT = 50;

			let textareaHeight = this.textareaHeight;
			if (!this.canPost)
			{
				textareaHeight = TEXTAREA_PLACEHOLDER_HEIGHT;
			}

			return {
				height: `calc(100% - ${CHAT_HEADER_HEIGHT}px - ${textareaHeight}px)`,
			};
		},
		dropAreaStyles(): {[top: string]: string}
		{
			const PINNED_MESSAGES_HEIGHT = 53;
			const DROP_AREA_OFFSET = 16 + CHAT_HEADER_HEIGHT;

			const dropAreaTopOffset = this.hasPinnedMessages
				? PINNED_MESSAGES_HEIGHT + DROP_AREA_OFFSET
				: DROP_AREA_OFFSET
			;

			return {
				top: `${dropAreaTopOffset}px`,
			};
		},
	},
	watch:
	{
		entityId(newValue, oldValue)
		{
			Logger.warn(`ChatContent: switching from ${oldValue || 'empty'} to ${newValue}`);
			this.onChatChange();
		},
	},
	created()
	{
		if (this.entityId)
		{
			this.onChatChange();
		}

		this.initTextareaResizeManager();
	},
	methods:
	{
		async onChatChange()
		{
			if (this.entityId === '')
			{
				return;
			}

			if (Utils.dialog.isExternalId(this.entityId))
			{
				const realDialogId = await this.getChatService().prepareDialogId(this.entityId);

				void LayoutManager.getInstance().setLayout({
					name: Layout.chat.name,
					entityId: realDialogId,
					contextId: this.layout.contextId,
				});

				return;
			}

			if (this.dialog.inited)
			{
				Logger.warn(`ChatContent: chat ${this.entityId} is already loaded`);
				if (this.isUser)
				{
					const userId = parseInt(this.dialog.dialogId, 10);
					void this.getUserService().updateLastActivityDate(userId);
				}

				return;
			}

			if (this.dialog.loading)
			{
				Logger.warn(`ChatContent: chat ${this.entityId} is loading`);

				return;
			}

			if (this.layout.contextId)
			{
				await this.loadChatWithContext();

				return;
			}

			await this.loadChat();
		},
		loadChatWithContext(): Promise
		{
			Logger.warn(`ChatContent: loading chat ${this.entityId} with context - ${this.layout.contextId}`);

			return this.getChatService().loadChatWithContext(this.entityId, this.layout.contextId).then(() => {
				Logger.warn(`ChatContent: chat ${this.entityId} is loaded with context of ${this.layout.contextId}`);
			}).catch((error) => {
				this.handleChatLoadError(error);
				Logger.error(error);
				Messenger.openChat();
			});
		},
		loadChat(): Promise
		{
			Logger.warn(`ChatContent: loading chat ${this.entityId}`);

			return this.getChatService().loadChatWithMessages(this.entityId).then(() => {
				Logger.warn(`ChatContent: chat ${this.entityId} is loaded`);
			}).catch((error) => {
				this.handleChatLoadError(error);
				Logger.error(error);
				Messenger.openChat();
			});
		},
		handleChatLoadError(error: Error[]): void
		{
			const [firstError] = error;
			if (firstError.code === 'ACCESS_DENIED')
			{
				this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR'));
			}
			else if (firstError.code === 'MESSAGE_NOT_FOUND')
			{
				this.showNotification(this.loc('IM_CONTENT_CHAT_CONTEXT_MESSAGE_NOT_FOUND'));
			}
		},
		initTextareaResizeManager()
		{
			this.textareaResizeManager = new ResizeManager();
			this.textareaResizeManager.subscribe(
				ResizeManager.events.onHeightChange,
				(event: BaseEvent<{newHeight: number}>) => {
					const { newHeight } = event.getData();
					this.textareaHeight = newHeight;
				},
			);
		},
		showNotification(text: string)
		{
			BX.UI.Notification.Center.notify({ content: text });
		},
		getUserService(): UserService
		{
			if (!this.userService)
			{
				this.userService = new UserService();
			}

			return this.userService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onDragEnter(event: DragEvent)
		{
			void hasDataTransferOnlyFiles(event.dataTransfer, false).then((success: boolean): void => {
				if (!success)
				{
					return;
				}
				this.lastDropAreaEnterTarget = event.target;
				this.showDropArea = true;
			});
		},
		onDragLeave(event: DragEvent)
		{
			if (this.lastDropAreaEnterTarget === event.target)
			{
				this.showDropArea = false;
			}
		},
		onDrop(event: DragEvent)
		{
			void getFilesFromDataTransfer(event.dataTransfer).then((files: File[]): void => {
				this.getUploadingService().addFilesFromInput(files, this.entityId);
			});
			this.showDropArea = false;
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		getUploadingService(): UploadingService
		{
			if (!this.uploadingService)
			{
				this.uploadingService = UploadingService.getInstance();
			}

			return this.uploadingService;
		},
		onChangeSidebarPanel({ panel }: {panel: $Keys<typeof SidebarDetailBlock>})
		{
			this.currentSidebarPanel = panel;
		},
	},
	template: `
		<div class="bx-im-content-chat__scope bx-im-content-chat__container" :class="containerClasses" :style="backgroundStyle">
			<div 
				class="bx-im-content-chat__content"
				@drop.prevent="onDrop"
				@dragleave.stop.prevent="onDragLeave"
				@dragenter.stop.prevent="onDragEnter"
				@dragover.prevent
			>
				<template v-if="entityId">
					<ChatHeader :dialogId="entityId" :key="entityId" :currentSidebarPanel="currentSidebarPanel" />
					<div :style="dialogContainerStyle" class="bx-im-content-chat__dialog_container">
						<div class="bx-im-content-chat__dialog_content">
							<ChatDialog :dialogId="entityId" :key="entityId" :textareaHeight="textareaHeight" />
						</div>
					</div>
					<!-- Textarea -->
					<div v-if="canPost" v-textarea-observer class="bx-im-content-chat__textarea_container">
						<ChatTextarea :dialogId="entityId" :key="entityId" />
					</div>
					<JoinPanel v-else-if="isGuest" :dialogId="entityId" />
					<MutePanel v-else :dialogId="entityId" />
					<!-- End textarea -->
					<DropArea :show="showDropArea" :style="dropAreaStyles" />
				</template>
				<EmptyState v-else />
			</div>
			<ChatSidebar 
				v-if="entityId.length > 0" 
				:originDialogId="entityId" 
				@changePanel="onChangeSidebarPanel" 
			/>
		</div>
	`,
};
