import { CallButton } from 'call.component.call-button';
import { Extension } from 'main.core';
import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelChat } from 'im.v2.model';

const { callInstalled } = Extension.getSettings('im.v2.lib.call');

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
			if (!callInstalled)
			{
				return null;
			}

			return CallButton;
		},
	},
	template: `
		<component v-if="componentToRender" :is="componentToRender" :dialog="dialog" :compactMode="compactMode" />
	`,
};