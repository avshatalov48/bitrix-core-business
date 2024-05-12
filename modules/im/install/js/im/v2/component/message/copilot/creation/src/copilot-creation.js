import { BaseMessage } from 'im.v2.component.message.base';
import { SendingService } from 'im.v2.provider.service';

import './css/copilot-creation-message.css';

import type { ImModelMessage } from 'im.v2.model';

const SAMPLE_MESSAGES = {
	IM_MESSAGE_COPILOT_CREATION_ACTION_1: 'plan',
	IM_MESSAGE_COPILOT_CREATION_ACTION_2: 'vacancy',
	IM_MESSAGE_COPILOT_CREATION_ACTION_3: 'ideas',
	IM_MESSAGE_COPILOT_CREATION_ACTION_4: 'letter',
};

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
	computed:
	{
		sampleMessages(): string[]
		{
			return Object.keys(SAMPLE_MESSAGES);
		},
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
		onMessageClick(promptLangCode: string)
		{
			void this.getSendingService().sendCopilotPrompt({
				text: this.loc(promptLangCode),
				dialogId: this.dialogId,
				copilot: {
					promptCode: SAMPLE_MESSAGES[promptLangCode],
				},
			});
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
			:withDefaultContextMenu="false"
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
