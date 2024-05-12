import { BitrixVue } from 'ui.vue3';

import { MessageList } from 'im.v2.component.message-list';

import { CopilotDialogStatus } from './dialog-status';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const CopilotMessageList = BitrixVue.cloneComponent(MessageList, {
	name: 'CopilotMessageList',
	components: { CopilotDialogStatus },
	computed:
	{
		statusComponent(): BitrixVueComponentProps
		{
			return CopilotDialogStatus;
		},
	},
	methods:
	{
		onMessageContextMenuClick()
		{},
		onAvatarClick(params: { dialogId: string, $event: PointerEvent })
		{
			const copilotUserId = this.$store.getters['users/bots/getCopilotUserId'];
			if (copilotUserId.toString() === params.dialogId)
			{
				return;
			}
			// noinspection JSUnresolvedReference
			this.parentOnAvatarClick(params);
		},
	},
});
