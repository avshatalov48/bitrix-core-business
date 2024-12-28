import { Loc } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { Messenger } from 'im.public';
import { ChatService } from 'im.v2.provider.service';
import { EmptyAvatarType } from 'im.v2.component.elements';
import { ChatType, EventType, Layout, Color } from 'im.v2.const';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { TitleInput, ChatAvatar, CreateChatHeading, TextareaInput, ButtonPanel } from 'im.v2.component.content.chat-forms.elements';

import { DescriptionBanner } from './components/description-banner/description-banner';
import { RightsSection } from './components/rights-section';

import type { JsonObject } from 'main.core';
import type { CustomColorScheme } from 'im.v2.component.elements';
import type { OnLayoutChangeEvent } from 'im.v2.const';
import type { AccessRightsFormResult } from './components/rights-section';

const CreateCollabErrorCode = {
	emptyName: 'name',
	duplicateName: 'ERROR_GROUP_NAME_EXISTS',
};

const NotificationTextByErrorCode = {
	[CreateCollabErrorCode.emptyName]: Loc.getMessage('IM_CREATE_COLLAB_ERROR_EMPTY_NAME'),
	[CreateCollabErrorCode.duplicateName]: Loc.getMessage('IM_CREATE_COLLAB_ERROR_DUPLICATE_NAME'),
	default: Loc.getMessage('IM_CREATE_CHAT_ERROR'),
};

export const CollabCreation = {
	name: 'CollabCreation',
	components: {
		TitleInput,
		ChatAvatar,
		DescriptionBanner,
		CreateChatHeading,
		TextareaInput,
		ButtonPanel,
		RightsSection,
	},
	data(): JsonObject
	{
		return {
			isCreating: false,
			avatarFile: null,
			chatTitle: '',
			description: '',
			groupSettings: {
				ownerId: Core.getUserId(),
				moderatorMembers: [],
				options: null,
				permissions: null,
			},
		};
	},
	watch:
	{
		chatTitle(newValue: string): void
		{
			CreateChatManager.getInstance().setChatTitle(newValue);
		},
	},
	computed:
	{
		EmptyAvatarType: () => EmptyAvatarType,
		createButtonColorScheme(): CustomColorScheme
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

			const { chatTitle, avatarFile, description } = savedFields;
			this.chatTitle = chatTitle;
			this.avatarFile = avatarFile;
			this.description = description;
		},
		saveFields()
		{
			CreateChatManager.getInstance().saveFields({
				chatTitle: this.chatTitle,
				avatarFile: this.avatarFile,
				description: this.description,
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
		async onCreateClick(): void
		{
			this.isCreating = true;

			try
			{
				const { newDialogId } = await this.getChatService().createCollab({
					title: this.chatTitle,
					avatar: this.avatarFile,
					description: this.description,
					ownerId: this.groupSettings.ownerId,
					moderatorMembers: this.groupSettings.moderatorMembers,
					options: this.groupSettings.options,
					permissions: this.groupSettings.permissions,
				});

				this.isCreating = false;
				this.exitByCreation = true;
				CreateChatManager.getInstance().setCreationStatus(false);
				await Messenger.openChat(newDialogId);
				EventEmitter.emit(EventType.header.openAddToChatPopup);
			}
			catch (error)
			{
				this.handleCreationError(error);
			}
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
		},
		handleCreationError(error: { code: $Values<typeof CreateCollabErrorCode> })
		{
			const { code } = error;
			const notificationText = NotificationTextByErrorCode[code] ?? NotificationTextByErrorCode.default;
			this.isCreating = false;
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
					:chatTitle="chatTitle" 
					:type="EmptyAvatarType.collab" 
					@avatarChange="onAvatarChange" 
				/>
				<TitleInput v-model="chatTitle" :placeholder="loc('IM_CREATE_COLLAB_TITLE_PLACEHOLDER')" />
			</div>
			<DescriptionBanner />
			<CreateChatHeading :text="loc('IM_CREATE_COLLAB_DESCRIPTION_TITLE')" />
			<div class="bx-im-content-chat-forms__description_container">
				<TextareaInput
					:value="description"
					:placeholder="loc('IM_CREATE_COLLAB_DESCRIPTION_PLACEHOLDER')"
					:border="false"
					@input="onDescriptionChange"
				/>
			</div>
			<RightsSection @change="onRightsChange" />
		</div>
		<ButtonPanel
			:isCreating="isCreating"
			:createButtonTitle="loc('IM_CREATE_COLLAB_CONFIRM')"
			:createButtonColorScheme="createButtonColorScheme"
			@create="onCreateClick"
			@cancel="onCancelClick"
		/>
	`,
};
