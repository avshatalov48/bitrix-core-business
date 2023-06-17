import 'ui.design-tokens';
import 'ui.fonts.opensans';

import {Runtime, Extension} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {provide} from 'ui.vue3';

import {Messenger} from 'im.public';
import {EventType} from 'im.v2.const';
import {Button, ButtonColor, ButtonSize, Loader} from 'im.v2.component.elements';
import {Utils} from 'im.v2.lib.utils';

import {SearchService} from './classes/search-service';
import {SearchContextMenu} from './classes/search-context-menu';
import {SearchUtils} from './classes/search-utils';
import {SearchItem} from './classes/search-item';
import {RecentUsersCarousel} from './components/recent-users-carousel';
import {SearchResultSection} from './components/search-result-section';
import {SearchResultNetworkItem} from './components/search-result-network-item';
import {SearchResultDepartmentItem} from './components/search-result-department-item';
import {SearchResultItem} from './components/search-result-item';

import './css/search-result.css';

// @vue/component
export const SearchResult = {
	name: 'SearchResult',
	components: {
		RecentUsersCarousel,
		SearchResultSection,
		SearchResultNetworkItem,
		SearchResultDepartmentItem,
		SearchResultItem,
		Button,
		Loader
	},
	props: {
		searchQuery: {
			type: String,
			default: ''
		},
		searchMode: {
			type: Boolean,
			required: true
		},
		searchConfig: {
			type: Object,
			required: true
		},
		selectMode: {
			type: Boolean,
			default: false
		},
		selectedItems: {
			type: Array,
			required: false,
			default: () => []
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
			result: {
				recentUsers: new Map(),
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
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		itemComponent: () => SearchResultItem,
		itemDepartmentComponent: () => SearchResultDepartmentItem,
		itemNetworkComponent: () => SearchResultNetworkItem,
		cleanQuery(): string
		{
			return this.searchQuery.trim().toLowerCase();
		},
		isEmptyState(): boolean
		{
			if (this.isServerLoading || this.isLocalLoading || this.isNetworkLoading)
			{
				return false;
			}

			if (this.isNetworkSectionAvailable && !this.isNetworkButtonClicked && this.isServerSearch)
			{
				return false;
			}

			return this.result.usersAndChats.size === 0
				&& this.result.departments.size === 0
				&& this.result.chatUsers.size === 0
				&& this.result.openLines.size === 0
				&& this.result.network.size === 0;
		},
		isLoadingState(): boolean
		{
			return this.isServerLoading || this.isRecentLoading;
		},
		isServerSearch(): boolean
		{
			return this.cleanQuery.length >= this.minTokenSize;
		},
		needToShowNetworkSection(): boolean
		{
			return !this.isNetworkButtonClicked || this.result.network.size > 0;
		},
		showSearchResult(): boolean
		{
			return this.cleanQuery.length > 0;
		},
		isNetworkSearchCode(): boolean
		{
			return !!(this.cleanQuery.length === 32 && /[\da-f]{32}/.test(this.cleanQuery));
		},
		isNetworkSectionAvailable(): boolean
		{
			if (!this.searchService.isNetworkAvailable())
			{
				return false;
			}

			return this.isNetworkSearchEnabled || this.isNetworkSearchCode;
		},
	},
	watch:
	{
		cleanQuery(newQuery: string, previousQuery: string)
		{
			if (newQuery === previousQuery)
			{
				return;
			}
			this.startSearch(newQuery);
		},
		searchMode(newValue, oldValue)
		{
			// search switched on and we have recent items
			if (newValue === true && oldValue === false && this.result.recent.size > 0)
			{
				return;
			}

			if (newValue === false && oldValue === true) // search switched off
			{
				this.isNetworkButtonClicked = false;
				this.searchService.disableNetworkSearch();
			}

			this.loadRecentSearchFromServer();
		}
	},
	created()
	{
		this.initSettings();
		this.contextMenuManager = new SearchContextMenu();

		this.searchService = new SearchService(this.searchConfig);
		provide('searchService', this.searchService);

		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 1500, this);

		EventEmitter.subscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.subscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.subscribe(EventType.search.keyPressed, this.onPressEnterKey);

		this.loadInitialRecentResult();
	},
	beforeUnmount()
	{
		this.searchService.destroy();
		this.contextMenuManager.destroy();
		EventEmitter.unsubscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.unsubscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.unsubscribe(EventType.search.keyPressed, this.onPressEnterKey);
	},
	methods:
	{
		loadInitialRecentResult()
		{
			this.searchService.loadRecentUsers().then(items => {
				this.result.recentUsers = items;
			});

			// we don't need an extra request to get recent items while messenger initialization
			this.searchService.loadRecentSearchFromCache().then(recentItems => {
				if (recentItems.size > 0)
				{
					this.result.recent = recentItems;

					return;
				}

				this.loadRecentSearchFromServer();
			});
		},
		loadRecentSearchFromServer()
		{
			this.isRecentLoading = true;
			this.searchService.loadRecentSearchFromServer().then(recentItemsFromServer => {
				this.result.recent = recentItemsFromServer;
				this.isRecentLoading = false;
			});
		},
		initSettings()
		{
			const settings = Extension.getSettings('im.v2.component.search.search-result');
			const defaultMinTokenSize = 3;
			this.minTokenSize = settings.get('minTokenSize', defaultMinTokenSize);
			this.isNetworkSearchEnabled = settings.get('isNetworkSearchEnabled', true);
		},
		startSearch(query: string)
		{
			if (query.length > 0 && query.length < this.minTokenSize)
			{
				this.isLocalLoading = true;
				const queryBeforeRequest = query;
				this.searchService.searchLocal(query).then((localSearchResult: Map<string, SearchItem>) => {
					if (queryBeforeRequest !== this.cleanQuery)
					{
						return;
					}
					this.result.usersAndChats = localSearchResult;
					this.isLocalLoading = false;
				});
			}
			else if (query.length >= this.minTokenSize)
			{
				this.isServerLoading = true;
				const queryBeforeRequest = query;
				this.searchService.searchLocal(query).then((localSearchResult: Map<string, SearchItem>) => {
					if (queryBeforeRequest !== this.cleanQuery)
					{
						this.isServerLoading = false;
						return;
					}
					this.result.usersAndChats = localSearchResult;
				}).then(() => this.searchOnServerDelayed(query));
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

			const queryBeforeRequest = query;
			this.searchService.searchOnServer(query).then((searchResultFromServer: Object) => {
				if (queryBeforeRequest !== this.cleanQuery)
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
		onOpenContextMenu(event: BaseEvent)
		{
			const {item, nativeEvent} = event.getData();

			if (Utils.key.isAltOrOption(nativeEvent))
			{
				return;
			}

			this.contextMenuManager.openMenu(item, nativeEvent.currentTarget);
		},
		onDelete({data: eventData})
		{
			const {dialogId} = eventData;
			this.result.recent.delete(dialogId);
			this.result.usersAndChats.delete(dialogId);
			this.result.chatUsers.delete(dialogId);
		},
		onScroll(event)
		{
			this.$emit('scroll', event);
			this.contextMenuManager.destroy();
		},
		onClickLoadNetworkResult()
		{
			this.isNetworkLoading = true;

			const originalQuery = this.cleanQuery;
			this.searchService.searchOnNetwork(originalQuery).then((searchResultFromServer: Map<string, SearchItem>) => {
				this.isNetworkLoading = false;
				if (originalQuery !== this.cleanQuery)
				{
					return;
				}
				this.result.network = searchResultFromServer;
				this.isNetworkButtonClicked = true;
			});
		},
		onClickItem(event: {selectedItem: SearchItem, selectedStatus: boolean, nativeEvent: KeyboardEvent})
		{
			if (!this.searchMode)
			{
				return;
			}

			const {selectedItem, nativeEvent} = event;
			if (this.selectMode)
			{
				this.$emit('selectItem', event);
			}
			else
			{
				Messenger.openChat(selectedItem.getDialogId());
			}

			if (!Utils.key.isAltOrOption(nativeEvent))
			{
				EventEmitter.emit(EventType.search.close);
			}
		},
		onPressEnterKey(event: BaseEvent)
		{
			if (this.selectMode)
			{
				return;
			}
			const {keyboardEvent} = event.getData();

			if (!Utils.key.isCombination(keyboardEvent, 'Enter'))
			{
				return;
			}

			const firstItem = this.getFirstItemFromSearchResults();
			if (!firstItem)
			{
				return;
			}

			this.onClickItem({
				selectedItem: firstItem,
				nativeEvent: keyboardEvent
			});
		},
		getFirstItemFromSearchResults(): ?SearchItem
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
		}
	},
	template: `
		<div class="bx-im-search-result__container bx-im-search-result__scope" @scroll="onScroll">
			<template v-if="!showSearchResult">
				<RecentUsersCarousel 
					:items="result.recentUsers"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					@clickItem="onClickItem"
				/>
				<SearchResultSection
					:component="itemComponent"
					:items="result.recent"
					:showMoreButton="false"
					:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT')"
					:canBeFolded="false"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					@clickItem="onClickItem"
				/>
			</template>
			<template v-else>
				<SearchResultSection
					v-if="result.usersAndChats.size > 0"
					:component="itemComponent"
					:items="result.usersAndChats"
					:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_USERS_AND_CHATS')"
					:min-items:="20"
					:max-items="50"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					@clickItem="onClickItem"
				/>
				<template v-if="!isLoadingState && isServerSearch">
					<SearchResultSection
						v-if="result.chatUsers.size > 0"
						:component="itemComponent"
						:items="result.chatUsers"
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_CHAT_USERS')"
						:min-items:="5"
						:max-items="20"
						:selectMode="selectMode"
						:selectedItems="selectedItems"
						@clickItem="onClickItem"
					/>
					<SearchResultSection
						v-if="result.departments.size > 0"
						:component="itemDepartmentComponent"
						:items="result.departments"
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_DEPARTMENTS')"
						:min-items:="5"
						:max-items="20"
						:selectMode="selectMode"
						:selectedItems="selectedItems"
						@clickItem="onClickItem"
					/>
					<template v-if="isNetworkSectionAvailable">
						<SearchResultSection
							v-if="needToShowNetworkSection"
							:component="itemNetworkComponent"
							:items="result.network"
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK')"
							:canBeFolded="isNetworkButtonClicked"
							:min-items:="5"
							:max-items="20"
							:selectMode="selectMode"
							:selectedItems="selectedItems"
							@clickItem="onClickItem"
						/>
						<div class="bx-im-search-result__network-button-container">
							<Button
								v-if="!isNetworkButtonClicked"
								:text="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK_BUTTON')"
								:color="ButtonColor.Primary"
								:size="ButtonSize.L"
								:isLoading="isNetworkLoading"
								:isRounded="true"
								@click="onClickLoadNetworkResult"
							/>
						</div>
					</template>
				</template>
				<div v-if="isEmptyState" class="bx-im-search-result__empty-state-container">
					<div class="bx-im-search-result__empty-state-icon"></div>
					<div class="bx-im-search-result__empty-state-title">
						{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND') }}
					</div>
					<div class="bx-im-search-result__empty-state-subtitle">
						{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND_DESCRIPTION') }}
					</div>
				</div>
			</template>
			<div v-if="isLoadingState" class="bx-im-search-result__loader-container">
				<Loader />
			</div>
		</div>
	`
};