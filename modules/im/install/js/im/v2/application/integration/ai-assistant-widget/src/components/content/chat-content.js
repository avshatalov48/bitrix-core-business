import { EventEmitter, type BaseEvent } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { AiAssistantBotContent } from 'im.v2.component.content.chat';
import { NotifierShowMessageAction } from 'im.v2.lib.message-notifier';

import { AiAssistantWidgetChatHeader } from './components/header';

// @vue/component
export const AiAssistantWidgetChatContent = {
	name: 'AiAssistantWidgetChatContent',
	components: { AiAssistantWidgetChatHeader, AiAssistantBotContent },
	props: {
		dialogId: {
			type: String,
			default: '',
		},
		withSidebar: {
			type: Boolean,
			default: true,
		},
	},
	created()
	{
		EventEmitter.subscribe(EventType.notifier.onBeforeShowMessage, this.onBeforeNotificationShow);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.notifier.onBeforeShowMessage, this.onBeforeNotificationShow);
	},
	methods:
	{
		onBeforeNotificationShow(event: BaseEvent<{ dialogId: string }>): $Values<typeof NotifierShowMessageAction>
		{
			const eventData = event.getData();
			if (eventData.dialogId !== this.dialogId)
			{
				return NotifierShowMessageAction.show;
			}

			return NotifierShowMessageAction.skip;
		},
	},
	template: `
		<AiAssistantBotContent :dialogId="dialogId" :withSidebar="withSidebar">
			<template #header>
				<AiAssistantWidgetChatHeader :dialogId="dialogId"/>
			</template>
		</AiAssistantBotContent>
	`,
};
