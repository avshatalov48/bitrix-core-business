import { BaseChatContent } from 'im.v2.component.content.elements';

import { CollabHeader } from './components/header';

import './css/collab.css';

export const CollabContent = {
	name: 'CollabContent',
	components: { BaseChatContent, CollabHeader },
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
				<CollabHeader :dialogId="dialogId" :key="dialogId" />
			</template>
		</BaseChatContent>
	`,
};
