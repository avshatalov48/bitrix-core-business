import {EventEmitter} from 'main.core.events';
import {Event} from 'main.core';

import {RecentList} from 'im.v2.component.list.element-list.recent';
import {SearchInput} from 'im.v2.component.search.search-input';
import {SearchResult} from 'im.v2.component.search.search-result';
import {Layout, EventType} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';
import {UnreadRecentService} from 'im.v2.provider.service';

import {HeaderMenu} from './components/header-menu';
import {CreateChatMenu} from './components/create-chat-menu/create-chat-menu';

import './css/recent-container.css';

// @vue/component
export const RecentListContainer = {
	name: 'RecentListContainer',
	components: {HeaderMenu, CreateChatMenu, SearchInput, SearchResult, RecentList},
	emits: ['selectEntity'],
	data()
	{
		return {
			searchMode: false,
			unreadOnlyMode: false,
			searchQuery: ''
		};
	},
	computed:
	{
		UnreadRecentService: () => UnreadRecentService,
	},
	created()
	{
		Logger.warn('List: Recent container created');

		EventEmitter.subscribe(EventType.recent.openSearch, this.onOpenSearch);
		Event.bind(document, 'mousedown', this.onDocumentClick);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.recent.openSearch, this.onOpenSearch);
		Event.unbind(document, 'mousedown', this.onDocumentClick);
	},
	methods:
	{
		onChatClick(dialogId)
		{
			this.$emit('selectEntity', {layoutName: Layout.chat.name, entityId: dialogId});
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
			if (!clickOnRecentContainer)
			{
				EventEmitter.emit(EventType.search.close);
			}
		},
	},
	template: `
		<div class="bx-im-list-container-recent__scope bx-im-list-container-recent__container" ref="recent-container">
			<div class="bx-im-list-container-recent__header_container">
				<HeaderMenu @showUnread="unreadOnlyMode = true" />
				<div class="bx-im-list-container-recent__search-input_container">
					<SearchInput 
						:searchMode="searchMode" 
						@openSearch="onOpenSearch"
						@closeSearch="onCloseSearch"
						@updateSearch="onUpdateSearch"
					/>
				</div>
				<CreateChatMenu />
			</div>
			<div class="bx-im-list-container-recent__elements_container">
				<div class="bx-im-list-container-recent__elements">
					<SearchResult 
						v-show="searchMode" 
						:searchMode="searchMode" 
						:searchQuery="searchQuery" 
						:searchConfig="{}"
					/>
					<RecentList v-show="!searchMode && !unreadOnlyMode" @chatClick="onChatClick" key="recent" />
<!--					<RecentList-->
<!--						v-if="!searchMode && unreadOnlyMode"-->
<!--						:recentService="UnreadRecentService.getInstance()"-->
<!--						@chatClick="onChatClick"-->
<!--						key="unread"-->
<!--					/>-->
				</div>
			</div>
		</div>
	`
};