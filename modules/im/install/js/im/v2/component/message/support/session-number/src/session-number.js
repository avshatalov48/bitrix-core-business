import { DefaultMessage } from 'im.v2.component.message.default';

import './css/session-number.css';

import type { JsonObject } from 'main.core';
import type { ImModelMessage } from 'im.v2.model';

const SESSION_ID_PARAMS_KEY = 'imolSid';

// @vue/component
export const SupportSessionNumberMessage = {
	name: 'SupportSessionNumber',
	components: { DefaultMessage },
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
		withTitle: {
			type: Boolean,
			default: true,
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
		sessionNumberText(): string
		{
			return this.loc('IM_MESSAGE_SUPPORT_SESSION_NUMBER_TEXT', {
				'#SESSION_NUMBER#': this.sessionNumber,
			});
		},
		sessionNumber(): string
		{
			return this.message.componentParams?.[SESSION_ID_PARAMS_KEY];
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
		<DefaultMessage :item="item" :dialogId="dialogId" :withTitle="withTitle">
			<template #before-message>
				<div class="bx-im-message-support-session-number__container">
					<div class="bx-im-message-support-session-number__content">
						{{ sessionNumberText }}
					</div>
				</div>
			</template>
		</DefaultMessage>
	`,
};
