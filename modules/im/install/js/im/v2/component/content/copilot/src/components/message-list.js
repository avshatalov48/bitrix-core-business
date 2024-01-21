import { BitrixVue } from 'ui.vue3';

import { MessageList } from 'im.v2.component.message-list';

import { DialogStatus } from './dialog-status';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const CopilotMessageList = BitrixVue.cloneComponent(MessageList, {
	name: 'CopilotMessageList',
	components: { DialogStatus },
	computed:
	{
		statusComponent(): BitrixVueComponentProps
		{
			return DialogStatus;
		},
	},
	methods:
	{
		initContextMenu()
		{},
		onMessageContextMenuClick()
		{},
		onAvatarClick(dialogId: string, event: PointerEvent)
		{},
	},
});
