import 'ui.design-tokens';
import 'ui.fonts.opensans';
import { Runtime, type JsonObject } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { EventType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { getRecentListItems, sortByDate, type SearchResultItem } from 'im.v2.lib.search';
import { ScrollWithGradient } from 'im.v2.component.elements.scroll-with-gradient';

import { getFirstItemFromSearchResults } from '../helpers/get-first-search-item';
import { getMinTokenSize } from '../helpers/get-min-token-size';
import { mergeSearchItems } from '../helpers/merge-search-items';
import { EmptyState } from './elements/empty-state';
import { SearchItem } from './elements/search-item';
import { SearchService } from '../classes/search-service';

import './css/chat-search.css';

// @vue/component
export const ForwardSearch = {
	name: 'ForwardSearch',
	components: { ScrollWithGradient, SearchItem, EmptyState },
	props: {
		query: {
			type: String,
			default: '',
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['clickItem', 'loading'],
	data(): JsonObject
	{
		return {
			isLoading: false,

			currentServerQueries: 0,
			searchResult: [],
		};
	},
	computed: {
		showLatestSearchResult(): boolean
		{
			return this.query.length === 0;
		},
		recentSearchItems(): SearchResultItem[]
		{
			const filteredRecent = this.recentListItems.filter(({ dialogId }) => {
				return dialogId !== Core.getUserId().toString();
			});

			filteredRecent.unshift({
				dialogId: Core.getUserId().toString(),
				dateMessage: '',
			});

			return filteredRecent;
		},
		isEmptyState(): boolean
		{
			return this.searchResult.length === 0;
		},
	},
	watch: {
		query(newQuery: string)
		{
			if (newQuery.length === 0)
			{
				this.cleanSearchResult();
			}

			this.startSearch(newQuery);
		},
		isLoading(newValue: boolean)
		{
			this.$emit('loading', newValue);
		},
	},
	created()
	{
		this.searchService = new SearchService({
			chats: true,
			users: true,
		});
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);
		this.recentListItems = getRecentListItems({ withFakeUsers: true });

		EventEmitter.subscribe(EventType.search.keyPressed, this.onKeyPressed);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.search.keyPressed, this.onKeyPressed);
	},
	methods: {
		startSearch(query: string)
		{
			if (query.length > 0)
			{
				const result = this.searchService.searchLocal(query);
				if (query !== this.query)
				{
					return;
				}

				this.searchResult = sortByDate(result);
			}

			if (query.length >= getMinTokenSize())
			{
				this.isLoading = true;
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
			if (query !== this.query)
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

			this.isLoading = false;
		},
		async onClickItem(event: {dialogId: string, nativeEvent: KeyboardEvent}, itemIndex: number)
		{
			Analytics.getInstance().messageForward.onSelectRecipientFromSearchResult({
				dialogId: this.dialogId,
				position: itemIndex + 1,
			});
			this.$emit('clickItem', event);
		},
		async onClickRecentItem(event: {dialogId: string, nativeEvent: KeyboardEvent}, itemIndex: number)
		{
			Analytics.getInstance().messageForward.onSelectRecipientFromRecent({
				dialogId: this.dialogId,
				position: itemIndex + 1,
			});
			this.$emit('clickItem', event);
		},
		onKeyPressed(event: BaseEvent)
		{
			const { keyboardEvent } = event.getData();

			if (Utils.key.isCombination(keyboardEvent, 'Enter'))
			{
				this.onPressEnterKey(event);
			}
		},
		onPressEnterKey(keyboardEvent: KeyboardEvent)
		{
			const firstItem: ?SearchResultItem = getFirstItemFromSearchResults({
				recentItems: this.recentSearchItems,
				searchResult: this.searchResult,
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
		<ScrollWithGradient :gradientHeight="28" :withShadow="false"> 
			<div class="bx-im-chat-search__container">
				<div class="bx-im-chat-search__title">
					{{ loc('IM_SEARCH_SECTION_RECENT_CHATS') }}
				</div>
				<template v-if="showLatestSearchResult">
					<SearchItem
						v-for="(item, index) in recentSearchItems"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:replaceWithNotes="true"
						@clickItem="onClickRecentItem($event, index)"
					/>
				</template>
				<template v-else>
					<SearchItem
						v-for="(item, index) in searchResult"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:dateMessage="item.dateMessage"
						:withDate="true"
						:query="query"
						:replaceWithNotes="true"
						@clickItem="onClickItem($event, index)"
					/>
					<EmptyState v-if="isEmptyState" />
				</template>
			</div>
		</ScrollWithGradient> 
	`,
};
