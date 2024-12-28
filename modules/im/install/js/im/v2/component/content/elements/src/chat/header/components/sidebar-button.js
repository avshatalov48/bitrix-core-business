import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType, SidebarDetailBlock } from 'im.v2.const';

// @vue/component
export const SidebarButton = {
	name: 'SidebarButton',
	inject: ['currentSidebarPanel'],
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		isSidebarOpened(): boolean
		{
			return Type.isStringFilled(this.currentSidebarPanel);
		},
	},
	methods:
	{
		toggleRightPanel()
		{
			if (this.isSidebarOpened)
			{
				EventEmitter.emit(EventType.sidebar.close, { panel: '' });

				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.main,
				dialogId: this.dialogId,
			});
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			class="bx-im-chat-header__icon --panel"
			:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SIDEBAR')"
			:class="{'--active': isSidebarOpened}"
			@click="toggleRightPanel"
		></div>
	`,
};
