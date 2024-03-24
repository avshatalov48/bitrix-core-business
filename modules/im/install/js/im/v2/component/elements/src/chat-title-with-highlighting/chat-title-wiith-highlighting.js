import { Text } from 'main.core';
import { BitrixVue } from 'ui.vue3';

import { highlightText } from 'im.v2.lib.text-highlighter';
import { ChatTitle } from '../registry';

// @vue/component
export const ChatTitleWithHighlighting = BitrixVue.cloneComponent(ChatTitle, {
	name: 'ChatTitleWithHighlighting',
	props: {
		textToHighlight: {
			type: String,
			default: '',
		},
	},
	computed:
		{
			dialogName(): string
			{
				// noinspection JSUnresolvedVariable
				return highlightText(this.parentDialogName, this.textToHighlight);
			},
		},
});
