import { AvatarSize } from '../../const/const';
import { BaseUiAvatar, AvatarType } from '../base/base-ui-avatar';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CopilotAvatar = {
	name: 'CopilotAvatar',
	components: { BaseUiAvatar },
	props: {
		dialogId: {
			type: [String, Number],
			default: 0,
		},
		size: {
			type: String,
			default: AvatarSize.M,
		},
		customSource: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		AvatarType: () => AvatarType,
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
			return this.customSource.length > 0 ? this.customSource : this.dialog.avatar;
		},
	},
	template: `
		<BaseUiAvatar
			:type="AvatarType.copilot"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl" 
		/>
	`,
};
