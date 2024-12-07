import { BaseMessage } from 'im.v2.component.message.base';

import './css/chat-creation.css';

import type { ImModelMessage } from 'im.v2.model';

const TITLE_PARAMS_KEY = 'bannerTitle';
// @vue/component
export const SupportChatCreationMessage = {
	name: 'SupportChatCreationMessage',
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
		message(): ImModelMessage
		{
			return this.item;
		},
		componentParams(): Object
		{
			return this.message.componentParams;
		},
		title(): string
		{
			return this.componentParams[TITLE_PARAMS_KEY];
		},
		text(): string
		{
			return this.message.text;
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
			:withBackground="false"
			:withContextMenu="false"
			:withReactions="false"
			class="bx-im-message-support-chat-creation__scope"
		>
			<div class="bx-im-message-support-chat-creation__container">
				<div class="bx-im-message-support-chat-creation__image" />
				<div class="bx-im-message-support-chat-creation__content">
					<div class="bx-im-message-chat-creation__title">
						{{ title }}
					</div>
					<div class="bx-im-message-support-chat-creation__description">
						{{ text }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
