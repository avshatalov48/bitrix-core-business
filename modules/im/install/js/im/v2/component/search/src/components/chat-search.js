import 'ui.design-tokens';
import 'ui.fonts.opensans';

import { Runtime, type JsonObject } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { Utils } from 'im.v2.lib.utils';
import { EventType } from 'im.v2.const';

import { ScrollWithGradient } from 'im.v2.component.elements.scroll-with-gradient';
import { Loader } from 'im.v2.component.elements.loader';
import { sortByDate, type SearchResultItem } from 'im.v2.lib.search';

import { SearchService } from '../classes/search-service';
import { SearchContextMenu } from '../classes/search-context-menu';
import { getFirstItemFromSearchResults } from '../helpers/get-first-search-item';
import { getMinTokenSize } from '../helpers/get-min-token-size';
import { mergeSearchItems } from '../helpers/merge-search-items';
import { EmptyState } from './elements/empty-state';
import { SearchItem } from './elements/search-item';
import { RecentUsersCarousel } from './elements/recent-users-carousel';

import './css/chat-search.css';

// @vue/component
export const ChatSearch = {
	name: 'ChatSearch',
	components: { ScrollWithGradient, SearchItem, EmptyState, RecentUsersCarousel, Loader },
	props: {
		query: {
			type: String,
			default: '',
		},
		searchMode: {
			type: Boolean,
			required: true,
		},
	},
	emits: ['loading'],
	data(): JsonObject
	{
		return {
			isRecentLoading: false,
			isServerLoading: false,

			currentServerQueries: 0,
			recentItems: [],
			searchResult: [],
		};
	},
	computed: {
		cleanQuery(): string
		{
			return this.query.trim().toLowerCase();
		},
		showLatestSearchResult(): boolean
		{
			return this.cleanQuery.length === 0;
		},
		isEmptyState(): boolean
		{
			return this.searchResult.length === 0;
		},
	},
	watch: {
		cleanQuery(newQuery: string)
		{
			if (newQuery.length === 0)
			{
				this.cleanSearchResult();
			}

			this.startSearch(newQuery);
		},
		isServerLoading(newValue: boolean)
		{
			this.$emit('loading', newValue);
		},
		searchMode(newValue: boolean)
		{
			if (!newValue)
			{
				this.searchService.clearSessionResult();
				void this.loadRecentSearchFromServer();
			}
		},
	},
	created()
	{
		this.contextMenuManager = new SearchContextMenu();

		this.searchService = new SearchService({
			chats: true,
			users: true,
		});
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);

		EventEmitter.subscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.subscribe(EventType.search.keyPressed, this.onKeyPressed);

		void this.loadRecentSearchFromServer();
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.unsubscribe(EventType.search.keyPressed, this.onKeyPressed);
	},
	methods: {
		async loadRecentSearchFromServer()
		{
			this.isRecentLoading = true;
			this.recentItems = await this.searchService.loadLatestResults();
			this.isRecentLoading = false;
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

				this.searchResult = sortByDate(result);
			}

			if (query.length >= getMinTokenSize())
			{
				this.isServerLoading = true;
				this.searchOnServerDelayed(query);
			}
		},
		cleanSearchResult()
		{
			this.searchResult = [];
			this.searchService.clearSessionResult();
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
			const mergedItems = mergeSearchItems(this.searchResult, searchResult);
			this.searchResult = sortByDate(mergedItems);
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
			this.recentItems = this.recentItems.filter((recentItem) => {
				return recentItem !== dialogId;
			});
			this.searchResult = this.searchResult.filter((dialogIdFromSearch) => {
				return dialogIdFromSearch !== dialogId;
			});
		},
		onScroll()
		{
			this.contextMenuManager.destroy();
		},
		async onClickItem(event: {dialogId: string, nativeEvent: KeyboardEvent})
		{
			const { dialogId, nativeEvent } = event;
			this.searchService.saveItemToRecentSearch(dialogId);
			void Messenger.openChat(dialogId);

			if (!Utils.key.isAltOrOption(nativeEvent))
			{
				EventEmitter.emit(EventType.search.close);
			}
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
		},
		onPressEnterKey(keyboardEvent: KeyboardEvent)
		{
			const firstItem: ?SearchResultItem = getFirstItemFromSearchResults({
				searchResult: this.searchResult,
				recentItems: this.recentItems,
			});
			if (!firstItem)
			{
				return;
			}

			void this.onClickItem({
				dialogId: firstItem.dialogId,
				nativeEvent: keyboardEvent,
			});
		},
		loc(key: string): string
		{
			return this.$Bitrix.Loc.getMessage(key);
		},
	},
	template: `
		<ScrollWithGradient :gradientHeight="28" :withShadow="false" @scroll="onScroll"> 
			<div class="bx-im-chat-search__container">
				<template v-if="showLatestSearchResult">
					<RecentUsersCarousel
						@clickItem="onClickItem"
						@openContextMenu="onOpenContextMenu"
					/>
					<div class="bx-im-chat-search__title">{{ loc('IM_SEARCH_SECTION_RECENT') }}</div>
					<SearchItem
						v-for="item in recentItems"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:replaceWithNotes="true"
						@clickItem="onClickItem"
						@openContextMenu="onOpenContextMenu"
					/>
					<Loader v-if="isRecentLoading" class="bx-im-chat-search__loader" />
				</template>
				<template v-else>
					<SearchItem
						v-for="item in searchResult"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:dateMessage="item.dateMessage"
						:withDate="true"
						:query="cleanQuery"
						:replaceWithNotes="true"
						@clickItem="onClickItem"
						@openContextMenu="onOpenContextMenu"
					/>
					<EmptyState v-if="isEmptyState" />
				</template>
			</div>
		</ScrollWithGradient> 
	`,
};
