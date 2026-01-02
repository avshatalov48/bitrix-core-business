import { BaseMessage } from 'im.v2.component.message.base';

import './css/task-chat-creation.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const TaskChatCreationMessage = {
	name: 'TaskChatCreationMessage',
	components: { BaseMessage },
	props:
	{
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
		message(): ImModelMessage
		{
			return this.item;
		},
	},
	methods:
	{
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
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-task-chat-creation__scope"
		>
			<div class="bx-im-message-task-chat-creation__container">
				<div class="bx-im-message-task-chat-creation__image"></div>
				<div class="bx-im-message-task-chat-creation__content">
					<div class="bx-im-message-task-chat-creation__title">
						{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_TITLE') }}
					</div>
					<ul class="bx-im-message-task-chat-creation__list">
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --camera"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_CAMERA') }}</span>
						</li>
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --file"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_FILE') }}</span>
						</li>
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --result"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_RESULT') }}</span>
						</li>
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --forward"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_FORWARD') }}</span>
						</li>
					</ul>
				</div>
			</div>
		</BaseMessage>
	`,
};
