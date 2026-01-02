import { EventEmitter, type BaseEvent } from 'main.core.events';

import { BaseChatContent } from 'im.v2.component.content.elements';
import { ChatTextarea } from 'im.v2.component.textarea';
import { ChatDialog } from 'im.v2.component.dialog.chat';
import { EventType } from 'im.v2.const';
import { NotifierShowMessageAction } from 'im.v2.lib.message-notifier';

import { TaskChatHeader } from './components/header';

// @vue/component
export const TaskChatContent = {
	name: 'TaskChatContent',
	components: { BaseChatContent, TaskChatHeader, ChatDialog, ChatTextarea },
	props:
	{
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
	methods: {
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
		<BaseChatContent :dialogId="dialogId" :withSidebar="withSidebar">
			<template #header>
				<TaskChatHeader :dialogId="dialogId" />
			</template>
			<template #dialog>
				<ChatDialog
					:dialogId="dialogId"
					:key="dialogId"
					:reloadOnExit="false"
				/>
			</template>
			<template #textarea="{ onTextareaMount }">
				<ChatTextarea
					:dialogId="dialogId"
					:key="dialogId"
					:withMarket="false"
					:withAutoFocus="false"
					@mounted="onTextareaMount"
				/>
			</template>
		</BaseChatContent>
	`,
};
