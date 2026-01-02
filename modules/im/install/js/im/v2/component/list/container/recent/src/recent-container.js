import { EventEmitter } from 'main.core.events';
import { Event as CoreEvent } from 'main.core';

import { PermissionManager } from 'im.v2.lib.permission';
import { RecentList } from 'im.v2.component.list.items.recent';
import { ChatSearchInput, ChatSearch } from 'im.v2.component.search';
import { Layout, EventType, ActionByUserType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';

import { HeaderMenu } from './components/header-menu';
import { CreateChatMenu } from './components/create-chat-menu/create-chat-menu';

import './css/recent-container.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const RecentListContainer = {
	name: 'RecentListContainer',
	components: { HeaderMenu, CreateChatMenu, ChatSearchInput, RecentList, ChatSearch },
	emits: ['selectEntity'],
	data(): JsonObject
	{
		return {
			searchMode: false,
			unreadOnlyMode: false,
			searchQuery: '',
			isSearchLoading: false,
		};
	},
	computed:
	{
		canCreateChat(): boolean
		{
			const actions = [
				ActionByUserType.createChat,
				ActionByUserType.createCollab,
				ActionByUserType.createChannel,
				ActionByUserType.createConference,
			];

			return actions.some((action) => PermissionManager.getInstance().canPerformActionByUserType(action));
		},
	},
	created()
	{
		Logger.warn('List: Recent container created');

		EventEmitter.subscribe(EventType.recent.openSearch, this.onOpenSearch);
		CoreEvent.bind(document, 'mousedown', this.onDocumentClick);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.recent.openSearch, this.onOpenSearch);
		CoreEvent.unbind(document, 'mousedown', this.onDocumentClick);
	},
	methods:
	{
		onChatClick(dialogId)
		{
			this.$emit('selectEntity', { layoutName: Layout.chat, entityId: dialogId });
		},
		onOpenSearch()
		{
			this.searchMode = true;
		},
		onCloseSearch()
		{
			this.searchMode = false;
			this.searchQuery = '';
		},
		onUpdateSearch(query)
		{
			this.searchMode = true;
			this.searchQuery = query;
		},
		onDocumentClick(event: Event)
		{
			const clickOnRecentContainer = event.composedPath().includes(this.$refs['recent-container']);
			if (this.searchMode && !clickOnRecentContainer)
			{
				EventEmitter.emit(EventType.search.close);
			}
		},
		onLoading(value: boolean)
		{
			this.isSearchLoading = value;
		},
	},
	template: `
		<div class="bx-im-list-container-recent__scope bx-im-list-container-recent__container" ref="recent-container">
			<div class="bx-im-list-container-recent__header_container">
				<HeaderMenu @showUnread="unreadOnlyMode = true" />
				<div class="bx-im-list-container-recent__search-input_container">
					<ChatSearchInput 
						:searchMode="searchMode" 
						:isLoading="searchMode && isSearchLoading"
						@openSearch="onOpenSearch"
						@closeSearch="onCloseSearch"
						@updateSearch="onUpdateSearch"
					/>
				</div>
				<CreateChatMenu v-if="canCreateChat" />
			</div>
			<div class="bx-im-list-container-recent__elements_container">
				<div class="bx-im-list-container-recent__elements">
					<ChatSearch 
						v-show="searchMode" 
						:searchMode="searchMode"
						:query="searchQuery"
						@loading="onLoading"
					/>
					<RecentList v-show="!searchMode && !unreadOnlyMode" @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`,
};
