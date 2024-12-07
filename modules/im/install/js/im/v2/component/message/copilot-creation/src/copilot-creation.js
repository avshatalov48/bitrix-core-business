import { BaseMessage } from 'im.v2.component.message.base';
import { SendingService } from 'im.v2.provider.service';

import './css/copilot-creation-message.css';

import type { JsonObject } from 'main.core';
import type { ImModelMessage } from 'im.v2.model';

const SAMPLE_MESSAGES = [
	'IM_MESSAGE_COPILOT_CREATION_ACTION_WHAT_CAN_YOU_DO',
	'IM_MESSAGE_COPILOT_CREATION_ACTION_NEW_USER_GREETING',
	'IM_MESSAGE_COPILOT_CREATION_ACTION_BIRTHDAY_GREETING',
	'IM_MESSAGE_COPILOT_CREATION_ACTION_BIRTHDAY_PRESENT_IDEA',
];

// @vue/component
export const ChatCopilotCreationMessage = {
	name: 'ChatCopilotCreationMessage',
	components: { BaseMessage },
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		sampleMessages: () => SAMPLE_MESSAGES,
		message(): ImModelMessage
		{
			return this.item;
		},
		chatId(): number
		{
			return this.message.chatId;
		},
		preparedText(): string
		{
			return this.loc('IM_MESSAGE_COPILOT_CREATION_TEXT', {
				'#BR#': '\n',
			});
		},
	},
	methods:
	{
		onMessageClick(text: string)
		{
			this.getSendingService().sendMessage({ text: this.loc(text), dialogId: this.dialogId });
		},
		getSendingService(): SendingService
		{
			if (!this.sendingService)
			{
				this.sendingService = SendingService.getInstance();
			}

			return this.sendingService;
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withBackground="false"
		>
			<div class="bx-im-message-copilot-creation__container">
				<div class="bx-im-message-copilot-creation__title">CoPilot</div>
				<div class="bx-im-message-copilot-creation__text">{{ preparedText }}</div>
				<div class="bx-im-message-copilot-creation__actions">
					<div
						v-for="message in sampleMessages"
						:key="message"
						@click="onMessageClick(message)"
						class="bx-im-message-copilot-creation__action"
					>
						{{ loc(message) }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
