import { CopilotManager } from 'im.v2.lib.copilot';

import { Avatar, AvatarSize } from '../base-avatar/avatar';

// @vue/component
export const ChatAvatar = {
	name: 'ChatAvatar',
	components: { Avatar },
	props:
	{
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
	},
	computed:
	{
		customAvatarUrl(): string
		{
			const copilotManager = new CopilotManager();
			if (!copilotManager.isCopilotChatOrBot(this.avatarDialogId))
			{
				return '';
			}

			return copilotManager.getRoleAvatarUrl({
				avatarDialogId: this.avatarDialogId,
				contextDialogId: this.contextDialogId,
			});
		},
	},
	template: `
		<Avatar
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
