import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import './css/multidialog-item.css';

import type { ImModelChat, ImModelSidebarMultidialogItem } from 'im.v2.model';

// @vue/component
export const MultidialogItem = {
	name: 'MultidialogItem',
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		multidialogItem(): ImModelSidebarMultidialogItem
		{
			return this.item;
		},
		dialogId(): string
		{
			return this.multidialogItem.dialogId;
		},
		chatId(): string
		{
			return this.multidialogItem.chatId;
		},
		title(): string
		{
			const chat: ImModelChat = this.$store.getters['chats/get'](this.dialogId);

			return chat.name;
		},
		status(): string
		{
			return this.multidialogItem.status;
		},
		transferredStatus(): string
		{
			const code = `IM_SIDEBAR_SUPPORT_TICKET_STATUS_${this.status.toUpperCase()}`;

			return this.loc(code);
		},
		containerClasses(): string[]
		{
			const status = `--${this.status}`;
			const chatIsOpened = this.$store.getters['application/isChatOpen'](this.dialogId);

			return [status, { '--selected': chatIsOpened }];
		},
		counter(): number
		{
			const counter = this.$store.getters['counters/getChatCounterByChatId'](this.chatId) ?? 0;

			return counter > 99 ? '99+' : counter;
		},
		formatDate(): string
		{
			const date = this.multidialogItem.date;

			return DateFormatter.formatByTemplate(date, DateTemplate.recent);
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			class="bx-im-multidialog-item__container bx-im-sidebar-multidialog-preview__scope"
		 	:class="containerClasses"
			:title="title"
		>
			<span class="bx-im-multidialog-item__title">{{ title }}</span>
			<span class="bx-im-multidialog-item__date">
				{{ formatDate }}
			</span>
			<div class="bx-im-multidialog-item__status">
				{{ transferredStatus }}
			</div>
			<div v-show="counter" class="bx-im-multidialog-item__count bx-im-sidebar-multidialog-preview__new-message-counter">
				{{ counter }}
			</div>
		</div>
	`,
};
