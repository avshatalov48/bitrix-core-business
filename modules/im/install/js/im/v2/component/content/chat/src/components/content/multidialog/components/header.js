import { ChatHeader } from 'im.v2.component.content.elements';

import { MultidialogChatTitle } from './title';

// @vue/component
export const MultidialogHeader = {
	name: 'MultidialogHeader',
	components: { ChatHeader, MultidialogChatTitle },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	template: `
		<ChatHeader :dialogId="dialogId">
			<template #title="{ onNewTitleHandler }">
				<MultidialogChatTitle
					:dialogId="dialogId"
					@newTitle="onNewTitleHandler"
				/>
			</template>
		</ChatHeader>
	`,
};
