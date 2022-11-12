import {EventEmitter} from 'main.core.events';
import {RecentList as RecentListComponent} from 'im.v2.component.old-chat-embedding.recent-list';
import {Search as SearchComponent} from 'im.v2.component.old-chat-embedding.search';
import {EventType} from 'im.v2.const';

// @vue/component
export const LeftPanel = {
	components: {RecentListComponent, SearchComponent},
	data: function()
	{
		return {
			searchMode: false,
			searchQuery: '',
		};
	},
	created()
	{
		this.registerSearchEvents();
	},
	beforeUnmount()
	{
		this.unregisterSearchEvents();
	},
	methods:
	{
		registerSearchEvents()
		{
			this.onOpenSearchHandler = this.onOpenSearch.bind(this);
			this.onUpdateSearchHandler = this.onUpdateSearch.bind(this);
			this.onCloseSearchHandler = this.onCloseSearch.bind(this);
			EventEmitter.subscribe(EventType.recent.openSearch, this.onOpenSearchHandler);
			EventEmitter.subscribe(EventType.recent.updateSearch, this.onUpdateSearchHandler);
			EventEmitter.subscribe(EventType.recent.closeSearch, this.onCloseSearchHandler);
		},
		unregisterSearchEvents()
		{
			EventEmitter.unsubscribe(EventType.recent.openSearch, this.onOpenSearchHandler);
			EventEmitter.unsubscribe(EventType.recent.updateSearch, this.onUpdateSearchHandler);
			EventEmitter.unsubscribe(EventType.recent.closeSearch, this.onCloseSearchHandler);
		},
		onOpenSearch(event)
		{
			if (this.searchMode)
			{
				return;
			}
			this.searchMode = true;
			this.searchQuery = event.data.query;
		},
		onUpdateSearch(event)
		{
			this.searchMode = true;
			this.searchQuery = event.data.query;
		},
		onCloseSearch()
		{
			this.searchQuery = '';
			this.searchMode = false;
		},
	},
	template: `
		<div class="bx-im-left-panel-wrap">
			<SearchComponent v-show="searchMode" :searchMode="searchMode" :searchQuery="searchQuery" />
			<RecentListComponent v-show="!searchMode" />
		</div>
	`
};