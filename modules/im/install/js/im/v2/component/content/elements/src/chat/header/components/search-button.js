import { EventEmitter } from 'main.core.events';

import { EventType, SidebarDetailBlock } from 'im.v2.const';

// @vue/component
export const SearchButton = {
	name: 'SearchButton',
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
		isMessageSearchActive(): boolean
		{
			return this.currentSidebarPanel === SidebarDetailBlock.messageSearch;
		},
	},
	methods:
	{
		toggleSearchPanel()
		{
			if (this.isMessageSearchActive)
			{
				EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.messageSearch });

				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.messageSearch,
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
			:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SEARCH')"
			:class="{'--active': isMessageSearchActive}"
			class="bx-im-chat-header__icon --search"
			@click="toggleSearchPanel"
		></div>
	`,
};
