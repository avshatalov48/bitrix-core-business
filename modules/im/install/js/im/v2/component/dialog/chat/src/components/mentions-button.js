import type { ImModelChat } from 'im.v2.model';
import { FloatButton, FloatButtonIcon, FloatButtonColor } from './float-button';
import { AnchorType } from 'im.v2.const';

// @vue/component
export const MentionsButton = {
	name: 'MentionsButton',
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
		mentionCounter(): number
		{
			return this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, AnchorType.mention);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		floatButtonProps(): {color: string, icon: string, counter: number}
		{
			return {
				color: FloatButtonColor.accent,
				icon: FloatButtonIcon.atSymbol,
				counter: this.mentionCounter,
			};
		},
	},
	template: `
		<FloatButton v-bind="floatButtonProps" />
	`,
};
