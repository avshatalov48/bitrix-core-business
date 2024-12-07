import type { ImModelMessage } from 'im.v2.model';
import { Type } from 'main.core';

import { BaseMessage } from 'im.v2.component.message.base';
import { DefaultMessageContent, AuthorTitle } from 'im.v2.component.message.elements';

import './css/deleted.css';

// @vue/component
export const DeletedMessage = {
	name: 'DeletedMessage',
	components: {
		BaseMessage,
		DefaultMessageContent,
		AuthorTitle,
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
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<BaseMessage :dialogId="dialogId" :item="item">
			<div class="bx-im-message-deleted__container bx-im-message-deleted__scope">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-deleted__content-container">
					<div class="bx-im-message-deleted__icon"></div>
					<div class="bx-im-message-deleted__text">{{ loc('IM_MESSENGER_MESSAGE_DELETED') }}</div>	
				</div>
				<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" />
			</div>
		</BaseMessage>
	`,
};
