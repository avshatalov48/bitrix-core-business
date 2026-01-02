import { ChatType, UserType } from 'im.v2.const';
import { CopilotManager } from 'im.v2.lib.copilot';

import { AvatarSize, ChatAvatarType } from './const/const';
import { Avatar } from './components/base/avatar';
import { CollabChatAvatar } from './components/collab/collab-chat';
import { CollaberAvatar } from './components/collab/collaber';
import { ExtranetChatAvatar } from './components/extranet/extranet-chat-avatar';
import { ExtranetUserAvatar } from './components/extranet/extranet-user-avatar';
import { CopilotAvatar } from './components/copilot/copilot';
import { NotesAvatar } from './components/notes-avatar';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelChat, ImModelUser } from 'im.v2.model';

// @vue/component
export const ChatAvatar = {
	name: 'ChatAvatar',
	components:
	{
		Avatar,
		CollabAvatar:
		CollabChatAvatar,
		CollaberAvatar,
		ExtranetUserAvatar,
		NotesAvatar,
		CopilotAvatar,
	},
	props: {
		avatarDialogId: {
			type: [String, Number],
			default: 0,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
		size: {
			type: String,
			default: AvatarSize.M,
		},
		withAvatarLetters: {
			type: Boolean,
			default: true,
		},
		withSpecialTypes: {
			type: Boolean,
			default: true,
		},
		withSpecialTypeIcon: {
			type: Boolean,
			default: true,
		},
		withTooltip: {
			type: Boolean,
			default: true,
		},
		customType: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		isUser(): boolean
		{
			return this.avatarDialog.type === ChatType.user;
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.avatarDialogId, true);
		},
		customAvatarUrl(): string
		{
			if (!this.isCopilot)
			{
				return '';
			}

			return this.copilotManager.getRoleAvatarUrl({
				avatarDialogId: this.avatarDialogId,
				contextDialogId: this.contextDialogId,
			});
		},
		avatarDialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.avatarDialogId, true);
		},
		isCollabChat(): boolean
		{
			return this.avatarDialog.type === ChatType.collab;
		},
		isCollaber(): boolean
		{
			return this.user?.type === UserType.collaber;
		},
		isExtranetChat(): boolean
		{
			return this.avatarDialog.extranet;
		},
		isExtranet(): boolean
		{
			return this.user?.type === UserType.extranet;
		},
		isCopilot(): boolean
		{
			return this.copilotManager.isCopilotChatOrBot(this.avatarDialogId);
		},
		avatarComponent(): BitrixVueComponentProps
		{
			if (this.customType === ChatAvatarType.notes)
			{
				return NotesAvatar;
			}

			if (this.isExtranet)
			{
				return ExtranetUserAvatar;
			}

			if (this.isCollaber)
			{
				return CollaberAvatar;
			}

			if (this.isCollabChat)
			{
				return CollabChatAvatar;
			}

			if (this.isCopilot)
			{
				return CopilotAvatar;
			}

			return this.isExtranetChat ? ExtranetChatAvatar : Avatar;
		},
	},
	created()
	{
		this.copilotManager = new CopilotManager();
	},
	template: `
		<component
			:is="avatarComponent"
			:dialogId="avatarDialogId"
			:customSource="customAvatarUrl"
			:size="size"
			:withAvatarLetters="withAvatarLetters"
			:withSpecialTypes="withSpecialTypes"
			:withSpecialTypeIcon="withSpecialTypeIcon"
			:withTooltip="withTooltip"
		/>
	`,
};
