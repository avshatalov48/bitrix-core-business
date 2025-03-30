import { Color } from 'im.v2.const';

import { AvatarSize, Avatar } from '../base/avatar';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ExtranetChatAvatar = {
	name: 'ExtranetChatAvatar',
	components: { Avatar },
	props:
	{
		dialogId: {
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
		customSource: {
			type: String,
			default: '',
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
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogName(): string
		{
			return this.dialog.name;
		},
		dialogAvatarUrl(): string
		{
			return this.dialog.avatar;
		},
		extranetBackgroundColor(): string
		{
			return Color.orange50;
		},
	},
	template: `
		<Avatar
			:dialogId="dialogId"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl" 
			:backgroundColor="extranetBackgroundColor" 
		/>
	`,
};
