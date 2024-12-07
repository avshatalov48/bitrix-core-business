import { DialogStatus } from 'im.v2.component.elements';
import { MessageList } from 'im.v2.component.message-list';

import { CopilotMessageMenu } from '../classes/message-menu';

// @vue/component
export const CopilotMessageList = {
	name: 'CopilotMessageList',
	components: { MessageList, DialogStatus },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		CopilotMessageMenu: () => CopilotMessageMenu,
	},
	template: `
		<MessageList :dialogId="dialogId" :messageMenuClass="CopilotMessageMenu" />
	`,
};
