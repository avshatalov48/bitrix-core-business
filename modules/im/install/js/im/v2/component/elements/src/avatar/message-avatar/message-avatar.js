import { CopilotManager } from 'im.v2.lib.copilot';

import { Avatar, AvatarSize } from '../base-avatar/avatar';

// @vue/component
export const MessageAvatar = {
	name: 'MessageAvatar',
	components: { Avatar },
	props:
	{
		messageId: {
			type: [String, Number],
			default: 0,
		},
		authorId: {
			type: [String, Number],
			default: 0,
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
			if (!copilotManager.isCopilotMessage(this.messageId))
			{
				return '';
			}

			return copilotManager.getMessageRoleAvatar(this.messageId);
		},
	},
	template: `
		<Avatar
			:dialogId="authorId"
			:customSource="customAvatarUrl"
			:size="size"
			:withAvatarLetters="withAvatarLetters"
			:withSpecialTypes="withSpecialTypes"
			:withSpecialTypeIcon="withSpecialTypeIcon"
			:withTooltip="withTooltip"
		/>
	`,
};
