import { Core } from 'im.v2.application.core';
import { ChatAvatar, ChatAvatarType, AvatarSize } from 'im.v2.component.elements.avatar';
import { ActionByRole, ChatType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { Utils } from 'im.v2.lib.utils';
import { ChatService } from 'im.v2.provider.service.chat';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const HeaderAvatar = {
	name: 'HeaderAvatar',
	components: { ChatAvatar },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['avatarClick'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		canChangeAvatar(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.avatar, this.dialogId);
		},
		isNotes(): boolean
		{
			return this.$store.getters['chats/isNotes'](this.dialogId);
		},
		userLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
		avatarType(): string
		{
			return this.isNotes ? ChatAvatarType.notes : '';
		},
		needProfileLink(): boolean
		{
			return this.isUser && !this.isNotes;
		},
	},
	methods:
	{
		onAvatarClick()
		{
			if (this.isUser || !this.canChangeAvatar)
			{
				return;
			}

			this.$refs.avatarInput.click();
		},
		async onAvatarSelect(event: Event)
		{
			const input: HTMLInputElement = event.target;
			const file: File = input.files[0];
			if (!file)
			{
				return;
			}

			const preparedAvatar = await this.getChatService().prepareAvatar(file);
			if (!preparedAvatar)
			{
				return;
			}
			void this.getChatService().changeAvatar(this.dialog.chatId, preparedAvatar);
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
	},
	// language=Vue
	template: `
		<div class="bx-im-chat-header__avatar" :class="{'--can-change': canChangeAvatar}" @click="onAvatarClick">
			<a v-if="needProfileLink" :href="userLink" target="_blank">
				<ChatAvatar
					:avatarDialogId="dialogId"
					:contextDialogId="dialogId"
					:size="AvatarSize.L"
				/>
			</a>
			<ChatAvatar v-else :avatarDialogId="dialogId" :contextDialogId="dialogId" :size="AvatarSize.L" :customType="avatarType" />
		</div>
		<input
			type="file"
			accept="image/*"
			class="bx-im-chat-header__avatar_input"
			ref="avatarInput"
			@change="onAvatarSelect"
		>
	`,
};
