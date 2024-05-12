import {OpenlineList} from 'im.v2.component.list.items.openline';
import {SearchInput} from 'im.v2.component.search2.search-input';
import {SearchResult} from 'im.v2.component.search2.search-result';
import {Logger} from 'im.v2.lib.logger';

// @vue/component
export const OpenlineListContainer = {
	components: {OpenlineList, SearchInput, SearchResult},
	emits: ['selectEntity'],
	data()
	{
		return {
			searchMode: false,
			searchQuery: ''
		};
	},
	created()
	{
		Logger.warn('List: Openline container created');
	},
	methods:
	{
		onChatClick(dialogId)
		{
			this.$emit('selectEntity', {layoutName: 'openline', entityId: dialogId});
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
			this.searchQuery = query;
		}
	},
	template: `
		<SearchInput @openSearch="onOpenSearch" @closeSearch="onCloseSearch" @updateSearch="onUpdateSearch" />
		<SearchResult v-if="searchMode" :searchQuery="searchQuery" />
		<OpenlineList v-else @chatClick="onChatClick" />
	`
};