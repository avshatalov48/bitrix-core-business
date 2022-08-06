import {RecentUsersCarousel} from './component/recent-users-carousel';
import {SearchResultSection} from './component/search-result-section';
import {LoadingState} from './component/loading-state';
import {SearchService} from './search-service';

import './css/search.css';
import {BitrixVue} from 'ui.vue3';
import {Runtime, Extension} from 'main.core';

// @vue/component
export const Search = {
	components: {RecentUsersCarousel, SearchResultSection, LoadingState},
	props: {
		searchQuery: {
			type: String,
			required: true
		}
	},
	data: function()
	{
		return {
			minTokenSize: 3,
			isLoading: false,
			result: {
				recent: [],
				usersAndChats: [],
				departments: [],
			}
		};
	},
	computed:
	{
		showSearchResult()
		{
			return this.searchQuery.length > 0;
		},
		phrases()
		{
			return BitrixVue.getFilteredPhrases(this, 'IM_SEARCH_');
		},
	},
	watch:
	{
		searchQuery(value)
		{
			if (value.length > 0 && value.length < this.minTokenSize)
			{
				this.searchInLocal(value);
			}
			else if (value.length >= this.minTokenSize)
			{
				this.isLoading = true;
				this.searchInLocal(value);
				this.searchOnServerDelayed(value);
			}
			else
			{
				this.cleanSearchResult();
			}
		}
	},
	mounted()
	{
		this.isLoading = true;
		this.searchService.loadRecentSearch().then(recentDialogIdsCollection => {
			this.result.recent = recentDialogIdsCollection;
			this.isLoading = false;
		});
	},
	created()
	{
		this.minTokenSize = Extension.getSettings('im.v2.component.search').get('minTokenSize');
		this.searchService = new SearchService(this.$Bitrix);
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 1500, this);
	},
	methods:
	{
		cleanSearchResult()
		{
			this.result.usersAndChats = [];
			this.result.departments = [];
		},
		searchOnServer(query)
		{
			this.searchService.searchOnServer(query).then((searchResultFromServer: Object) => {
				searchResultFromServer.items.forEach(searchItem => {
					const exist = this.result.usersAndChats.includes(searchItem);
					if (!exist)
					{
						this.result.usersAndChats.push(searchItem);
					}
				});
				this.result.departments = searchResultFromServer.departments;
				this.isLoading = false;
			});
		},
		searchInLocal(query: string)
		{
			this.searchService.searchInCache(query).then((localSearchResult: Array<string>) => {
				this.result.usersAndChats = localSearchResult;
			});
		},
	},
	// language=Vue
	template: `
		<div class="bx-messenger-search">
			<div>
				<template v-if="!showSearchResult">
					<RecentUsersCarousel :title="phrases['IM_SEARCH_SECTION_EMPLOYEES']"/>
				</template>
				<template v-if="!showSearchResult">
					<SearchResultSection :items="result.recent" :title="phrases['IM_SEARCH_SECTION_RECENT']"/>
				</template>
				<template v-if="showSearchResult">
					<SearchResultSection 
						v-if="result.usersAndChats.length > 0" 
						:items="result.usersAndChats" 
						:title="phrases['IM_SEARCH_SECTION_USERS_AND_CHATS']"
					/>
					<template v-if="!isLoading && searchQuery.length >= 3">
						<SearchResultSection 
							v-if="result.departments.length > 0" 
							:items="result.departments" 
							:title="phrases['IM_SEARCH_SECTION_DEPARTMENTS']" 
							:showMore="result.departments.length > 5"
							:hasChildren="true"
						/>
					</template>
				</template>
				<loading-state v-if="isLoading" />
			</div>
		</div>
	`
};