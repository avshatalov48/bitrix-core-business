import { UnsupportedMessage } from 'im.v2.component.message.unsupported';
import { VoteChatDisplay } from 'vote.component.message';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const VoteMessage = {
	name: 'VoteMessage',
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
				return VoteChatDisplay ? VoteChatDisplay : UnsupportedMessage;
			},
		},
	template: `
		<component :is="messageComponentToRender" :item="item" :dialogId="dialogId" :withTitle="withTitle" />
	`,
};
