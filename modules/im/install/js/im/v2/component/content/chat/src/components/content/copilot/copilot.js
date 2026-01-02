import { BaseChatContent } from 'im.v2.component.content.elements';

import { CopilotChatHeader } from './components/header';
import { CopilotTextarea } from './components/textarea';

// @vue/component
export const CopilotContent = {
	name: 'CopilotContent',
	components: { BaseChatContent, CopilotChatHeader, CopilotTextarea },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	template: `
		<BaseChatContent :dialogId="dialogId" :withDropArea="false">
			<template #header>
				<CopilotChatHeader :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #textarea="{ onTextareaMount }">
				<CopilotTextarea :dialogId="dialogId" :key="dialogId" @mounted="onTextareaMount" />
			</template>
		</BaseChatContent>
	`,
};
