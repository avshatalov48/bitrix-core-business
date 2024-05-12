import { EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { Loader } from 'im.v2.component.elements';
import { EventType, SidebarDetailBlock } from 'im.v2.const';

import { ChatItem } from './chat-item';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';
import { ChatsWithUser } from '../../../classes/panels/chats-with-user';

import './css/chats-with-user-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatsWithUserPanel = {
	name: 'ChatsWithUserPanel',
	components: { DetailHeader, ChatItem, DetailEmptyState, Loader },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
			chats: [],
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		isEmptyState(): boolean
		{
			return !this.isLoading && this.chats.length === 0;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	watch:
	{
		dialogId()
		{
			this.chats = [];
			this.service = new ChatsWithUser({ dialogId: this.dialogId });
			void this.loadFirstPage();
		},
	},
	created()
	{
		this.service = new ChatsWithUser({ dialogId: this.dialogId });
		void this.loadFirstPage();
	},
	methods:
	{
		onClick(event)
		{
			const { dialogId } = event;

			void Messenger.openChat(dialogId);
		},
		async loadFirstPage()
		{
			this.isLoading = true;
			this.chats = await this.service.loadFirstPage();
			this.isLoading = false;
		},
		needToLoadNextPage(event): boolean
		{
			const target = event.target;

			return target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
		},
		async onScroll(event)
		{
			if (this.isLoading)
			{
				return;
			}

			if (!this.needToLoadNextPage(event) || !this.service.hasMoreItemsToLoad)
			{
				return;
			}

			this.isLoading = true;
			const nextPageChats = await this.service.loadNextPage();
			this.chats = [...this.chats, ...nextPageChats];
			this.isLoading = false;
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.chatsWithUser });
		},
		loc(phrase: string): string
		{
			return this.$Bitrix.Loc.getMessage(phrase);
		},
	},
	template: `
		<div class="bx-im-sidebar-chats-with-user-detail__scope">
			<DetailHeader
				:title="loc('IM_SIDEBAR_CHATSWITHUSER_DETAIL_TITLE')"
				:dialogId="dialogId"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div 
				class="bx-im-sidebar-chats-with-user-detail__container" 
				@scroll="onScroll"
			>
				<ChatItem
					v-for="chat in chats"
					:dialogId="chat.dialogId"
					:dateMessage="chat.dateMessage"
					@clickItem="onClick"
				/>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="loc('IM_SIDEBAR_CHATS_WITH_USER_EMPTY')"
					:iconType="SidebarDetailBlock.messageSearch"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-chats-with-user-detail__loader-container" />
			</div>
		</div>
	`,
};
