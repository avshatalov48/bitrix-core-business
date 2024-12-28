import 'ui.notification';

import { BaseEvent, EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { EmptyAvatarType } from 'im.v2.component.elements';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChatService } from 'im.v2.provider.service';
import { UserRole, PopupType, ChatType, EventType, Layout } from 'im.v2.const';
import {
	TitleInput,
	ChatAvatar,
	ChatMembersSelector,
	ButtonPanel,
	CreateChatHeading,
	SettingsSection,
	RightsSection,
	AppearanceSection,
	ConferenceSection,
} from 'im.v2.component.content.chat-forms.elements';

import type { JsonObject } from 'main.core';
import type { OnLayoutChangeEvent } from 'im.v2.const';

type UserRoleItem = $Keys<typeof UserRole>;

// @vue/component
export const ConferenceCreation = {
	name: 'ConferenceCreation',
	components: {
		TitleInput,
		ChatAvatar,
		ChatMembersSelector,
		SettingsSection,
		RightsSection,
		AppearanceSection,
		ConferenceSection,
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
			conference: {
				passwordNeeded: false,
				password: '',
			},
			settings: {
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
	computed:
	{
		EmptyAvatarType: () => EmptyAvatarType,
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
		CreateChatManager.getInstance().setChatType(ChatType.videoconf);
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
		onAvatarChange(newAvatarFile: File)
		{
			this.avatarFile = newAvatarFile;
			CreateChatManager.getInstance().setChatAvatar(this.avatarFile);
		},
		onPasswordNeededChange(passwordNeeded: boolean)
		{
			this.conference.passwordNeeded = passwordNeeded;
		},
		onPasswordChange(newPassword: string)
		{
			this.conference.password = newPassword;
		},
		async onCreateClick()
		{
			if (!this.checkPassword())
			{
				this.showPasswordError();

				return;
			}

			this.isCreating = true;

			const { newDialogId } = await this.getChatService().createChat({
				entityType: ChatType.videoconf,
				title: this.chatTitle,
				avatar: this.avatarFile,
				memberEntities: this.chatMembers,
				ownerId: this.rights.ownerId,
				managers: this.rights.managerIds,
				description: this.settings.description,
				manageUsersAdd: this.rights.manageUsersAdd,
				manageUsersDelete: this.rights.manageUsersDelete,
				manageUi: this.rights.manageUi,
				manageMessages: this.rights.manageMessages,
				conferencePassword: this.conference.passwordNeeded ? this.conference.password : '',
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
			if (to.name === Layout.createChat.name && to.entityId !== ChatType.videoconf)
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
				conference: this.conference,
			});
		},
		restoreFields()
		{
			const savedFields = CreateChatManager.getInstance().getFields();
			if (!savedFields)
			{
				return;
			}

			const { chatTitle, avatarFile, chatMembers, conference, settings, rights } = savedFields;
			this.chatTitle = chatTitle;
			this.avatarFile = avatarFile;
			this.chatMembers = chatMembers;
			this.conference = conference;
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
		checkPassword(): boolean
		{
			const PASSWORD_MIN_LENGTH = 3;
			if (!this.conference.passwordNeeded)
			{
				return true;
			}
			const password = this.conference.password.trim();

			return password !== '' && password.length >= PASSWORD_MIN_LENGTH;
		},
		showPasswordError()
		{
			BX.UI.Notification.Center.notify({
				content: this.loc('IM_CREATE_CHAT_CONFERENCE_PASSWORD_ERROR'),
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-chat-forms__content --conference" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar 
					:avatarFile="avatarFile" 
					:chatTitle="chatTitle"
					:type="EmptyAvatarType.squared"	
					@avatarChange="onAvatarChange" 
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CONFERENCE_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHAT_MEMBERS_TITLE')" />
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
			</div>
			<ConferenceSection
				:passwordNeeded="conference.passwordNeeded"
				:password="conference.password"
				@passwordNeededChange="onPasswordNeededChange"
				@passwordChange="onPasswordChange"
			/>
			<SettingsSection
				:withSearchOption="false"
				:description="settings.description"
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
			:createButtonTitle="loc('IM_CREATE_CONFERENCE_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`,
};
