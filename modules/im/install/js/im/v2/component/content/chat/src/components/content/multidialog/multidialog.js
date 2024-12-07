import { BaseChatContent } from 'im.v2.component.content.elements';

import { MultidialogHeader } from './components/header';

export const MultidialogContent = {
	name: 'MultidialogContent',
	components: { BaseChatContent, MultidialogHeader },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	template: `
		<BaseChatContent :dialogId="dialogId">
			<template #header>
				<MultidialogHeader :dialogId="dialogId" :key="dialogId" />
			</template>
		</BaseChatContent>
	`,
};
