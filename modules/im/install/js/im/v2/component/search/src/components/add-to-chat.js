import 'ui.design-tokens';
import 'ui.fonts.opensans';

import { Runtime } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Utils } from 'im.v2.lib.utils';
import { EventType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { ScrollWithGradient } from 'im.v2.component.elements.scroll-with-gradient';
import { sortByDate, getUsersFromRecentItems, type SearchResultItem } from 'im.v2.lib.search';

import { SearchService } from '../classes/search-service';
import { getFirstItemFromSearchResults } from '../helpers/get-first-search-item';
import { getMinTokenSize } from '../helpers/get-min-token-size';
import { mergeSearchItems } from '../helpers/merge-search-items';
import { EmptyState } from './elements/empty-state';
import { SearchItem } from './elements/search-item';

import './css/chat-search.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const AddToChat = {
	name: 'AddToChat',
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
		selectedItems: {
			type: Array,
			required: false,
			default: () => [],
		},
	},
	emits: ['clickItem'],
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
	},
	created()
	{
		this.searchService = new SearchService({
			chats: false,
			users: true,
		});
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);
		this.recentSearchItems = getUsersFromRecentItems({ withFakeUsers: true });

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
			this.searchService.clearSessionResult();
			this.searchResult = [];
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
			Analytics.getInstance().userAdd.onSelectUserFromSearchResult({
				dialogId: this.dialogId,
				position: itemIndex + 1,
			});
			this.$emit('clickItem', event);
		},
		async onClickItemRecentItem(event: {dialogId: string, nativeEvent: KeyboardEvent}, itemIndex: number)
		{
			Analytics.getInstance().userAdd.onSelectUserFromRecent({
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
				searchResult: this.searchResult,
				recentItems: this.recentSearchItems,
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
		isSelected(dialogId: string): boolean
		{
			return this.selectedItems.includes(dialogId);
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
						:selected="isSelected(item.dialogId)"
						:replaceWithNotes="false"
						@clickItem="onClickItemRecentItem($event, index)"
					/>
				</template>
				<template v-else>
					<SearchItem
						v-for="(item, index) in searchResult"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:dateMessage="item.dateMessage"
						:withDate="true"
						:isSelected="isSelected(item.dialogId)"
						:query="query"
						:replaceWithNotes="false"
						@clickItem="onClickItem($event, index)"
					/>
					<EmptyState v-if="isEmptyState" />
				</template>
			</div>
		</ScrollWithGradient> 
	`,
};
