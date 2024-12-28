import 'ui.notification';
import { Loc } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Messenger } from 'im.public';
import { Analytics } from 'im.v2.lib.analytics';
import { ChatService } from 'im.v2.provider.service';
import { EmptyAvatarType } from 'im.v2.component.elements';
import { Color } from 'im.v2.const';
import {
	TitleInput,
	ChatAvatar,
	ButtonPanel,
	CreateChatHeading,
	TextareaInput,
} from 'im.v2.component.content.chat-forms.elements';

import { RightsSection } from '../create/collab/components/rights-section';
import { ChatMemberDiffManager } from '../../classes/chat-member-diff-manager';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelCollabInfo } from 'im.v2.model';
import type { CustomColorScheme } from 'im.v2.component.elements';

import type { AccessRightsFormResult } from '../create/collab/components/rights-section';

const UpdateCollabErrorCode = {
	emptyName: 'name',
	duplicateName: 'ERROR_GROUP_NAME_EXISTS',
};

const NotificationTextByErrorCode = {
	[UpdateCollabErrorCode.emptyName]: Loc.getMessage('IM_CREATE_COLLAB_ERROR_EMPTY_NAME'),
	[UpdateCollabErrorCode.duplicateName]: Loc.getMessage('IM_CREATE_COLLAB_ERROR_DUPLICATE_NAME'),
	default: Loc.getMessage('IM_UPDATE_CHAT_ERROR'),
};

// @vue/component
export const CollabUpdating = {
	name: 'CollabUpdating',
	components:
	{
		TitleInput,
		ChatAvatar,
		TextareaInput,
		ButtonPanel,
		CreateChatHeading,
		RightsSection,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			isUpdating: false,
			avatarFile: null,
			avatarUrl: '',
			chatTitle: '',
			description: '',
			groupSettings: {
				ownerId: Core.getUserId(),
				moderatorMembers: [],
				options: null,
				permissions: null,
				isModified: false,
			},
		};
	},
	computed:
	{
		EmptyAvatarType: () => EmptyAvatarType,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		collabId(): number
		{
			const { collabId }: ImModelCollabInfo = this.$store.getters['chats/collabs/getByChatId'](this.chatId);

			return collabId;
		},
		updateButtonColorScheme(): CustomColorScheme
		{
			return {
				borderColor: Color.transparent,
				backgroundColor: Color.collab60,
				iconColor: Color.white,
				textColor: Color.white,
				hoverColor: Color.collab50,
			};
		},
	},
	created()
	{
		this.fillForm();
		this.initDiffManager();
	},
	methods:
	{
		initDiffManager()
		{
			this.memberDiffManager = new ChatMemberDiffManager();
			const managersWithoutOwner = this.dialog.managerList.filter((managerId) => {
				return managerId !== Core.getUserId();
			});
			this.memberDiffManager.setInitialManagers(managersWithoutOwner);
		},
		async fillForm()
		{
			this.chatTitle = this.dialog.name;
			this.avatarUrl = this.dialog.avatar;
			this.description = this.dialog.description;
		},
		onAvatarChange(newAvatarFile: File)
		{
			this.avatarFile = newAvatarFile;
			this.avatarUrl = '';
		},
		onDescriptionChange(description: string)
		{
			this.description = description;
		},
		onRightsChange(rights: AccessRightsFormResult)
		{
			const { ownerId, moderators, permissions, options } = rights;
			this.groupSettings.ownerId = ownerId;
			this.groupSettings.moderatorMembers = moderators;
			this.groupSettings.permissions = permissions;
			this.groupSettings.options = options;

			this.groupSettings.isModified = true;
		},
		async onUpdateClick(): Promise
		{
			Analytics.getInstance().chatEdit.onSubmitForm(this.dialogId);
			Analytics.getInstance().ignoreNextChatOpen(this.dialogId);

			this.isUpdating = true;

			let payload = {
				title: this.chatTitle,
				avatar: this.avatarFile,
				description: this.description,
			};

			if (this.groupSettings.isModified)
			{
				const groupSettings = {
					ownerId: this.groupSettings.ownerId,
					options: this.groupSettings.options,
					permissions: this.groupSettings.permissions,
					addModeratorMembers: this.memberDiffManager.getAddedManagers(this.groupSettings.moderatorMembers),
					deleteModeratorMembers: this.memberDiffManager.getDeletedManagers(this.groupSettings.moderatorMembers),
				};

				payload = {
					...payload,
					groupSettings,
				};
			}

			try
			{
				await this.getChatService().updateCollab(this.dialogId, payload);
				this.isUpdating = false;
				void Messenger.openChat(this.dialogId);
			}
			catch (error)
			{
				this.handleUpdateError(error);
			}
		},
		onCancelClick()
		{
			Analytics.getInstance().ignoreNextChatOpen(this.dialogId);
			void Messenger.openChat(this.dialogId);
		},
		handleUpdateError(error: { code: $Values<typeof UpdateCollabErrorCode> })
		{
			console.error('1', error);
			const { code } = error;
			const notificationText = NotificationTextByErrorCode[code] ?? NotificationTextByErrorCode.default;
			this.isUpdating = false;
			BX.UI.Notification.Center.notify({ content: notificationText });
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
		<div class="bx-im-content-chat-forms__content --collab">
			<div class="bx-im-content-chat-forms__header">
				<ChatAvatar
					:avatarFile="avatarFile"
					:existingAvatarUrl="avatarUrl"
					:chatTitle="chatTitle"
					:type="EmptyAvatarType.collab"
					@avatarChange="onAvatarChange"
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_COLLAB_TITLE_PLACEHOLDER')" />
			</div>
			<CreateChatHeading :text="loc('IM_CREATE_COLLAB_DESCRIPTION_TITLE')" />
			<div class="bx-im-content-chat-forms__description_container">
				<TextareaInput
					:value="description"
					:placeholder="loc('IM_CREATE_COLLAB_DESCRIPTION_PLACEHOLDER')"
					:border="false"
					@input="onDescriptionChange"
				/>
			</div>
			<RightsSection :collabId="collabId" @change="onRightsChange" />
		</div>
		<ButtonPanel
			:isCreating="isUpdating"
			:createButtonTitle="loc('IM_UPDATE_CONFIRM')"
			:createButtonColorScheme="updateButtonColorScheme"
			@create="onUpdateClick"
			@cancel="onCancelClick"
		/>
	`,
};
