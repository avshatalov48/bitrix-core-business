import { EventEmitter } from 'main.core.events';
import { Text } from 'main.core';

import { ChatType, EventType } from 'im.v2.const';
import { Avatar, AvatarSize, ChatTitleWithHighlighting } from 'im.v2.component.elements';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';
import { highlightText } from 'im.v2.lib.text-highlighter';

import '../css/search-experimental-item.css';

import type { ImModelChat, ImModelRecentItem, ImModelUser } from 'im.v2.model';

// @vue/component
export const SearchExperimentalItem = {
	name: 'SearchExperimentalItem',
	components: { Avatar, ChatTitleWithHighlighting },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		withDate: {
			type: Boolean,
			default: false,
		},
		query: {
			type: String,
			default: '',
		},
	},
	emits: ['clickItem'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		recentItem(): ImModelRecentItem
		{
			return this.$store.getters['recent/get'](this.dialogId);
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		position(): string
		{
			if (!this.isUser)
			{
				return '';
			}

			return this.user.workPosition;
		},
		userItemText(): string
		{
			if (!this.position)
			{
				return this.loc('IM_SEARCH_EXPERIMENTAL_ITEM_USER_TYPE_GROUP_V2');
			}

			return highlightText(Text.encode(this.position), this.query);
		},
		chatItemText(): string
		{
			if (this.isFoundByUser)
			{
				return `<span class="--highlight">${this.loc('IM_SEARCH_EXPERIMENTAL_ITEM_FOUND_BY_USER')}</span>`;
			}

			return this.loc('IM_SEARCH_EXPERIMENTAL_ITEM_CHAT_TYPE_GROUP_V2');
		},
		chatItemTextForTitle(): string
		{
			if (this.isFoundByUser)
			{
				return this.loc('IM_SEARCH_EXPERIMENTAL_ITEM_FOUND_BY_USER');
			}

			return this.loc('IM_SEARCH_EXPERIMENTAL_ITEM_CHAT_TYPE_GROUP_V2');
		},
		itemText(): string
		{
			return this.isUser ? this.userItemText : this.chatItemText;
		},
		itemTextForTitle(): string
		{
			return this.isUser ? this.position : this.chatItemTextForTitle;
		},
		formattedDate(): string
		{
			if (!this.recentItem.message.date)
			{
				return '';
			}

			return this.formatDate(this.recentItem.message.date);
		},
		isFoundByUser(): boolean
		{
			const searchRecentItem = this.$store.getters['recent/search/get'](this.dialogId);
			if (!searchRecentItem)
			{
				return false;
			}

			return Boolean(searchRecentItem.foundByUser);
		},
	},
	methods:
	{
		onClick(event)
		{
			this.$emit('clickItem', {
				dialogId: this.dialogId,
				nativeEvent: event,
			});
		},
		onRightClick(event)
		{
			if (event.altKey && event.shiftKey)
			{
				return;
			}

			const item = { dialogId: this.dialogId };
			EventEmitter.emit(EventType.search.openContextMenu, { item, nativeEvent: event });
		},
		formatDate(date: Date): string
		{
			return DateFormatter.formatByTemplate(date, DateTemplate.recent);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div 
			@click="onClick" 
			@click.right.prevent="onRightClick" 
			class="bx-im-search-experimental-item__container bx-im-search-experimental-item__scope"
		>
			<div class="bx-im-search-experimental-item__avatar-container">
				<Avatar :dialogId="dialogId" :size="AvatarSize.XL" />
			</div>
			<div class="bx-im-search-experimental-item__content-container">
				<div class="bx-im-search-experimental-item__content_header">
					<ChatTitleWithHighlighting :dialogId="dialogId" :textToHighlight="query" />
					<div v-if="withDate && formattedDate.length > 0" class="bx-im-search-experimental-item__date">
						<span>{{ formattedDate }}</span>
					</div>
				</div>
				<div class="bx-im-search-experimental-item__item-text" :title="itemTextForTitle" v-html="itemText"></div>
			</div>
		</div>
	`,
};
