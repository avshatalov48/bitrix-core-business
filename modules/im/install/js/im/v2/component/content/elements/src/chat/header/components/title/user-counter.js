import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { ChatType, EventType, SidebarDetailBlock } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';

const UserCounterPhraseCodeByChatType = {
	[ChatType.openChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	[ChatType.channel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	[ChatType.generalChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	default: 'IM_CONTENT_CHAT_HEADER_USER_COUNT',
};

// @vue/component
export const UserCounter = {
	name: 'UserCounter',
	inject: ['currentSidebarPanel', 'withSidebar'],
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		sidebarTooltipText(): string
		{
			return this.withSidebar ? this.loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS') : '';
		},
		isMembersPanelActive(): boolean
		{
			return this.currentSidebarPanel === SidebarDetailBlock.members;
		},
		needShowSubtitleCursor(): boolean
		{
			return this.withSidebar;
		},
		userCounterPhraseCode(): string
		{
			return UserCounterPhraseCodeByChatType[this.dialog.type] ?? UserCounterPhraseCodeByChatType.default;
		},
		userCounterText(): string
		{
			return Loc.getMessagePlural(this.userCounterPhraseCode, this.dialog.userCounter, {
				'#COUNT#': this.dialog.userCounter,
			});
		},
	},
	methods:
	{
		onMembersClick()
		{
			if (this.isMembersPanelActive)
			{
				EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.members });

				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.members,
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
			:title="sidebarTooltipText"
			@click="onMembersClick"
			class="bx-im-chat-header__subtitle_content"
			:class="{'--click': needShowSubtitleCursor}"
		>
			{{ userCounterText }}
		</div>
	`,
};
