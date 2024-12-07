import { Type } from 'main.core';

import { BaseMessage } from 'im.v2.component.message.base';
import { DefaultMessageContent } from 'im.v2.component.message.elements';

import './system.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const SystemMessage = {
	name: 'SystemMessage',
	components: {
		BaseMessage,
		DefaultMessageContent,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		withTitle: {
			type: Boolean,
			default: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withTitle="false"
			:withBackground="false"
			class="bx-im-message-system__scope"
		>
			<div class="bx-im-message-system__container">
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
		</BaseMessage>
	`,
};
