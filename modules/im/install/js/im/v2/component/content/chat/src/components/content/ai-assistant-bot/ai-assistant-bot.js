import { BaseChatContent } from 'im.v2.component.content.elements';
import { ChatTextarea } from 'im.v2.component.textarea';

import { ServiceHealthPanel } from './service-health-panel/service-health-panel';

export const AiAssistantBotContent = {
	name: 'AiAssistantBotContent',
	components: { BaseChatContent, ChatTextarea, ServiceHealthPanel },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		withSidebar: {
			type: Boolean,
			default: true,
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<BaseChatContent :dialogId="dialogId" :withSidebar="withSidebar" :withDropArea="false">
			<template #header v-if="$slots['header']">
				<slot name="header"></slot>
			</template>
			<template #sub-header>
				<ServiceHealthPanel />
			</template>
			<template #textarea="{ onTextareaMount }">
				<ChatTextarea
					:dialogId="dialogId"
					:key="dialogId"
					:placeholder="loc('IM_CONTENT_AIASSISTANT_TEXTAREA_PLACEHOLDER_MSGVER_1')"
					:withMarket="false"
					:withEdit="false"
					:withUploadMenu="false"
					:withSmileSelector="false"
					@mounted="onTextareaMount"
				/>
			</template>
		</BaseChatContent>
	`,
};
