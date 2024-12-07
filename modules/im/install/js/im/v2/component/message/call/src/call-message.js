import { DefaultMessage } from 'im.v2.component.message.default';
import { CallMessage as CallCustomMessage } from 'call.component.call-message';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const CallMessage = {
	name: 'CallMessage',
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
		withTitle: {
			type: Boolean,
			required: true,
		},
	},
	computed:
	{
		messageComponentToRender(): BitrixVueComponentProps
		{
			return CallCustomMessage ? CallCustomMessage : DefaultMessage;
		},
	},
	template: `
		<component :is="messageComponentToRender" :item="item" :dialogId="dialogId" :withTitle="withTitle" />
	`,
};
