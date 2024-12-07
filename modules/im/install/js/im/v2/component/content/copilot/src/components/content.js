import { BaseChatContent } from 'im.v2.component.content.elements';

import { CopilotChatHeader } from './chat-header';
import { CopilotTextarea } from './textarea';
import { CopilotDialog } from './dialog';
import { COPILOT_BACKGROUND_ID } from '../const/const';

// @vue/component
export const CopilotInternalContent = {
	name: 'CopilotInternalContent',
	components: { BaseChatContent, CopilotChatHeader, CopilotDialog, CopilotTextarea },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		COPILOT_BACKGROUND_ID: () => COPILOT_BACKGROUND_ID,
	},
	template: `
		<BaseChatContent :dialogId="dialogId" :backgroundId="COPILOT_BACKGROUND_ID">
			<template #header>
				<CopilotChatHeader :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #dialog>
				<CopilotDialog :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #textarea="{ onTextareaMount }">
				<CopilotTextarea :dialogId="dialogId" :key="dialogId" @mounted="onTextareaMount" />
			</template>
		</BaseChatContent>
	`,
};
