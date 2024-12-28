import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import './context-menu.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const ContextMenu = {
	name: 'ContextMenu',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		message: {
			type: Object,
			required: true,
		},
		menuIsActiveForId: {
			type: [String, Number],
			default: 0,
		},
		showContextMenu: {
			type: Boolean,
			default: true,
		},
	},
	computed:
	{
		menuTitle(): string
		{
			return this.$Bitrix.Loc.getMessage(
				'IM_MESSENGER_MESSAGE_MENU_TITLE',
				{ '#SHORTCUT#': Utils.platform.isMac() ? 'CMD' : 'CTRL' },
			);
		},
		messageItem(): ImModelMessage
		{
			return this.message;
		},
		messageHasError(): boolean
		{
			return this.messageItem.error;
		},
		canShowContextMenu(): boolean
		{
			return this.showContextMenu && !this.messageHasError;
		},
		isBulkActionsMode(): boolean
		{
			return this.$store.getters['messages/select/getBulkActionsMode'];
		},
	},
	methods:
	{
		onMenuClick(event: PointerEvent)
		{
			EventEmitter.emit(EventType.dialog.onClickMessageContextMenu, {
				message: this.message,
				dialogId: this.dialogId,
				event,
			});
		},
	},
	template: `
		<template v-if="!isBulkActionsMode">
			<div v-if="canShowContextMenu" class="bx-im-message-context-menu__container bx-im-message-context-menu__scope">
				<button
					:title="menuTitle"
					@click="onMenuClick"
					@contextmenu.prevent
					:class="{'--active': menuIsActiveForId === message.id}"
					class="bx-im-message-context-menu__button"
				></button>
			</div>
			<div v-else class="bx-im-message-base__context-menu-placeholder"></div>
		</template>
	`,
};
