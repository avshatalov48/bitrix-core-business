import { Parser } from 'im.v2.lib.parser';

import './message.css';

import type { AttachMessageConfig } from 'im.v2.const';

// @vue/component
export const AttachMessage = {
	name: 'AttachMessage',
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
	},
	computed:
	{
		internalConfig(): AttachMessageConfig
		{
			return this.config;
		},
		message(): string
		{
			return Parser.decodeText(this.internalConfig.message);
		},
	},
	template: `
		<div class="bx-im-attach-message__container" v-html="message"></div>
	`,
};
