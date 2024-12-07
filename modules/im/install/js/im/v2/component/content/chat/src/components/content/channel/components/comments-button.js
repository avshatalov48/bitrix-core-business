import '../css/comments-button.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CommentsButton = {
	name: 'CommentsButton',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		counter: {
			type: Number,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
	},
	template: `
		<div class="bx-im-dialog-channel__comments-button">
			<div class="bx-im-dialog-channel__comments-button_counter">
				{{ counter }}
			</div>
		</div>
	`,
};
