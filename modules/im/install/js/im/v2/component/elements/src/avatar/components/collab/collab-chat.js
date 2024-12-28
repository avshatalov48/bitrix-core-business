import { Color } from 'im.v2.const';

import { AvatarSize } from '../base/avatar';
import { UiAvatarHexagon } from '../base/ui-avatar-hexagon';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CollabChatAvatar = {
	name: 'CollabChatAvatar',
	components: { UiAvatarHexagon },
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
		collabBackgroundColor(): string
		{
			return Color.collab60;
		},
	},
	template: `
		<UiAvatarHexagon
			:key="dialogId"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl" 
			:backgroundColor="collabBackgroundColor" 
		/>
	`,
};
