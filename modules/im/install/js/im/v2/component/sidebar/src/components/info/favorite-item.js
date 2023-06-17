import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import type {ImModelSidebarFavoriteItem, ImModelMessage} from 'im.v2.model';
import {Parser} from 'im.v2.lib.parser';
import '../../css/info/favorite-item.css';
import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';

// @vue/component
export const FavoriteItem = {
	name: 'FavoriteItem',
	components: {Avatar, ChatTitle},
	props:
	{
		favorite: {
			type: Object,
			required: true
		},
		chatId: {
			type: Number,
			required: true
		},
		dialogId: {
			type: String,
			required: true
		},
	},
	emits: ['contextMenuClick'],
	data() {
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
			return Parser.purifyMessage(this.favoriteMessage);
		}
	},
	methods:
	{
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				id: this.favoriteItem.id,
				messageId: this.favorite.messageId,
				target: event.currentTarget
			});
		},
		onItemClick()
		{
			EventEmitter.emit(EventType.dialog.goToMessageContext, {
				messageId: this.favorite.messageId,
				dialogId: this.dialogId,
			});
		},
		onMessageBodyClick(event)
		{
			if (event.target.tagName === 'A')
			{
				event.stopPropagation();
			}
		}
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
					<Avatar
						:size="AvatarSize.XS"
						:withStatus="false"
						:dialogId="authorDialogId"
						class="bx-im-favorite-item__author-avatar"
					/>
					<ChatTitle :dialogId="authorDialogId" :showItsYou="false" class="bx-im-favorite-item__author-text" />
				</div>
				<button 
					v-if="showContextButton"
					class="bx-im-messenger__context-menu-icon"
					@click.stop="onContextMenuClick"
				></button>
			</div>
			<div class="bx-im-favorite-item__message-text" v-html="messageText" @click="onMessageBodyClick"></div>
		</div>
	`
};