import { EventEmitter } from 'main.core.events';
import { Text } from 'main.core';
import { EventType } from 'im.v2.const';
import { Parser } from 'im.v2.lib.parser';
import { MessageAvatar, AvatarSize, MessageAuthorTitle } from 'im.v2.component.elements';

import './css/favorite-item.css';

import type { ImModelSidebarFavoriteItem, ImModelMessage } from 'im.v2.model';
import { highlightText } from 'im.v2.lib.text-highlighter';

// @vue/component
export const FavoriteItem = {
	name: 'FavoriteItem',
	components: { MessageAvatar, MessageAuthorTitle },
	props:
	{
		favorite: {
			type: Object,
			required: true,
		},
		chatId: {
			type: Number,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		searchQuery: {
			type: String,
			default: '',
		},
	},
	emits: ['contextMenuClick'],
	data(): { showContextButton: boolean } {
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		favoriteItem(): ImModelSidebarFavoriteItem
		{
			return this.favorite;
		},
		favoriteMessage(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.favoriteItem.messageId);
		},
		authorDialogId(): string
		{
			return this.favoriteMessage.authorId.toString();
		},
		messageText(): string
		{
			const purifiedMessage = Parser.purifyMessage(this.favoriteMessage);
			const textToShow = Text.encode(purifiedMessage);

			if (this.searchQuery.length === 0)
			{
				return textToShow;
			}

			return highlightText(textToShow, this.searchQuery);
		},
		isCopilot(): boolean
		{
			return this.$store.getters['users/bots/isCopilot'](this.favoriteMessage.authorId);
		},
	},
	methods:
	{
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				id: this.favoriteItem.id,
				messageId: this.favorite.messageId,
				target: event.currentTarget,
			});
		},
		onItemClick()
		{
			EventEmitter.emit(EventType.dialog.goToMessageContext, {
				messageId: this.favorite.messageId,
				dialogId: this.dialogId,
			});
		},
	},
	template: `
		<div 
			class="bx-im-favorite-item__container bx-im-favorite-item__scope" 
			@click.stop="onItemClick"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-favorite-item__header-container">
				<div class="bx-im-favorite-item__author-container">
					<MessageAvatar
						:messageId="favoriteItem.messageId"
						:authorId="authorDialogId"
						:size="AvatarSize.XS"
						class="bx-im-favorite-item__author-avatar"
					/>
					<MessageAuthorTitle 
						:dialogId="authorDialogId"
						:messageId="favoriteItem.messageId"
						:withLeftIcon="!isCopilot"
						:showItsYou="false" 
						class="bx-im-favorite-item__author-text"
					/>
				</div>
				<button 
					v-if="showContextButton"
					class="bx-im-messenger__context-menu-icon"
					@click.stop="onContextMenuClick"
				></button>
			</div>
			<div class="bx-im-favorite-item__message-text" v-html="messageText"></div>
		</div>
	`,
};
