import { RetryContextMenu } from './classes/retry-context-menu';

import './retry-button.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const RetryButton = {
	name: 'RetryButton',
	props:
	{
		message: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		messageItem(): ImModelMessage
		{
			return this.message;
		},
		menuTitle(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_MESSENGER_MESSAGE_CONTEXT_MENU_RETRY');
		},
	},
	created()
	{
		this.contextMenu = new RetryContextMenu();
	},
	methods:
	{
		onClick(event)
		{
			const context = { dialogId: this.dialogId, ...this.messageItem };
			this.contextMenu.openMenu(context, event.currentTarget);
		},
	},
	template: `
		<div class="bx-im-message-retry-button__container bx-im-message-retry-button__scope">
			<button
				:title="menuTitle"
				@click="onClick"
				class="bx-im-message-retry-button__arrow"
			></button>
		</div>
	`,
};
