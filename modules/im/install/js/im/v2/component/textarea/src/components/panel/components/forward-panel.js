import { Loc, Text } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Parser } from 'im.v2.lib.parser';

import '../css/message-panel.css';

import type { ImModelMessage, ImModelUser } from 'im.v2.model';
import type { PanelContext } from 'im.v2.provider.service';

type MultiForwardPanelContext = PanelContext & {
	messagesIds: number[],
}

const MESSAGE_DISPLAY_LIMIT = 5;

// @vue/component
export const ForwardPanel = {
	name: 'ForwardPanel',
	props:
	{
		context: {
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		forwardContext(): MultiForwardPanelContext
		{
			return this.context;
		},
		messagesIds(): number[]
		{
			return this.forwardContext.messagesIds;
		},
		sortedMessagesIds(): number[]
		{
			return [...this.messagesIds].sort();
		},
		authorsOfMessages(): ImModelUser[]
		{
			return this.sortedMessagesIds.map((id) => {
				const isForward = this.$store.getters['messages/isForward'](id);
				const message = this.getMessage(id);
				const userId: number = isForward ? message.forward.userId : message.authorId;

				return this.$store.getters['users/get'](userId, true);
			});
		},
		uniqueUsers(): ImModelUser[]
		{
			const uniqueUsersObj = {};

			this.authorsOfMessages.forEach((user) => {
				if (!uniqueUsersObj[user.id])
				{
					uniqueUsersObj[user.id] = user;
				}
			});

			return Object.values(uniqueUsersObj);
		},
		forwardMessagesCount(): number
		{
			return this.messagesIds.length;
		},
		forwardAuthorName(): string
		{
			const author = this.authorsOfMessages[0];
			let name = author.name;

			if (author.id === 0)
			{
				name = this.loc('IM_TEXTAREA_FORWARD_SYSTEM');
			}

			return `${name}: `;
		},
		displayedAuthorNames(): string
		{
			const systemMessagesCount = this.authorsOfMessages.filter((user) => user.id === 0).length;
			const displayedNames = this.uniqueUsers.slice(0, MESSAGE_DISPLAY_LIMIT);

			const names = [];

			displayedNames.forEach((user) => {
				if (user.id === 0)
				{
					return systemMessagesCount > 1 ? names.push(this.loc('IM_TEXTAREA_FORWARD_MESSAGES_SYSTEM')) : names.push(this.loc('IM_TEXTAREA_FORWARD_SYSTEM'));
				}

				if (this.isOwnMessage(user))
				{
					return names.unshift(this.loc('IM_TEXTAREA_FORWARD_OWN_MESSAGE'));
				}

				return names.push(user.firstName);
			});

			return names.join(', ');
		},
		formattedAuthorNames(): string
		{
			if (this.remainingAuthors > 0)
			{
				return Loc.getMessage('IM_TEXTAREA_FORWARD_TEXT_MORE', {
					'[name]': '<span class="bx-im-message-panel__forward-author_name">',
					'[/name]': '</span>',
					'#USER_LIST#': Text.encode(this.displayedAuthorNames),
					'[remaining]': '<span class="bx-im-message-panel__forward-author_remaining">',
					'[/remaining]': '</span>',
					'#COUNT#': this.remainingAuthors,
				});
			}

			return this.loc('IM_TEXTAREA_FORWARD_TEXT', {
				'#USER_LIST#': Text.encode(this.displayedAuthorNames),
			});
		},
		remainingAuthors(): number
		{
			return this.uniqueUsers.length - MESSAGE_DISPLAY_LIMIT;
		},
		messageText(): string
		{
			return Parser.purifyMessage(this.getMessage(this.messagesIds));
		},
		titleText(): string
		{
			if (this.forwardMessagesCount > 1)
			{
				return this.formattedMessageCounter;
			}

			return this.loc('IM_TEXTAREA_FORWARD_TITLE');
		},
		formattedMessageCounter(): string
		{
			return Loc.getMessagePlural('IM_TEXTAREA_FORWARD_TITLE_MULTIPLE_COUNT', this.forwardMessagesCount, {
				'#COUNT_MESSAGES#': this.forwardMessagesCount,
			});
		},
	},
	methods:
	{
		isOwnMessage(user: ImModelUser): boolean
		{
			return user.id === Core.getUserId() && this.uniqueUsers.length > 1;
		},
		getMessage(messageId: number): ImModelMessage
		{
			return this.$store.getters['messages/getById'](messageId);
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon --forward"></div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ titleText }}</div>
				<div v-if="forwardMessagesCount > 1" class="bx-im-message-panel__text" :class="{'--compact': remainingAuthors > 0}">
					<div class="bx-im-message-panel__bulk-forward-author" v-html="formattedAuthorNames"></div>
				</div>
				<div v-else class="bx-im-message-panel__text">
					<span class="bx-im-message-panel__forward-author">{{ forwardAuthorName }}</span>
					<span class="bx-im-message-panel__forward-message-text">{{ messageText }}</span>
				</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`,
};
