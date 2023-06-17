import {Messenger} from 'im.public';
import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {Layout} from 'im.v2.const';
import {ChatService} from 'im.v2.provider.service';

import {Button, ButtonSize, ButtonColor} from 'im.v2.component.elements';

import {ChatMembersSelector} from './components/chat-members';
import {SettingsSection} from './components/settings/settings-section';
import {AppearanceSection} from './components/appearance/appearance-section';

import 'ui.notification';
import './css/create-chat-content.css';

// @vue/component
export const CreateChatContent = {
	name: 'CreateChatContent',
	components: {ChatMembersSelector, SettingsSection, AppearanceSection, Button},
	data()
	{
		return {
			isCreating: false,
			chatTitle: '',
			chatMembers: [],
			settings: {
				ownerId: 0,
				manageType: '',
				isAvailableInSearch: false,
				description: ''
			}
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor
	},
	created()
	{
		Logger.warn('Content: CreateChat created');
		this.chatService = new ChatService();
		this.settings.ownerId = Core.getUserId();
	},
	mounted()
	{
		this.$refs['titleInput'].focus();
	},
	methods:
	{
		onMembersChange(currentTags: number[])
		{
			this.chatMembers = currentTags;
		},
		onOwnerChange(ownerId: number)
		{
			this.settings.ownerId = ownerId;
		},
		onManageTypeChange(value: string)
		{
			this.settings.manageType = value;
		},
		onChatTypeChange(isAvailableInSearch: boolean)
		{
			this.settings.isAvailableInSearch = isAvailableInSearch;
		},
		onDescriptionChange(description: string)
		{
			this.settings.description = description;
		},
		onCreateClick()
		{
			this.isCreating = true;
			this.chatService.createChat({
				title: this.chatTitle,
				members: this.chatMembers,
				ownerId: this.settings.ownerId,
				manageType: this.settings.manageType,
				isAvailableInSearch: this.settings.isAvailableInSearch,
				description: this.settings.description
			}).then(newDialogId => {
				this.isCreating = false;
				Messenger.openChat(newDialogId);
			}).catch(() => {
				this.isCreating = false;
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_CREATE_CHAT_ERROR')
				});
			});
		},
		onCancelClick()
		{
			Messenger.openChat();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-content-create-chat__container bx-im-content-create-chat__scope">
			<div class="bx-im-content-create-chat__content">
				<div class="bx-im-content-create-chat__header">
					<!--<div class="bx-im-content-create-chat__avatar"></div>-->
					<div class="bx-im-content-create-chat__title_container">
						<input
							v-model="chatTitle"
							:placeholder="loc('IM_CREATE_CHAT_TITLE_PLACEHOLDER')"
							class="bx-im-content-create-chat__title_input"
							ref="titleInput"
						/>
					</div>
				</div>
				<ChatMembersSelector @membersChange="onMembersChange" />
				<SettingsSection
					:isAvailableInSearch="settings.isAvailableInSearch"
					:ownerId="settings.ownerId"
					:description="settings.description"
					@ownerChange="onOwnerChange"
					@manageTypeChange="onManageTypeChange"
					@chatTypeChange="onChatTypeChange"
					@descriptionChange="onDescriptionChange"
				/>
				<!--<AppearanceSection />-->
			</div>
			<div class="bx-im-content-create-chat__buttons">
				<div class="bx-im-content-create-chat__buttons_create">
					<Button
						:size="ButtonSize.XL"
						:color="ButtonColor.Success"
						:text="loc('IM_CREATE_CHAT_CONFIRM')"
						:isLoading="isCreating"
						:isDisabled="isCreating"
						@click="onCreateClick"
					/>
				</div>
				<div class="bx-im-content-create-chat__buttons_cancel">
					<Button
						:size="ButtonSize.XL"
						:color="ButtonColor.Link"
						:text="loc('IM_CREATE_CHAT_CANCEL')"
						:isDisabled="isCreating"
						@click="onCancelClick"
					/>
				</div>
			</div>
		</div>
	`
};