import { EventEmitter } from 'main.core.events';
import { Text } from 'main.core';

import { DialogType, EventType } from 'im.v2.const';
import { Avatar, AvatarSize, ChatTitleWithHighlighting } from 'im.v2.component.elements';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';
import { highlightText } from 'im.v2.lib.text-highlighter';

import '../css/search-experimental-item.css';

import type { ImModelDialog, ImModelRecentItem, ImModelUser } from 'im.v2.model';

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
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
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
			return this.dialog.type === DialogType.user;
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
				return this.$Bitrix.Loc.getMessage('IM_SEARCH_EXPERIMENTAL_ITEM_USER_TYPE_GROUP_V2');
			}

			return highlightText(Text.encode(this.position), this.query);
		},
		chatItemText(): string
		{
			if (this.isUser)
			{
				return '';
			}

			return this.$Bitrix.Loc.getMessage('IM_SEARCH_EXPERIMENTAL_ITEM_CHAT_TYPE_GROUP_V2');
		},
		itemText(): string
		{
			return this.isUser ? this.userItemText : this.chatItemText;
		},
		itemTextForTitle(): string
		{
			return this.isUser ? this.position : this.chatItemText;
		},
		formattedDate(): string
		{
			if (!this.recentItem.dateUpdate)
			{
				return '';
			}

			return this.formatDate(this.recentItem.dateUpdate);
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
