import { Messenger } from 'im.public';
import { SidebarDetailBlock } from 'im.v2.const';
import { Loader } from 'im.v2.component.elements';

import { DetailEmptyState } from '../detail-empty-state';
import { ChatItem } from './chat-item';

import '../../css/chat-with-user/detail.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const ChatsWithUserDetail = {
	name: 'ChatsWithUserDetail',
	components: { ChatItem, DetailEmptyState, Loader },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		chatId: {
			type: Number,
			required: true,
		},
		service: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
			chats: [],
			currentServerQueries: 0,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		isEmptyState(): boolean
		{
			return !this.isLoading && this.chats.length === 0;
		},
	},
	created()
	{
		this.loadFirstPage();
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
	},
	template: `
		<div class="bx-im-sidebar-chats-with-user-detail__container bx-im-sidebar-chats-with-user-detail__scope" @scroll="onScroll">
			<ChatItem
				v-for="chat in chats"
				:dialog-id="chat"
				@clickItem="onClick"
			/>
			<DetailEmptyState
				v-if="!isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_CHATS_WITH_USER_EMPTY')"
				:iconType="SidebarDetailBlock.messageSearch"
			/>
			<Loader v-if="isLoading" class="bx-im-sidebar-chats-with-user-detail__loader-container" />
		</div>
	`,
};
