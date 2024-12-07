import { EventEmitter } from 'main.core.events';

import { EventType, SidebarDetailBlock } from 'im.v2.const';

import '../css/multidialog.css';

// @vue/component
export const MultidialogPreview = {
	name: 'MultidialogPreview',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		chatId(): number
		{
			return this.$store.getters['chats/get'](this.dialogId, true).chatId;
		},
		numberRequests(): number
		{
			const chatsCount = this.$store.getters['sidebar/multidialog/getChatsCount'];

			return chatsCount > 999 ? '999+' : chatsCount;
		},
		totalChatCounter(): number
		{
			const counter = this.$store.getters['sidebar/multidialog/getTotalChatCounter'];

			return counter > 99 ? '99+' : counter;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onOpenDetail()
		{
			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.multidialog,
				dialogId: this.dialogId,
				standalone: true,
			});
		},
	},
	template: `
		<div class="bx-im-sidebar-multidialog-preview__scope">
			<div class="bx-im-sidebar-multidialog-preview__container" @click="onOpenDetail">
				<div class="bx-im-sidebar-multidialog-preview__questions-container">
					<div class="bx-im-sidebar-multidialog-preview__questions-text">
						{{ loc('IM_SIDEBAR_SUPPORT_TICKET_TITLE') }}
					</div>
					<div class="bx-im-sidebar-multidialog-preview__questions-count">
						{{ numberRequests }}
					</div>
				</div>
				<div class="bx-im-sidebar-multidialog-preview__new-message-container">
					<div v-if="totalChatCounter" class="bx-im-sidebar-multidialog-preview__new-message-counter">
						{{ totalChatCounter }}
					</div>
					<div class="bx-im-sidebar__forward-icon" />
				</div>
			</div>
		</div>
	`,
};
