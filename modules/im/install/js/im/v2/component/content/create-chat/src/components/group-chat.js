import { BaseEvent, EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChatService } from 'im.v2.provider.service';
import { UserRole, PopupType, DialogType, EventType, Layout, type OnLayoutChangeEvent } from 'im.v2.const';

import { TitleInput } from './elements/title-input';
import { ChatAvatar } from './elements/chat-avatar';
import { ChatMembersSelector } from './elements/chat-members';
import { ButtonPanel } from './elements/button-panel';
import { SettingsSection } from './sections/settings/settings-section';
import { RightsSection } from './sections/rights/rights-section';
import { AppearanceSection } from './sections/appearance/appearance-section';

import 'ui.notification';
import '../css/create-chat-content.css';

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
	},
	data(): JsonObject
	{
		return {
			isCreating: false,
			avatarFile: null,
			chatTitle: '',
			chatMembers: [],
			settings: {
				isAvailableInSearch: false,
				description: '',
			},
			rights: {
				ownerId: 0,
				managerIds: [],
				manageUsers: '',
				manageSettings: '',
				manageUi: '',
				canPost: '',
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
		CreateChatManager.getInstance().setChatType(DialogType.chat);
		CreateChatManager.getInstance().setCreationStatus(true);
		CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
	},
	beforeUnmount()
	{
		if (this.exitByCancel || this.exitByChatTypeSwitch)
		{
			return;
		}
		this.saveFields();
	},
	methods:
	{
		onMembersChange(currentTags: number[])
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
		onManageUsersChange(newValue: UserRoleItem)
		{
			this.rights.manageUsers = newValue;
		},
		onManageUiChange(newValue: UserRoleItem)
		{
			this.rights.manageUi = newValue;
		},
		onCanPostChange(newValue: UserRoleItem)
		{
			this.rights.canPost = newValue;
		},
		async onCreateClick()
		{
			this.isCreating = true;

			const newDialogId = await this.getChatService().createChat({
				title: this.chatTitle,
				avatar: this.avatarFile,
				members: this.chatMembers,
				ownerId: this.rights.ownerId,
				managers: this.rights.managerIds,
				isAvailableInSearch: this.settings.isAvailableInSearch,
				description: this.settings.description,
				manageUsers: this.rights.manageUsers,
				manageUi: this.rights.manageUi,
				manageSettings: this.rights.manageSettings,
				canPost: this.rights.canPost,
			}).catch(() => {
				this.isCreating = false;
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_CREATE_CHAT_ERROR'),
				});
			});

			this.isCreating = false;
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
			MenuManager.getMenuById(PopupType.createChatManageUsersMenu)?.close();
			MenuManager.getMenuById(PopupType.createChatManageUiMenu)?.close();
			MenuManager.getMenuById(PopupType.createChatCanPostMenu)?.close();
		},
		onLayoutChange(event: BaseEvent<OnLayoutChangeEvent>)
		{
			const { to } = event.getData();
			if (to.name === Layout.createChat.name && to.entityId !== DialogType.chat)
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
				manageUsers,
				manageUi,
				manageSettings,
			} = PermissionManager.getInstance().getDefaultRolesForActionGroups();

			this.rights.manageUsers = manageUsers;
			this.rights.manageUi = manageUi;
			this.rights.manageSettings = manageSettings;
			this.rights.canPost = UserRole.member;
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
		<div class="bx-im-content-create-chat__content" @scroll="onScroll">
			<div class="bx-im-content-create-chat__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHAT_TITLE_PLACEHOLDER')" />
			</div>
			<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			<SettingsSection
				:isAvailableInSearch="settings.isAvailableInSearch"
				:description="settings.description"
				@chatTypeChange="onChatTypeChange"
				@descriptionChange="onDescriptionChange"
			/>
			<RightsSection
				:ownerId="rights.ownerId"
				:managerIds="rights.managerIds"
				:manageUsers="rights.manageUsers"
				:manageUi="rights.manageUi"
				:manageSettings="rights.manageSettings"
				:canPost="rights.canPost"
				@ownerChange="onOwnerChange"
				@managersChange="onManagersChange"
				@manageUsersChange="onManageUsersChange"
				@manageUiChange="onManageUiChange"
				@canPostChange="onCanPostChange"
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
