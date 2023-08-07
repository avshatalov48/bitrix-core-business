// @vue/component
import { Parser } from 'im.v2.lib.parser';

export const TextExtension = {
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	data()
	{
		return {};
	},
	computed:
	{
		formattedText(): string
		{
			return Parser.decodeMessage(this.item);
		},
	},
	template: `
		<div class="bx-im-message-base__text" v-html="formattedText"></div>
	`,
};
