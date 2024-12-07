import { BaseMessage } from 'im.v2.component.message.base';

import './css/own-chat-creation.css';

import type { JsonObject } from 'main.core';
import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const OwnChatCreationMessage = {
	name: 'OwnChatCreationMessage',
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
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		description(): string
		{
			return this.loc('IM_MESSAGE_OWN_CHAT_CREATION_DESCRIPTION', {
				'#BR#': '\n',
			});
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
			class="bx-im-message-own-chat-creation__scope"
		>
			<div class="bx-im-message-own-chat-creation__container">
				<div class="bx-im-message-own-chat-creation__image"></div>
				<div class="bx-im-message-own-chat-creation__content">
					<div class="bx-im-message-chat-creation__title">
						{{ loc('IM_MESSAGE_OWN_CHAT_CREATION_TITLE') }}
					</div>
					<div class="bx-im-message-own-chat-creation__description">
						{{ description }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
