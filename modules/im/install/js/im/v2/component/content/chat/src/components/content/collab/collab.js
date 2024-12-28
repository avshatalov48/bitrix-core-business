import { BaseChatContent } from 'im.v2.component.content.elements';
import { SpecialBackground } from 'im.v2.lib.theme';

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
	computed:
	{
		SpecialBackground: () => SpecialBackground,
	},
	template: `
		<BaseChatContent :dialogId="dialogId" :backgroundId="SpecialBackground.collab">
			<template #header>
				<CollabHeader :dialogId="dialogId" :key="dialogId" />
			</template>
		</BaseChatContent>
	`,
};
