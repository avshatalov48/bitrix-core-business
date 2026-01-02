import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';
import { FloatButton, FloatButtonColor, FloatButtonIcon } from './float-button';

// @vue/component
export const ScrollButton = {
	name: 'ScrollButton',
	components: {
		FloatButton,
	},
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
		floatButtonProps(): { color: string, icon: string, counter: number }
		{
			return {
				color: FloatButtonColor.accent,
				icon: FloatButtonIcon.chevronDown,
				counter: this.dialog.counter,
			};
		},
	},
	template: `
		<FloatButton v-bind="floatButtonProps" />
	`,
};
