import 'ui.design-tokens';
import 'ui.fonts.opensans';

import {RecentUsersCarousel} from './components/recent-users-carousel';
import {SearchResultSection} from './components/search-result-section';
import {SearchResultOpenlineItem} from './components/search-result-openline-item';
import {SearchResultNetworkItem} from './components/search-result-network-item';
import {SearchResultDepartmentItem} from './components/search-result-department-item';
import {SearchResultItem} from './components/search-result-item';
import {RecentLoadingState as LoadingState} from 'im.v2.component.old-chat-embedding.elements';
import {SearchService} from './search-service';
import {SearchCache} from './search-cache';
import {SearchRecentList} from './search-recent-list';

import './css/search.css';
import {Runtime, Extension} from 'main.core';
import {SearchContextMenu} from './search-context-menu';
import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';
import {SearchUtils} from './search-utils';
import {SearchItem} from './search-item';

/**
* @bitrixEvents EventType.search.openContextMenu
* @bitrixEvents EventType.dialog.errors.accessDenied
* @bitrixEvents EventType.search.selectItem
* @bitrixEvents EventType.recent.updateSearch
*/
export const Search = {
	components: {
		RecentUsersCarousel,
		SearchResultSection,
		LoadingState,
		SearchResultOpenlineItem,
		SearchResultNetworkItem,
		SearchResultDepartmentItem,
		SearchResultItem
	},
	props: {
		searchQuery: {
			type: String,
			required: true
		},
		searchMode: {
			type: Boolean,
			required: true
		}
	},
	data: function()
	{
		return {
			isRecentLoading: false,
			isLocalLoading: false,
			isServerLoading: false,
			isNetworkLoading: false,
			currentServerQueries: 0,
			isNetworkButtonClicked: false,
			isNetworkAvailable: false,
			isNetworkSearchEnabled: true,
			result: {
				recent: new Map(),
				usersAndChats: new Map(),
				chatUsers: new Map(),
				departments: new Map(),
				openLines: new Map(),
				network: new Map(),
			}
		};
	},
	computed:
	{
		isEmptyState()
		{
			if (this.isServerLoading || this.isLocalLoading || this.isNetworkLoading)
			{
				return false;
			}

			if (this.isNetworkAvailable && !this.isNetworkButtonClicked && this.isServerSearch)
			{
				return false;
			}

			return this.result.usersAndChats.size === 0
				&& this.result.departments.size === 0
				&& this.result.chatUsers.size === 0
				&& this.result.openLines.size === 0
				&& this.result.network.size === 0;
		},
		isLoadingState()
		{
			return (this.isServerLoading || this.isRecentLoading);
		},
		isServerSearch()
		{
			return this.searchQuery.trim().length >= this.minTokenSize;
		},
		needToShowNetworkSection()
		{
			return !this.isNetworkButtonClicked || this.result.network.size > 0;
		},
		showSearchResult()
		{
			return this.searchQuery.trim().length > 0;
		},
		isNetworkSearchCode(): boolean
		{
			return !!(this.searchQuery.length === 32 && /[\da-f]{32}/.test(this.searchQuery));
		},
		isNetworkAvailableForSearch(): boolean
		{
			if (!this.isNetworkAvailable)
			{
				return false;
			}

			return this.isNetworkSearchEnabled || this.isNetworkSearchCode;
		},
		itemComponent: () => SearchResultItem,
		itemDepartmentComponent: () => SearchResultDepartmentItem,
		itemNetworkComponent: () => SearchResultNetworkItem,
		itemOpenlineComponent: () => SearchResultOpenlineItem,
	},
	watch:
	{
		searchQuery(newValue, oldValue)
		{
			const newQuery = newValue.trim();
			const previousQuery = oldValue.trim();

			if (newQuery === previousQuery)
			{
				return;
			}

			this.startSearch(newQuery);
		},
		searchMode(newValue, oldValue)
		{
			if (newValue === false && oldValue === true) // search switch off
			{
				this.isNetworkButtonClicked = false;
			}
			else if (newValue === true && oldValue === false) // search switch on
			{
				if (this.result.recent.size > 0)
				{
					return;
				}

				this.isRecentLoading = true;
			}

			this.searchService.loadRecentSearchFromServer().then(recentItems => {
				this.result.recent = recentItems;
				this.isRecentLoading = false;
			});
		}
	},
	created()
	{
		this.initSettings();
		this.contextMenuManager = new SearchContextMenu(this.$Bitrix);
		const cache = new SearchCache(this.getCurrentUserId());
		const recentList = new SearchRecentList(this.$Bitrix);
		this.searchService = SearchService.getInstance(this.$Bitrix, cache, recentList);
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 1500, this);

		EventEmitter.subscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.subscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.subscribe(EventType.search.selectItem, this.onSelectItem);
		EventEmitter.subscribe(EventType.recent.updateSearch, this.onPressEnterKey);

		this.loadInitialRecentFromCache();
	},
	beforeUnmount()
	{
		this.searchService.destroy();
		this.contextMenuManager.destroy();
		EventEmitter.unsubscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.unsubscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.unsubscribe(EventType.search.selectItem, this.onSelectItem);
		EventEmitter.unsubscribe(EventType.recent.updateSearch, this.onPressEnterKey);
	},
	methods:
	{
		loadInitialRecentFromCache()
		{
			// we don't need an extra request to get recent items while messenger initialization
			this.searchService.loadRecentSearchFromCache().then(recentItems => {
				this.result.recent = recentItems;
			});
		},
		initSettings()
		{
			const settings = Extension.getSettings('im.v2.component.old-chat-embedding.search');
			const defaultMinTokenSize = 3;
			this.minTokenSize = settings.get('minTokenSize', defaultMinTokenSize);
			this.isNetworkAvailable = settings.get('isNetworkAvailable', false);
			this.isNetworkSearchEnabled = settings.get('isNetworkSearchEnabled', true);
			this.isDepartmentsAvailable = settings.get('isDepartmentsAvailable', false);
		},
		startSearch(searchQuery: string)
		{
			if (searchQuery.length > 0 && searchQuery.length < this.minTokenSize)
			{
				this.isLocalLoading = true;
				const queryBeforeRequest = searchQuery;
				this.searchService.searchLocal(searchQuery).then((localSearchResult: Map<string, SearchItem>) => {
					if (queryBeforeRequest !== this.searchQuery.trim())
					{
						return;
					}
					this.result.usersAndChats = localSearchResult;
					this.isLocalLoading = false;
				});
			}
			else if (searchQuery.length >= this.minTokenSize)
			{
				this.isServerLoading = true;
				const queryBeforeRequest = searchQuery;
				this.searchService.searchLocal(searchQuery).then((localSearchResult: Map<string, SearchItem>) => {
					if (queryBeforeRequest !== this.searchQuery.trim())
					{
						return;
					}
					this.result.usersAndChats = localSearchResult;
				}).then(() => this.searchOnServerDelayed(searchQuery));
			}
			else
			{
				this.cleanSearchResult();
			}
		},
		cleanSearchResult()
		{
			this.result.usersAndChats = new Map();
			this.result.departments = new Map();
			this.result.chatUsers = new Map();
			this.result.network = new Map();
			this.result.openLines = new Map();
		},
		searchOnServer(query: string)
		{
			this.currentServerQueries++;
			this.isNetworkLoading = this.isNetworkButtonClicked;

			const config = {
				network: this.isNetworkAvailableForSearch && this.isNetworkButtonClicked,
				departments: !BX.MessengerProxy.isCurrentUserExtranet() && this.isDepartmentsAvailable,
			};

			const queryBeforeRequest = query;
			this.searchService.searchOnServer(query, config).then((searchResultFromServer: Object) => {
				if (queryBeforeRequest !== this.searchQuery.trim())
				{
					this.stopLoader();

					return;
				}
				this.result.usersAndChats = this.mergeResults(this.result.usersAndChats, searchResultFromServer.usersAndChats);
				this.result.departments = searchResultFromServer.departments;
				this.result.chatUsers = searchResultFromServer.chatUsers;
				this.result.openLines = searchResultFromServer.openLines;
				this.result.network = searchResultFromServer.network;
			}).catch(error => {
				console.error(error);
			}).finally(() => {
				this.currentServerQueries--;
				this.stopLoader();
			});
		},
		stopLoader()
		{
			if (this.currentServerQueries > 0)
			{
				return;
			}

			this.isNetworkLoading = false;
			this.isServerLoading = false;
		},
		searchOnNetwork(query: string)
		{
			this.isNetworkLoading = true;
			const queryBeforeRequest = query;
			this.searchService.searchOnNetwork(query).then((searchResultFromServer: Map<string, SearchItem>) => {
				if (queryBeforeRequest !== this.searchQuery)
				{
					this.isNetworkLoading = false;
					return;
				}
				this.result.network = searchResultFromServer;
				this.isNetworkButtonClicked = true;
				this.isNetworkLoading = false;
			});
		},
		mergeResults(originalItems: Map<string, SearchItem>, newItems: Map<string, SearchItem>): Map<string, SearchItem>
		{
			const mergedMap = new Map(originalItems.entries());

			newItems.forEach((newItemValue, newItemKey) => {
				if (!mergedMap.has(newItemKey))
				{
					mergedMap.set(newItemKey, newItemValue);
				}
			});

			return mergedMap;
		},
		onOpenContextMenu({data: eventData})
		{
			if (eventData.event.altKey && eventData.event.shiftKey)
			{
				return;
			}

			this.contextMenuManager.openMenu(eventData.item, eventData.event.currentTarget);
		},
		onDelete({data: eventData})
		{
			const {dialogId} = eventData;
			this.result.recent.delete(dialogId);
			this.result.usersAndChats.delete(dialogId);
			this.result.chatUsers.delete(dialogId);
		},
		onScroll()
		{
			this.contextMenuManager.destroy();
		},
		onClickLoadNetworkResult()
		{
			this.searchOnNetwork(this.searchQuery);
		},
		onSelectItem(event)
		{
			const {selectedItem, nativeEvent} = event.getData();

			EventEmitter.emit(EventType.dialog.open, {
				dialogId: selectedItem.dialogId,
				chat: this.$store.getters['dialogues/get'](selectedItem.dialogId, true),
				user: this.$store.getters['users/get'](selectedItem.dialogId, true)
			});

			if (!nativeEvent.altKey)
			{
				BX.MessengerProxy.clearSearchInput();
			}
		},
		onPressEnterKey(event)
		{
			if (event.data.keyCode !== 13) // enter
			{
				return;
			}

			const firstItem = this.getFirstItemFromSearchResults();
			if (!firstItem)
			{
				return;
			}

			const selectedItem = {
				id: firstItem.getId(),
				entityId: firstItem.getEntityId(),
				dialogId: firstItem.getDialogId(),
			};
			EventEmitter.emit(
				EventType.search.selectItem,
				{
					selectedItem: selectedItem,
					onlyOpen: firstItem.isOpeLinesType(),
					nativeEvent: {}
				}
			);
		},
		getFirstItemFromSearchResults(): ?string
		{
			if (!this.showSearchResult && this.result.recent.size > 0)
			{
				return SearchUtils.getFirstItemFromMap(this.result.recent);
			}

			if (this.result.usersAndChats.size > 0)
			{
				return SearchUtils.getFirstItemFromMap(this.result.usersAndChats);
			}

			if (this.result.chatUsers.size > 0)
			{
				return SearchUtils.getFirstItemFromMap(this.result.chatUsers);
			}

			if (this.result.openLines.size > 0)
			{
				return SearchUtils.getFirstItemFromMap(this.result.openLines);
			}

			return null;
		},
		getCurrentUserId(): number
		{
			return this.$store.state.application.common.userId;
		}
	},
	template: `
		<div class="bx-messenger-search" @scroll="onScroll">
			<div>
				<template v-if="!showSearchResult">
					<RecentUsersCarousel />
					<SearchResultSection
						:component="itemComponent"
						:items="result.recent" 
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT')" 
						:showMoreButton="false" 
					/>
				</template>
				<template v-if="showSearchResult">
					<SearchResultSection 
						v-if="result.usersAndChats.size > 0"
						:component="itemComponent"
						:items="result.usersAndChats"
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_USERS_AND_CHATS')"
						:min-items:="20"
						:max-items="50"
					/>
					<template v-if="!isLoadingState && isServerSearch">
						<SearchResultSection
							v-if="result.chatUsers.size > 0"
							:component="itemComponent"
							:items="result.chatUsers"
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_CHAT_USERS')"
							:min-items:="5"
							:max-items="20"
						/>
						<SearchResultSection 
							v-if="result.departments.size > 0"
							:component="itemDepartmentComponent"
							:items="result.departments" 
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_DEPARTMENTS')"
							:min-items:="5"
							:max-items="20"
						/>
						<SearchResultSection
							v-if="result.openLines.size > 0"
							:component="itemOpenlineComponent"
							:items="result.openLines"
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_OPENLINES')"
							:min-items:="5"
							:max-items="20"
						/>
						<template v-if="isNetworkAvailableForSearch">
							<SearchResultSection
								v-if="needToShowNetworkSection"
								:component="itemNetworkComponent"
								:items="result.network"
								:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK')"
								:min-items:="5"
								:max-items="20"
							/>
							<template v-if="!isNetworkButtonClicked">
								<div 
									v-if="!isNetworkLoading"
									@click="onClickLoadNetworkResult"
									class="bx-im-search-network-button"
								>
									{{$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK_BUTTON')}}
								</div>
								<div v-else class="bx-search-network-loader-wrapper">
									<div class="bx-search-loader bx-search-loader-large-size"></div>
								</div>
							</template>
						</template>
					</template>
					<div v-if="isEmptyState" class="bx-im-search-not-found-wrapper">
						<div class="bx-im-search-not-found-icon"></div>
						<div class="bx-im-search-not-found-title">{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND') }}</div>
						<div class="bx-im-search-not-found-title">
							{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND_DESCRIPTION') }}
						</div>
					</div>
				</template>
				<LoadingState v-if="isLoadingState" />
			</div>
		</div>
	`
};