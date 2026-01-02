import { UserType } from 'im.v2.const';
import { CopilotManager } from 'im.v2.lib.copilot';

import { AvatarSize } from './const/const';
import { Avatar } from './components/base/avatar';
import { CollaberAvatar } from './components/collab/collaber';
import { CopilotAvatar } from './components/copilot/copilot';
import { ExtranetUserAvatar } from './components/extranet/extranet-user-avatar';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelUser } from 'im.v2.model';

// @vue/component
export const MessageAvatar = {
	name: 'MessageAvatar',
	components: { Avatar, CollaberAvatar, CopilotAvatar },
	props: {
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
	computed: {
		customAvatarUrl(): string
		{
			const copilotManager = new CopilotManager();
			if (!copilotManager.isCopilotMessage(this.messageId))
			{
				return '';
			}

			return copilotManager.getMessageRoleAvatar(this.messageId);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.authorId, true);
		},
		avatarComponent(): BitrixVueComponentProps
		{
			const avatarMap = {
				[UserType.extranet]: ExtranetUserAvatar,
				[UserType.collaber]: CollaberAvatar,
				[UserType.bot]: this.getBotAvatar(),
			};

			return avatarMap[this.user.type] ?? Avatar;
		},
	},
	methods:
	{
		getBotAvatar(): BitrixVueComponentProps
		{
			const copilotManager = new CopilotManager();

			return copilotManager.isCopilotChatOrBot(this.authorId) ? CopilotAvatar : Avatar;
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
