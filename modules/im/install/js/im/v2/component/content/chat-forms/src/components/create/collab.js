import { Messenger } from 'im.public';
import type { OnLayoutChangeEvent } from 'im.v2.const';
import { ChatService } from 'im.v2.provider.service';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { ChatType, EventType, Layout, PopupType } from 'im.v2.const';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { MenuManager } from 'main.popup';

import { TitleInput } from '../elements/title-input';
import { ChatAvatar } from '../elements/chat-avatar';
import { CreateChatHeading } from '../elements/heading';
import { ChatMembersSelector } from '../elements/chat-members';
import { ButtonPanel } from '../elements/button-panel';

import type { JsonObject } from 'main.core';

export const CollabCreation = {
	name: 'CollabCreation',
	components: { TitleInput, ChatAvatar, CreateChatHeading, ChatMembersSelector, ButtonPanel },
	data(): JsonObject
	{
		return {
			isCreating: false,
			avatarFile: null,
			chatTitle: '',
			chatMembers: [],
		};
	},
	watch:
	{
		chatTitle(newValue: string): void
		{
			CreateChatManager.getInstance().setChatTitle(newValue);
		},
	},
	created()
	{
		EventEmitter.subscribe(EventType.layout.onLayoutChange, this.onLayoutChange);

		this.restoreFields();
		CreateChatManager.getInstance().setChatType(ChatType.collab);
		CreateChatManager.getInstance().setCreationStatus(true);
		CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.layout.onLayoutChange, this.onLayoutChange);

		if (this.exitByCancel || this.exitByChatTypeSwitch || this.exitByCreation)
		{
			return;
		}
		this.saveFields();
	},
	methods:
	{
		restoreFields(): void
		{
			const savedFields = CreateChatManager.getInstance().getFields();
			if (!savedFields)
			{
				return;
			}

			const { chatTitle, avatarFile, chatMembers } = savedFields;
			this.chatTitle = chatTitle;
			this.avatarFile = avatarFile;
			this.chatMembers = chatMembers;
		},
		saveFields()
		{
			CreateChatManager.getInstance().saveFields({
				chatTitle: this.chatTitle,
				avatarFile: this.avatarFile,
				chatMembers: this.chatMembers,
			});
		},
		onLayoutChange(event: BaseEvent<OnLayoutChangeEvent>): void
		{
			const { to } = event.getData();
			if (to.name === Layout.createChat.name && to.entityId !== ChatType.collab)
			{
				this.exitByChatTypeSwitch = true;
			}
		},
		onMembersChange(currentTags: [string, number | string][])
		{
			this.chatMembers = currentTags;
		},
		async onCreateClick(): void
		{
			this.isCreating = true;

			const { newDialogId } = await this.getChatService().createCollab({
				title: this.chatTitle,
				avatar: this.avatarFile,
				memberEntities: this.chatMembers,
			}).catch(() => {
				this.isCreating = false;
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_CREATE_CHAT_ERROR'),
				});
			});

			this.isCreating = false;
			this.exitByCreation = true;
			CreateChatManager.getInstance().setCreationStatus(false);
			void Messenger.openChat(newDialogId);
		},
		onCancelClick()
		{
			this.exitByCancel = true;
			CreateChatManager.getInstance().setCreationStatus(false);
			Messenger.openChat();
		},
		onAvatarChange(newAvatarFile: File)
		{
			this.avatarFile = newAvatarFile;
			CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
		},
		onScroll()
		{
			MenuManager.getMenuById(PopupType.createChatManageUsersAddMenu)?.close();
			MenuManager.getMenuById(PopupType.createChatManageUsersDeleteMenu)?.close();
			MenuManager.getMenuById(PopupType.createChatManageUiMenu)?.close();
			MenuManager.getMenuById(PopupType.createChatManageMessagesMenu)?.close();
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
		<div class="bx-im-content-chat-forms__content --collab" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" :squared="true" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_COLLAB_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_COLLAB_MEMBERS_TITLE')" />
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			</div>
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_COLLAB_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`,
};
