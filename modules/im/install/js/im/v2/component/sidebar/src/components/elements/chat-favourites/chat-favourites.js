import { EventEmitter } from 'main.core.events';

import { SidebarDetailBlock, EventType, Layout } from 'im.v2.const';

import './css/chat-favourites.css';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatFavourites = {
	name: 'ChatFavourites',
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
		favoriteCounter(): string
		{
			const counter = this.$store.getters['sidebar/favorites/getCounter'](this.chatId);

			return this.getCounterString(counter);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		isCopilotLayout(): boolean
		{
			const { name: currentLayoutName } = this.$store.getters['application/getLayout'];

			return currentLayoutName === Layout.copilot.name;
		},
	},
	methods:
	{
		getCounterString(counter: number): string
		{
			const MAX_COUNTER = 100;
			if (counter >= MAX_COUNTER)
			{
				return '99+';
			}

			return counter.toString();
		},
		onFavouriteClick()
		{
			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.favorite,
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
			class="bx-im-sidebar-chat-favourites__container" 
			:class="{'--copilot': isCopilotLayout}"
			@click="onFavouriteClick"
		>
			<div class="bx-im-sidebar-chat-favourites__title">
				<div class="bx-im-sidebar-chat-favourites__icon"></div>
				<div class="bx-im-sidebar-chat-favourites__title-text">
					{{ loc('IM_SIDEBAR_FAVORITE_DETAIL_TITLE') }}
				</div>
			</div>
			<div class="bx-im-sidebar-chat-favourites__counter-container">
				<span class="bx-im-sidebar-chat-favourites__counter">{{favoriteCounter}}</span>
			</div>
		</div>
	`,
};
