import { BaseMessage } from 'im.v2.component.message.base';

import './css/general-chat-creation.css';

// @vue/component
export const GeneralChatCreationMessage = {
	name: 'GeneralChatCreationMessage',
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
		>
		<div class="bx-im-message-general-chat-creation__container">
			<div class="bx-im-message-general-chat-creation__image"></div>
			<div class="bx-im-message-general-chat-creation__content">
				<div class="bx-im-message-chat-creation__title">
					<div class="bx-im-message-chat-creation__title-icon"></div>
					{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_TITLE') }}
				</div>
				<div class="bx-im-message-general-chat-creation__description">
					<ul class="bx-im-message-general-chat-creation__description-list">
						<li>
							<div class="bx-im-message-general-chat-creation__description-list_icon --chat"></div>
							{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_LIST_CHATS') }}
						</li>
						<li>
							<div class="bx-im-message-general-chat-creation__description-list_icon --stress"></div>
							{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_LIST_STRESS') }}
						</li>
						<li>
							<div class="bx-im-message-general-chat-creation__description-list_icon --persons"></div>
							{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_LIST_PERSONS') }}
						</li>
					</ul>
				</div>
			</div>
		</div>
		</BaseMessage>
	`,
};
