import { Parser } from 'im.v2.lib.parser';

import '../css/message-panel.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const EditPanel = {
	name: 'EditPanel',
	props:
	{
		messageId: {
			type: [Number, String],
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
		preparedText(): string
		{
			return Parser.purifyMessage(this.message);
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
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon"></div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ loc('IM_TEXTAREA_EDIT_MESSAGE_TITLE') }}</div>
				<div class="bx-im-message-panel__text">{{ preparedText }}</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`,
};
