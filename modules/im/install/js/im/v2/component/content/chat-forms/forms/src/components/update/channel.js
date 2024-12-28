import 'ui.notification';
import { EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';

import { Messenger } from 'im.public';
import { Analytics } from 'im.v2.lib.analytics';
import { ChatService } from 'im.v2.provider.service';
import { EmptyAvatarType } from 'im.v2.component.elements';
import { UserRole, PopupType, ChatType, EventType, SidebarDetailBlock } from 'im.v2.const';
import { showExitUpdateChatConfirm } from 'im.v2.lib.confirm';
import {
	TitleInput,
	ChatAvatar,
	ChatMembersSelector,
	ButtonPanel,
	TextareaInput,
	CreateChatHeading,
	SettingsSection,
	RightsSection,
	AppearanceSection,
	PrivacySection,
} from 'im.v2.component.content.chat-forms.elements';

import { ChatMemberDiffManager } from '../../classes/chat-member-diff-manager';
import { getCollapsedUsersElement, type TagSelectorElement } from '../../helpers/get-collapsed-users-element';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

type UserRoleItem = $Keys<typeof UserRole>;

// @vue/component
export const ChannelUpdating = {
	name: 'ChannelUpdating',
	components: {
		TitleInput,
		ChatAvatar,
		ChatMembersSelector,
		SettingsSection,
		RightsSection,
		AppearanceSection,
		PrivacySection,
		ButtonPanel,
		TextareaInput,
		CreateChatHeading,
	},
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: true,
			isUpdating: false,
			areUsersCollapsed: false,
			collapsedUsersCount: 0,
			avatarFile: null,
			avatarUrl: '',
			chatTitle: '',
			chatMembers: [],
			settings: {
				isAvailableInSearch: true,
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
		ChatType: () => ChatType,
		EmptyAvatarType: () => EmptyAvatarType,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		collapsedUsers(): TagSelectorElement[]
		{
			if (!this.areUsersCollapsed)
			{
				return [];
			}

			const title = this.loc('IM_UPDATE_CHANNEL_COLLAPSED_ELEMENT', {
				'#COUNT#': this.collapsedUsersCount,
			});

			const element = getCollapsedUsersElement({
				title,
				onclick: this.onCollapsedUsersClick,
			});

			return [element];
		},
		changedChatType(): $Values<typeof ChatType>
		{
			return this.settings.isAvailableInSearch ? ChatType.openChannel : ChatType.channel;
		},
	},
	async created()
	{
		await this.fillForm();
		this.memberDiffManager = new ChatMemberDiffManager();
		this.memberDiffManager.setInitialChatMembers(this.chatMembers);
		this.memberDiffManager.setInitialManagers(this.rights.managerIds);

		this.isLoading = false;
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
		onChatTypeChange(isAvailableInSearch: boolean)
		{
			this.settings.isAvailableInSearch = isAvailableInSearch;
		},
		async onUpdateClick(): Promise
		{
			Analytics.getInstance().chatEdit.onSubmitForm(this.dialogId);
			Analytics.getInstance().ignoreNextChatOpen(this.dialogId);

			this.isUpdating = true;

			await this.getChatService().updateChat(this.chatId, {
				title: this.chatTitle,
				avatar: this.avatarFile,
				type: this.changedChatType,
				addedMemberEntities: this.memberDiffManager.getAddedMemberEntities(this.chatMembers),
				deletedMemberEntities: this.memberDiffManager.getDeletedMemberEntities(this.chatMembers),
				addedManagers: this.memberDiffManager.getAddedManagers(this.rights.managerIds),
				deletedManagers: this.memberDiffManager.getDeletedManagers(this.rights.managerIds),
				ownerId: this.rights.ownerId,
				isAvailableInSearch: this.settings.isAvailableInSearch,
				description: this.settings.description,
				manageUsersAdd: this.rights.manageUsersAdd,
				manageUsersDelete: this.rights.manageUsersDelete,
				manageUi: this.rights.manageUi,
				manageMessages: this.rights.manageMessages,
			}).catch(() => {
				this.isUpdating = false;
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_UPDATE_CHAT_ERROR'),
				});
			});

			this.isUpdating = false;

			return Messenger.openChat(this.dialogId);
		},
		onCancelClick()
		{
			Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
			void Messenger.openChat(this.dialogId);
		},
		onAvatarChange(newAvatarFile: File)
		{
			this.avatarFile = newAvatarFile;
			this.avatarUrl = '';
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
		async fillForm()
		{
			this.chatTitle = this.dialog.name;
			this.avatarUrl = this.dialog.avatar;
			this.settings.description = this.dialog.description;
			this.chatMembers = await this.getMemberEntities();
			this.rights.ownerId = this.dialog.ownerId;
			this.rights.manageMessages = this.dialog.permissions.manageMessages;
			this.rights.managerIds = this.dialog.managerList;
			this.rights.manageUi = this.dialog.permissions.manageUi;
			this.rights.manageUsersAdd = this.dialog.permissions.manageUsersAdd;
			this.rights.manageUsersDelete = this.dialog.permissions.manageUsersDelete;
			this.settings.isAvailableInSearch = this.dialog.type === ChatType.openChannel;
		},
		async getMemberEntities(): Promise<[string, number][]>
		{
			const {
				memberEntities,
				areUsersCollapsed,
				userCount,
			} = await this.getChatService().getMemberEntities(this.chatId);

			if (areUsersCollapsed)
			{
				this.areUsersCollapsed = true;
				this.collapsedUsersCount = userCount;

				return memberEntities;
			}

			return memberEntities;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		async onCollapsedUsersClick()
		{
			const confirmResult = await showExitUpdateChatConfirm(this.dialogId);
			if (!confirmResult)
			{
				return;
			}

			await this.onUpdateClick();

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.members,
				dialogId: this.dialogId,
			});
		},
	},
	template: `
		<div v-if="isLoading" class="bx-im-content-chat-forms__skeleton"></div>
		<div v-else class="bx-im-content-chat-forms__content --channel" @scroll="onScroll">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar
					:avatarFile="avatarFile"
					:chatTitle="chatTitle"
					:existingAvatarUrl="avatarUrl"
					:type="EmptyAvatarType.squared"
					@avatarChange="onAvatarChange"
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_CHANNEL_TITLE_PLACEHOLDER_V2')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_CHANNEL_DESCRIPTION_TITLE')" />
			<div class="bx-im-content-chat-forms__description_container">
				<TextareaInput
					:value="settings.description"
					:placeholder="loc('IM_CREATE_CHANNEL_DESCRIPTION_PLACEHOLDER_V3')"
					:border="false"
					@input="onDescriptionChange"
				/>
			</div>
			<PrivacySection
				:isAvailableInSearch="settings.isAvailableInSearch"
				@chatTypeChange="onChatTypeChange"
			/>
			<CreateChatHeading
				:text="loc('IM_CREATE_CHANNEL_MEMBERS_TITLE')"
				:hintText="loc('IM_CREATE_CHANNEL_MEMBERS_HINT')"
			/>
			<div class="bx-im-content-chat-forms__members_container">
				<ChatMembersSelector
					:customElements="collapsedUsers"
					:chatMembers="chatMembers" 
					@membersChange="onMembersChange" 
				/>
			</div>
			<RightsSection
				:chatType="ChatType.channel"
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
			:isCreating="isLoading || isUpdating"
			:createButtonTitle="loc('IM_UPDATE_CONFIRM')"
			@create="onUpdateClick"
			@cancel="onCancelClick"
		/>
	`,
};
