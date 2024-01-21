import { BitrixVue } from 'ui.vue3';

import { ChatDialog } from 'im.v2.component.dialog.chat';

import { CopilotMessageList } from './message-list';

import '../css/dialog.css';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const CopilotDialog = BitrixVue.cloneComponent(ChatDialog, {
	name: 'CopilotDialog',
	computed:
	{
		messageListComponent(): BitrixVueComponentProps
		{
			return CopilotMessageList;
		},
	},
});
