import 'ui.notification';

import { BaseEvent, EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChatService } from 'im.v2.provider.service';
import { UserRole, PopupType, ChatType, EventType, Layout, type OnLayoutChangeEvent } from 'im.v2.const';
import {
	TitleInput,
	ChatAvatar,
	ChatMembersSelector,
	ButtonPanel,
	CreateChatHeading,
	SettingsSection,
	RightsSection,
	AppearanceSection,
} from 'im.v2.component.content.chat-forms.elements';

import type { JsonObject } from 'main.core';

type UserRoleItem = $Keys<typeof UserRole>;

// @vue/component
export const GroupChatCreation = {
	name: 'GroupChatCreation',
	components: {
		TitleInput,
		ChatAvatar,
		ChatMembersSelector,
		SettingsSection,
		RightsSection,
		AppearanceSection,
		ButtonPanel,
		CreateChatHeading,
	},
	data(): JsonObject
	{
		return {
			isCreating: false,
			avatarFile: null,
			chatTitle: '',
			chatMembers: [['user', Core.getUserId()]],
			settings: {
				isAvailableInSearch: false,
				description: '',
			},
			rights: {
				ownerId: 0,
				managerIds: [],
				manageUsersAdd: '',
				manageUsersDelete: '',
				manageUi: '',
				manageMessages: '',
			},
		};
	},
	watch:
	{
		chatTitle(newValue)
		{
			CreateChatManager.getInstance().setChatTitle(newValue);
		},
	},
	created()
	{
		EventEmitter.subscribe(EventType.layout.onLayoutChange, this.onLayoutChange);

		this.rights.ownerId = Core.getUserId();
		this.initDefaultRolesForRights();

		this.restoreFields();
		CreateChatManager.getInstance().setChatType(ChatType.chat);
		CreateChatManager.getInstance().setCreationStatus(true);
		CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	},
	beforeUnmount()
	{
		if (this.exitByCancel || this.exitByChatTypeSwitch || this.exitByCreation)
		{
			return;
		}
		this.saveFields();
	},
	methods:
	{
		onMembersChange(currentTags: [string, number | string][])
		{
			this.chatMembers = currentTags;
		},
		onOwnerChange(ownerId: number)
		{
			this.rights.ownerId = ownerId;
		},
		onManagersChange(managerIds: number[])
		{
			this.rights.managerIds = managerIds;
		},
		onChatTypeChange(isAvailableInSearch: boolean)
		{
			this.settings.isAvailableInSearch = isAvailableInSearch;
		},
		onDescriptionChange(description: string)
		{
			this.settings.description = description;
		},
		onManageUsersAddChange(newValue: UserRoleItem)
		{
			this.rights.manageUsersAdd = newValue;
		},
		onManageUsersDeleteChange(newValue: UserRoleItem)
		{
			this.rights.manageUsersDelete = newValue;
		},
		onManageUiChange(newValue: UserRoleItem)
		{
			this.rights.manageUi = newValue;
		},
		onManageMessagesChange(newValue: UserRoleItem)
		{
			this.rights.manageMessages = newValue;
		},
		async onCreateClick()
		{
			this.isCreating = true;

			const { newDialogId } = await this.getChatService().createChat({
				title: this.chatTitle,
				avatar: this.avatarFile,
				memberEntities: this.chatMembers,
				ownerId: this.rights.ownerId,
				managers: this.rights.managerIds,
				isAvailableInSearch: this.settings.isAvailableInSearch,
				description: this.settings.description,
				manageUsersAdd: this.rights.manageUsersAdd,
				manageUsersDelete: this.rights.manageUsersDelete,
				manageUi: this.rights.manageUi,
				manageMessages: this.rights.manageMessages,
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
		onLayoutChange(event: BaseEvent<OnLayoutChangeEvent>)
		{
			const { to } = event.getData();
			if (to.name === Layout.createChat.name && to.entityId !== ChatType.chat)
			{
				this.exitByChatTypeSwitch = true;
			}
		},
		saveFields()
		{
			CreateChatManager.getInstance().saveFields({
				chatTitle: this.chatTitle,
				avatarFile: this.avatarFile,
				chatMembers: this.chatMembers,
				settings: this.settings,
				rights: this.rights,
			});
		},
		restoreFields()
		{
			const savedFields = CreateChatManager.getInstance().getFields();
			if (!savedFields)
			{
				return;
			}

			const { chatTitle, avatarFile, chatMembers, settings, rights } = savedFields;
			this.chatTitle = chatTitle;
			this.avatarFile = avatarFile;
			this.chatMembers = chatMembers;
			this.settings = settings;
			this.rights = rights;
		},
		initDefaultRolesForRights()
		{
			const {
				manageUsersAdd,
				manageUsersDelete,
				manageUi,
				manageMessages,
			} = PermissionManager.getInstance().getDefaultRolesForActionGroups();

			this.rights.manageUsersAdd = manageUsersAdd;
			this.rights.manageUsersDelete = manageUsersDelete;
			this.rights.manageUi = manageUi;
			this.rights.manageMessages = manageMessages;
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-chat-forms__content --chat" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHAT_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHAT_MEMBERS_TITLE')" />
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			</div>
			<SettingsSection
				:isAvailableInSearch="settings.isAvailableInSearch"
				:description="settings.description"
				@chatTypeChange="onChatTypeChange"
				@descriptionChange="onDescriptionChange"
			/>
			<RightsSection
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsersAdd="rights.manageUsersAdd"
				:manageUsersDelete="rights.manageUsersDelete"
				:manageUi="rights.manageUi"
				:manageMessages="rights.manageMessages"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersAddChange="onManageUsersAddChange"
				@manageUsersDeleteChange="onManageUsersDeleteChange"
				@manageUiChange="onManageUiChange"
				@manageMessagesChange="onManageMessagesChange"
			/> 
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_CHAT_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`,
};
