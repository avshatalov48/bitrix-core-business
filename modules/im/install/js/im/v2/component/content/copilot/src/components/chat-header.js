import 'ui.notification';
import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { ChatService } from 'im.v2.provider.service';
import { EditableChatTitle, AvatarSize, ChatAvatar } from 'im.v2.component.elements';
import { ChatHeader } from 'im.v2.component.content.elements';
import { EventType, SidebarDetailBlock } from 'im.v2.const';

import { AddToChatButton } from './add-to-chat/add-to-chat-button';

import '../css/chat-header.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CopilotChatHeader = {
	name: 'CopilotChatHeader',
	components: { ChatHeader, EditableChatTitle, ChatAvatar, AddToChatButton },
	inject: ['currentSidebarPanel'],
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): JsonObject
	{
		return {
			buttonPanelReady: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isInited(): boolean
		{
			return this.dialog.inited;
		},
		isGroupCopilotChat(): boolean
		{
			return this.dialog.userCounter > 2;
		},
		copilotRole(): string
		{
			const role = this.$store.getters['copilot/chats/getRole'](this.dialogId);

			return role?.name ?? '';
		},
		formattedUserCounter(): string
		{
			return Loc.getMessagePlural('IM_CONTENT_COPILOT_HEADER_USER_COUNT', this.dialog.userCounter, {
				'#COUNT#': this.dialog.userCounter,
			});
		},
	},
	methods:
	{
		onNewTitleSubmit(newTitle: string)
		{
			this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_CONTENT_CHAT_HEADER_RENAME_ERROR'),
				});
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
		onButtonPanelReady()
		{
			this.buttonPanelReady = true;
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
		<ChatHeader
			:dialogId="dialogId"
			:withSearchButton="false"
			class="bx-im-copilot-header__container"
			@buttonPanelReady="onButtonPanelReady"
		>
			<template #left>
				<div class="bx-im-copilot-header__avatar">
					<ChatAvatar
						:avatarDialogId="dialogId"
						:contextDialogId="dialogId"
						:withSpecialTypes="false"
						:size="AvatarSize.L"
					/>
				</div>
				<div class="bx-im-copilot-header__info">
					<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit" />
					<div
						v-if="isGroupCopilotChat"
						:title="loc('IM_CONTENT_COPILOT_HEADER_OPEN_MEMBERS_TITLE')"
						@click="onMembersClick"
						class="bx-im-copilot-header__subtitle --click"
					>
						{{ formattedUserCounter }}
					</div>
					<div v-else class="bx-im-copilot-header__subtitle">
						{{ copilotRole }}
					</div>
				</div>
			</template>
			<template v-if="buttonPanelReady" #add-to-chat-button>
				<AddToChatButton :dialogId="dialogId" />
			</template>
		</ChatHeader>
	`,
};
