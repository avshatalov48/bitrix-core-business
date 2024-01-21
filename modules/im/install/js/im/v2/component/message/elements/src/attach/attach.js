import { provide } from 'ui.vue3';

import { Attach } from 'im.v2.component.elements';
import { ChatType } from 'im.v2.const';

import './attach.css';

import type { ImModelChat, ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const MessageAttach = {
	name: 'MessageAttach',
	components: { Attach },
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
		dialogColor(): string
		{
			return this.dialog.type === ChatType.user ? this.user.color : this.dialog.color;
		},
	},
	created()
	{
		provide('message', this.message);
	},
	template: `
		<div v-for="config in message.attach" :key="config.id" class="bx-im-message-attach__container">
			<Attach :baseColor="dialogColor" :config="config" />
		</div>
	`,
};
