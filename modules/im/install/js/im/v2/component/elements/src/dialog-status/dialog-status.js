import { Text, type JsonObject } from 'main.core';

import { ChatType } from 'im.v2.const';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import { AdditionalUsers } from './additional-users';

import './css/dialog-status.css';

import type { ImModelChat } from 'im.v2.model';

type LastMessageViews = {
	countOfViewers: number,
	firstViewer?: {
		userId: number,
		userName: string,
		date: Date
	},
	messageId: number
};

const TYPING_USERS_COUNT = 3;
const MORE_USERS_CSS_CLASS = 'bx-im-dialog-chat-status__user-count';

// @vue/component
export const DialogStatus = {
	components: { AdditionalUsers },
	props: {
		dialogId: {
			required: true,
			type: String,
		},
	},
	data(): JsonObject
	{
		return {
			showAdditionalUsers: false,
			additionalUsersLinkElement: null,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		typingStatus(): string
		{
			if (!this.dialog.inited || this.dialog.writingList.length === 0)
			{
				return '';
			}

			const firstTypingUsers = this.dialog.writingList.slice(0, TYPING_USERS_COUNT);
			const text = firstTypingUsers.map((element) => element.userName).join(', ');
			const remainingUsersCount = this.dialog.writingList.length - TYPING_USERS_COUNT;
			if (remainingUsersCount > 0)
			{
				return this.loc('IM_ELEMENTS_STATUS_TYPING_PLURAL_MORE', {
					'#USERS#': text,
					'#COUNT#': remainingUsersCount,
				});
			}

			if (this.dialog.writingList.length > 1)
			{
				return this.loc('IM_ELEMENTS_STATUS_TYPING_PLURAL', {
					'#USERS#': text,
				});
			}

			return this.loc('IM_ELEMENTS_STATUS_TYPING', { '#USER#': text });
		},
		readStatus(): string
		{
			if (!this.dialog.inited)
			{
				return '';
			}

			if (this.lastMessageViews.countOfViewers === 0)
			{
				return '';
			}

			if (this.isUser)
			{
				return this.formatUserViewStatus();
			}

			return this.formatChatViewStatus();
		},
		lastMessageViews(): LastMessageViews
		{
			return this.dialog.lastMessageViews;
		},
	},
	methods:
	{
		formatUserViewStatus(): string
		{
			const { date } = this.lastMessageViews.firstViewer;

			return this.loc('IM_ELEMENTS_STATUS_READ_USER_MSGVER_1', {
				'#DATE#': DateFormatter.formatByTemplate(date, DateTemplate.messageReadStatus),
			});
		},
		formatChatViewStatus(): string
		{
			const { countOfViewers, firstViewer } = this.lastMessageViews;
			if (countOfViewers === 1)
			{
				return this.loc('IM_ELEMENTS_STATUS_READ_CHAT', {
					'#USER#': Text.encode(firstViewer.userName),
				});
			}

			return this.loc('IM_ELEMENTS_STATUS_READ_CHAT_PLURAL', {
				'#USERS#': Text.encode(firstViewer.userName),
				'#LINK_START#': `<span class="${MORE_USERS_CSS_CLASS}" ref="moreUsersLink">`,
				'#COUNT#': countOfViewers - 1,
				'#LINK_END#': '</span>',
			});
		},
		onClick(event: PointerEvent)
		{
			if (!event.target.matches(`.${MORE_USERS_CSS_CLASS}`))
			{
				return;
			}

			this.onMoreUsersClick();
		},
		onMoreUsersClick()
		{
			this.additionalUsersLinkElement = document.querySelector(`.${MORE_USERS_CSS_CLASS}`);
			this.showAdditionalUsers = true;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div @click="onClick" class="bx-im-dialog-chat-status__container">
			<div v-if="typingStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --typing"></div>
				<div class="bx-im-dialog-chat-status__text">{{ typingStatus }}</div>
			</div>
			<div v-else-if="readStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --read"></div>
				<div v-html="readStatus" class="bx-im-dialog-chat-status__text"></div>
			</div>
			<AdditionalUsers
				:dialogId="dialogId"
				:show="showAdditionalUsers"
				:bindElement="additionalUsersLinkElement || {}"
				@close="showAdditionalUsers = false"
			/>
		</div>
	`,
};
