import { type JsonObject } from 'main.core';

// @vue/component
export const DateGroupTitle = {
	props:
	{
		title: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	template: `
		<div class="bx-im-message-list-date-group-title__container">
			<div class="bx-im-message-list-date-group-title__text">{{ title }}</div>
		</div>
	`,
};
