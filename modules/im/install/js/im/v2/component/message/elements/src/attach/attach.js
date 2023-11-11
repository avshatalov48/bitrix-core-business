import { provide } from 'ui.vue3';

import { Attach } from 'im.v2.component.elements';
import { DialogType } from 'im.v2.const';

import './attach.css';

import type { ImModelDialog, ImModelMessage } from 'im.v2.model';

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
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		dialogColor(): string
		{
			return this.dialog.type === DialogType.private ? this.user.color : this.dialog.color;
		},
	},
	created()
	{
		provide('message', this.message);
	},
	template: `
		<div v-for="config in message.attach" :key="config.ID" class="bx-im-message-attach__container">
			<Attach :baseColor="dialogColor" :config="config" />
		</div>
	`,
};
