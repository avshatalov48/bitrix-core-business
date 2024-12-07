import { BaseChatContent } from 'im.v2.component.content.elements';

import { ChannelDialog } from './components/dialog';
import { JoinPanel } from './components/join-panel';
import { ChannelTextarea } from './components/textarea';

export const ChannelContent = {
	name: 'ChannelContent',
	components: { BaseChatContent, ChannelDialog, ChannelTextarea, JoinPanel },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	template: `
		<BaseChatContent :dialogId="dialogId">
			<template #dialog>
				<ChannelDialog :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #join-panel>
				<JoinPanel :dialogId="dialogId" />
			</template>
			<template #textarea="{ onTextareaMount }">
				<ChannelTextarea :dialogId="dialogId" :key="dialogId" @mounted="onTextareaMount" />
			</template>
		</BaseChatContent>
	`,
};
