import { Type } from 'main.core';

import { BaseMessage } from 'im.v2.component.message.base';
import { DefaultMessageContent } from 'im.v2.component.message.elements';

import './css/unsupported.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const UnsupportedMessage = {
	name: 'UnsupportedMessage',
	components: { BaseMessage, DefaultMessageContent },
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
			<div class="bx-im-message-unsupported__container bx-im-message-unsupported__scope">
				<div class="bx-im-message-unsupported__content">
					<div class="bx-im-message-unsupported__icon"></div>
					<div class="bx-im-message-unsupported__text">
						{{ loc('IM_MESSENGER_MESSAGE_UNSUPPORTED_EXTENSION') }}
					</div>
				</div>
				<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" />
			</div>
		</BaseMessage>
	`,
};
