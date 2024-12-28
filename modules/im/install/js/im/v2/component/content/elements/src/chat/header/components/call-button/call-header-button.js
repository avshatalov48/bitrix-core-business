import { CallButton } from 'call.component.call-button';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CallHeaderButton = {
	name: 'CallHeaderButton',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		compactMode: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		componentToRender(): BitrixVueComponentProps
		{
			return CallButton;
		},
	},
	template: `
		<component v-if="componentToRender" :is="componentToRender" :dialog="dialog" :compactMode="compactMode" />
	`,
};
