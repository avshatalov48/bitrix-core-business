import { UserType } from 'im.v2.const';
import { CopilotManager } from 'im.v2.lib.copilot';

import { Avatar, AvatarSize } from './components/base/avatar';
import { CollaberAvatar } from './components/collab/collaber';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const MessageAvatar = {
	name: 'MessageAvatar',
	components: { Avatar, CollaberAvatar },
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
		isCollaber(): boolean
		{
			const user = this.$store.getters['users/get'](this.authorId, true);

			return user.type === UserType.collaber;
		},
		avatarComponent(): BitrixVueComponentProps
		{
			return this.isCollaber ? CollaberAvatar : Avatar;
		},
	},
	template: `
		<component
			:is="avatarComponent"
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
