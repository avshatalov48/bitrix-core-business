import { Keyboard } from 'im.v2.component.elements';

import './keyboard.css';

import type { ImModelChat, ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const MessageKeyboard = {
	name: 'MessageKeyboard',
	components: { Keyboard },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
	},
	template: `
		<div class="bx-im-message-keyboard__container">
			<Keyboard :buttons="message.keyboard" :dialogId="dialogId" :messageId="message.id" />
		</div>
	`,
};
