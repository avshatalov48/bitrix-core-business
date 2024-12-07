import { ChatDialog } from 'im.v2.component.dialog.chat';

import { CopilotMessageList } from './message-list';

// @vue/component
export const CopilotDialog = {
	name: 'CopilotDialog',
	components: { ChatDialog, CopilotMessageList },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	template: `
		<ChatDialog :dialogId="dialogId">
			<template #message-list>
				<CopilotMessageList :dialogId="dialogId" />
			</template>
		</ChatDialog>	
	`,
};
