import { BaseEvent, EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChatService } from 'im.v2.provider.service';
import { UserRole, PopupType, DialogType, EventType, Layout } from 'im.v2.const';

import { TitleInput } from './elements/title-input';
import { ChatAvatar } from './elements/chat-avatar';
import { ChatMembersSelector } from './elements/chat-members';
import { ButtonPanel } from './elements/button-panel';
import { SettingsSection } from './sections/settings/settings-section';
import { RightsSection } from './sections/rights/rights-section';
import { AppearanceSection } from './sections/appearance/appearance-section';
import { ConferenceSection } from './sections/conference/conference-section';

import 'ui.notification';
import '../css/create-chat-content.css';

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
	},
	data(): JsonObject
	{
		return {
			isCreating: false,
			avatarFile: null,
			chatTitle: '',
			chatMembers: [],
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
		CreateChatManager.getInstance().setChatType(DialogType.videoconf);
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

			const newDialogId = await this.getChatService().createChat({
				type: DialogType.videoconf,
				title: this.chatTitle,
				avatar: this.avatarFile,
				members: this.chatMembers,
				ownerId: this.rights.ownerId,
				managers: this.rights.managerIds,
				description: this.settings.description,
				manageUsers: this.rights.manageUsers,
				manageUi: this.rights.manageUi,
				manageSettings: this.rights.manageSettings,
				canPost: this.rights.canPost,
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
			MenuManager.getMenuById(PopupType.createChatManageUsersMenu)?.close();
			MenuManager.getMenuById(PopupType.createChatManageUiMenu)?.close();
			MenuManager.getMenuById(PopupType.createChatCanPostMenu)?.close();
		},
		onLayoutChange(event: BaseEvent<OnLayoutChangeEvent>)
		{
			const { to } = event.getData();
			if (to.name === Layout.createChat.name && to.entityId !== DialogType.videoconf)
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
				manageUsers,
				manageUi,
				manageSettings,
			} = PermissionManager.getInstance().getDefaultRolesForActionGroups();

			this.rights.manageUsers = manageUsers;
			this.rights.manageUi = manageUi;
			this.rights.manageSettings = manageSettings;
			this.rights.canPost = UserRole.member;
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
		<div class="bx-im-content-create-chat__content" @scroll="onScroll">
			<div class="bx-im-content-create-chat__header">
				<ChatAvatar :avatarFile="avatarFile" :chatTitle="chatTitle" @avatarChange="onAvatarChange" />
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CONFERENCE_TITLE_PLACEHOLDER')" />
			</div>
			<ChatMembersSelector :chatMembers="chatMembers" @membersChange="onMembersChange" />
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
			:createButtonTitle="loc('IM_CREATE_CONFERENCE_CONFIRM')"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`,
};
