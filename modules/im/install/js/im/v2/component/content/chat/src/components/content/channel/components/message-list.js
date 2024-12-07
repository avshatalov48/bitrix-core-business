import { MessageList } from 'im.v2.component.message-list';

import { ChannelMessageMenu } from '../classes/message-menu';

// @vue/component
export const ChannelMessageList = {
	name: 'ChannelMessageList',
	components: { MessageList },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		ChannelMessageMenu: () => ChannelMessageMenu,
	},
	template: `
		<MessageList :dialogId="dialogId" :messageMenuClass="ChannelMessageMenu" />
	`,
};
