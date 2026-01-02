import { Core } from 'im.v2.application.core';
import { BaseChatContent, ChatHeader } from 'im.v2.component.content.elements';
import { ChatTitle, ChatTitleType } from 'im.v2.component.elements.chat-title';

// @vue/component
export const NotesContent = {
	name: 'NotesContent',
	components: { BaseChatContent, ChatHeader, ChatTitle },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed: {
		titleType(): string
		{
			return Number.parseInt(this.dialogId, 10) === Core.getUserId() ? ChatTitleType.notes : '';
		},
	},
	template: `
		<BaseChatContent :dialogId="dialogId">
			<template #header>
				<ChatHeader :dialogId="dialogId" :withCallButton="false" :withAddToChatButton="false">
					<template #title>
						<ChatTitle :dialogId="dialogId" :customType="titleType" :showItsYou="false"/>
					</template>
				</ChatHeader>
			</template>
		</BaseChatContent>
	`,
};
