import {Parser} from 'im.v2.lib.parser';

import '../css/edit-panel.css';

import type {ImModelMessage} from 'im.v2.model';

// @vue/component
export const EditPanel = {
	name: 'EditPanel',
	props:
	{
		messageId: {
			type: Number,
			required: true
		},
	},
	emits: ['close'],
	data()
	{
		return {};
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
		preparedText(): string
		{
			return Parser.purifyMessage(this.message);
		}
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-edit-message__container">
			<div class="bx-im-edit-message__icon"></div>
			<div class="bx-im-edit-message__content">
				<div class="bx-im-edit-message__title">{{ loc('IM_TEXTAREA_EDIT_MESSAGE_TITLE') }}</div>
				<div class="bx-im-edit-message__text">{{ preparedText }}</div>
			</div>
			<div @click="$emit('close')" class="bx-im-edit-message__close"></div>
		</div>
	`
};