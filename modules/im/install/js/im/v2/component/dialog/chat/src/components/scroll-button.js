import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

// @vue/component
export const ScrollButton = {
	name: 'ScrollButton',
	props:
	{
		dialogId: {
			type: String,
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
		formattedCounter(): string
		{
			if (this.dialog.counter === 0)
			{
				return '';
			}

			if (this.dialog.counter > 99)
			{
				return '99+';
			}

			return String(this.dialog.counter);
		},
	},
	template: `
		<div class="bx-im-dialog-chat__scroll-button">
			<div v-if="dialog.counter" class="bx-im-dialog-chat__scroll-button_counter">
				{{ formattedCounter }}
			</div>
		</div>
	`,
};
