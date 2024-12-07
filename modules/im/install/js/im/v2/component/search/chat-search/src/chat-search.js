import 'ui.design-tokens';
import 'ui.fonts.opensans';

import { Runtime, Extension } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Utils } from 'im.v2.lib.utils';
import { EventType } from 'im.v2.const';
import { ScrollWithGradient } from 'im.v2.component.elements';

import { SearchService } from './classes/search-service';
import { SearchContextMenu } from './classes/search-context-menu';
import { LatestSearchResult } from './components/latest-search-result';
import { SearchResult } from './components/search-result';

import './css/chat-search.css';

import type { JsonObject } from 'main.core';
import type { SearchResultItem } from 'im.v2.lib.search';

// @vue/component
export const ChatSearch = {
	name: 'ChatSearch',
	components: {
		ScrollWithGradient,
		LatestSearchResult,
		SearchResult,
	},
	props: {
		searchQuery: {
			type: String,
			default: '',
		},
		searchMode: {
			type: Boolean,
			required: true,
		},
		selectMode: {
			type: Boolean,
			default: false,
		},
		saveSearchHistory: {
			type: Boolean,
			default: false,
		},
		showMyNotes: {
			type: Boolean,
			default: true,
		},
		selectedItems: {
			type: Array,
			required: false,
			default: () => [],
		},
		searchConfig: {
			type: Object,
			required: true,
		},
	},
	emits: ['clickItem', 'loading', 'scroll'],
	data(): JsonObject
	{
		return {
			isRecentLoading: false,
			isServerLoading: false,

			queryWasDeleted: false,
			currentServerQueries: 0,
			result: {
				recent: [],
				usersAndChats: [],
			},
		};
	},
	computed:
	{
		cleanQuery(): string
		{
			return this.searchQuery.trim().toLowerCase();
		},
		showLatestSearchResult(): boolean
		{
			return this.cleanQuery.length === 0;
		},
	},
	watch:
	{
		cleanQuery(newQuery: string, previousQuery: string)
		{
			if (newQuery.length > 0)
			{
				this.queryWasDeleted = false;
			}

			if (newQuery.length === 0)
			{
				this.searchService.clearSessionResult();
			}

			if (newQuery === previousQuery)
			{
				return;
			}
			this.startSearch(newQuery);
		},
		isServerLoading(newValue: boolean)
		{
			this.$emit('loading', newValue);
		},
		searchMode(newValue: boolean, oldValue: boolean)
		{
			if (!newValue && oldValue)
			{
				this.searchService.clearSessionResult();
				void this.loadRecentSearchFromServer();
			}
		},
	},
	created()
	{
		this.initSettings();
		this.contextMenuManager = new SearchContextMenu();

		this.searchService = new SearchService(this.searchConfig);
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);

		EventEmitter.subscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.subscribe(EventType.search.keyPressed, this.onKeyPressed);

		void this.loadRecentSearchFromServer();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
		EventEmitter.unsubscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.unsubscribe(EventType.search.keyPressed, this.onKeyPressed);
	},
	methods:
	{
		async loadRecentSearchFromServer()
		{
			this.isRecentLoading = true;
			this.result.recent = await this.searchService.loadLatestResults();
			this.isRecentLoading = false;
		},
		initSettings()
		{
			const settings = Extension.getSettings('im.v2.component.search.chat-search');
			const defaultMinTokenSize = 3;
			this.minTokenSize = settings.get('minTokenSize', defaultMinTokenSize);
		},
		startSearch(query: string)
		{
			if (query.length > 0)
			{
				const result = this.searchService.searchLocal(query);
				if (query !== this.cleanQuery)
				{
					return;
				}

				this.result.usersAndChats = this.searchService.sortByDate(result);
			}

			if (query.length >= this.minTokenSize)
			{
				this.isServerLoading = true;
				this.searchOnServerDelayed(query);
			}

			if (query.length === 0)
			{
				this.cleanSearchResult();
			}
		},
		cleanSearchResult()
		{
			this.result.usersAndChats = [];
		},
		async searchOnServer(query: string)
		{
			this.currentServerQueries++;

			const searchResult = await this.searchService.search(query);
			if (query !== this.cleanQuery)
			{
				this.stopLoader();

				return;
			}
			const mergedItems = this.mergeResults(this.result.usersAndChats, searchResult);
			this.result.usersAndChats = this.searchService.sortByDate(mergedItems);
			this.stopLoader();
		},
		stopLoader()
		{
			this.currentServerQueries--;
			if (this.currentServerQueries > 0)
			{
				return;
			}

			this.isServerLoading = false;
		},
		onOpenContextMenu(event)
		{
			if (this.selectMode)
			{
				return;
			}

			const { dialogId, nativeEvent } = event;
			if (Utils.key.isAltOrOption(nativeEvent))
			{
				return;
			}

			this.contextMenuManager.openMenu({ dialogId }, nativeEvent.currentTarget);
		},
		onDelete({ data: eventData })
		{
			const { dialogId } = eventData;
			this.result.recent = this.result.recent.filter((recentItem) => {
				return recentItem !== dialogId;
			});
			this.result.usersAndChats = this.result.usersAndChats.filter((dialogIdFromSearch) => {
				return dialogIdFromSearch !== dialogId;
			});
		},
		onScroll(event)
		{
			this.$emit('scroll', event);
			this.contextMenuManager.destroy();
		},
		async onClickItem(event: {dialogId: string, nativeEvent: KeyboardEvent})
		{
			if (this.saveSearchHistory)
			{
				void this.searchService.saveItemToRecentSearch(event.dialogId);
			}

			this.$emit('clickItem', event);
		},
		onKeyPressed(event: BaseEvent)
		{
			if (!this.searchMode)
			{
				return;
			}

			const { keyboardEvent } = event.getData();

			if (Utils.key.isCombination(keyboardEvent, 'Enter'))
			{
				this.onPressEnterKey(event);
			}

			if (Utils.key.isCombination(keyboardEvent, 'Backspace'))
			{
				this.onPressBackspaceKey();
			}
		},
		onPressEnterKey(keyboardEvent: KeyboardEvent)
		{
			const firstItem: ?SearchResultItem = this.getFirstItemFromSearchResults();
			if (!firstItem)
			{
				return;
			}

			void this.onClickItem({
				dialogId: firstItem.dialogId,
				nativeEvent: keyboardEvent,
			});
		},
		onPressBackspaceKey()
		{
			if (this.searchQuery.length > 0)
			{
				this.queryWasDeleted = false;

				return;
			}

			if (!this.queryWasDeleted)
			{
				this.queryWasDeleted = true;

				return;
			}

			if (this.queryWasDeleted)
			{
				EventEmitter.emit(EventType.search.close);
			}
		},
		getFirstItemFromSearchResults(): ?SearchResultItem
		{
			if (this.showLatestSearchResult && this.result.recent.length > 0)
			{
				return this.result.recent[0];
			}

			if (this.result.usersAndChats.length > 0)
			{
				return this.result.usersAndChats[0];
			}

			return null;
		},
		mergeResults(originalItems: SearchResultItem[], newItems: SearchResultItem[]): SearchResultItem[]
		{
			const mergedItems = [...originalItems, ...newItems].map((item) => {
				return [item.dialogId, item];
			});
			const result = new Map(mergedItems);

			return [...result.values()];
		},
	},
	template: `
		<ScrollWithGradient :gradientHeight="28" :withShadow="false" @scroll="onScroll"> 
			<div class="bx-im-chat-search__container bx-im-chat-search__scope">
				<LatestSearchResult
					v-if="showLatestSearchResult"
					:items="result.recent"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					:showMyNotes="showMyNotes"
					:isLoading="isRecentLoading"
					@clickItem="onClickItem"
					@openContextMenu="onOpenContextMenu"
				/>
				<SearchResult
					v-else
					:items="result.usersAndChats"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					:isLoading="isServerLoading"
					:query="cleanQuery"
					@clickItem="onClickItem"
					@openContextMenu="onOpenContextMenu"
				/>
			</div>
		</ScrollWithGradient> 
	`,
};
