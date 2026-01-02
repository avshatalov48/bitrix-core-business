import type { ImModelChat } from 'im.v2.model';
import { AnchorType } from 'im.v2.const';

import { FloatButton, FloatButtonColor, FloatButtonIcon } from './float-button';

export const ReactionsButton = {
	name: 'ReactionsButton',
	components: {
		FloatButton,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		reactionCounter(): number
		{
			return this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, AnchorType.reaction);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		floatButtonProps(): { color: string, icon: string, counter: number }
		{
			return {
				color: FloatButtonColor.alert,
				icon: FloatButtonIcon.heart,
				counter: this.reactionCounter,
			};
		},
	},
	template: `
		<FloatButton v-bind="floatButtonProps" />
	`,
};
