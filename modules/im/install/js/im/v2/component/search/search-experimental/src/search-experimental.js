import 'ui.design-tokens';
import 'ui.fonts.opensans';

import { Runtime, Extension } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { ScrollWithGradient } from 'im.v2.component.elements';
import { EventType, LocalStorageKey } from 'im.v2.const';

import { SearchService } from 'im.v2.provider.service';
import { SearchContextMenu } from './classes/search-context-menu';
import { LatestSearchResult } from './components/latest-search-result';
import { SearchExperimentalResult } from './components/search-experimental-result';

import './css/search-experimental.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const SearchExperimental = {
	name: 'SearchExperimental',
	components: {
		ScrollWithGradient,
		LatestSearchResult,
		SearchExperimentalResult,
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
		handleClickItem: {
			type: Boolean,
			default: true,
		},
		withMyNotes: {
			type: Boolean,
			default: false,
		},
	},
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
	},
	created()
	{
		this.initSettings();
		this.contextMenuManager = new SearchContextMenu();

		this.findByParticipants = LocalStorageManager.getInstance().get(LocalStorageKey.findByParticipants, false);
		this.searchService = new SearchService({ findByParticipants: this.findByParticipants });
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);

		EventEmitter.subscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.subscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.subscribe(EventType.search.keyPressed, this.onKeyPressed);

		this.loadRecentSearchFromServer();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
		EventEmitter.unsubscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.unsubscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.unsubscribe(EventType.search.keyPressed, this.onKeyPressed);
	},
	methods:
	{
		loadRecentSearchFromServer()
		{
			this.isRecentLoading = true;
			this.searchService.loadLatestResults().then((recentItemsFromServer) => {
				this.result.recent = recentItemsFromServer;
				this.isRecentLoading = false;
			}).catch((error) => {
				Logger.error('SearchExperimental: loadRecentSearchFromServer', error);
			});
		},
		initSettings()
		{
			const settings = Extension.getSettings('im.v2.component.search.search-result');
			const defaultMinTokenSize = 3;
			this.minTokenSize = settings.get('minTokenSize', defaultMinTokenSize);
		},
		startSearch(query: string)
		{
			if (!this.findByParticipants && query.length > 0)
			{
				this.searchService.searchLocal(query).then((dialogIds: string[]) => {
					if (query !== this.cleanQuery)
					{
						return;
					}

					this.result.usersAndChats = this.searchService.sortByDate(dialogIds);
				}).catch((error) => {
					Logger.error('SearchExperimental: startSearch', error);
				});
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
		searchOnServer(query: string)
		{
			this.currentServerQueries++;

			this.searchService.searchOnServer(query).then((dialogIds: string[]) => {
				if (query !== this.cleanQuery)
				{
					this.stopLoader();

					return;
				}

				if (this.findByParticipants)
				{
					this.result.usersAndChats = this.searchService.sortByDate(dialogIds);
				}
				else
				{
					const mergedItems = this.mergeResults(this.result.usersAndChats, dialogIds);
					this.result.usersAndChats = this.searchService.sortByDate(mergedItems);
				}
			}).catch((error) => {
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

			this.isServerLoading = false;
		},
		onOpenContextMenu(event: BaseEvent)
		{
			const { item, nativeEvent } = event.getData();

			const recentItem = this.$store.getters['recent/get'](item.dialogId);

			if (Utils.key.isAltOrOption(nativeEvent))
			{
				return;
			}

			this.contextMenuManager.openMenu(recentItem, nativeEvent.currentTarget);
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
		onClickItem(event: {dialogId: string, nativeEvent: KeyboardEvent})
		{
			const { dialogId, nativeEvent } = event;
			if (!this.searchMode)
			{
				return;
			}

			this.searchService.addItemToRecent(dialogId).then(() => {
				this.loadRecentSearchFromServer();
			}).catch((error) => {
				Logger.error('SearchExperimental.onClickItem: addItemToRecent', error);
			});

			Messenger.openChat(dialogId);
			if (!this.handleClickItem)
			{
				this.$emit('clickItem', event);

				return;
			}

			if (!Utils.key.isAltOrOption(nativeEvent))
			{
				EventEmitter.emit(EventType.search.close);
			}
		},
		onKeyPressed(event: BaseEvent)
		{
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
			const firstItem = this.getFirstItemFromSearchResults();
			if (!firstItem)
			{
				return;
			}

			this.onClickItem({
				dialogId: firstItem,
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
		getFirstItemFromSearchResults(): ?string
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
		mergeResults(originalItems: string[], newItems: string[]): string[]
		{
			newItems.forEach((newItem) => {
				if (!originalItems.includes(newItem))
				{
					originalItems.push(newItem);
				}
			});

			return originalItems;
		},
	},
	template: `
		<ScrollWithGradient :gradientHeight="28" :withShadow="false" @scroll="onScroll"> 
			<div class="bx-im-search-experimental__container bx-im-search-experimental__scope">
				<LatestSearchResult
					v-if="showLatestSearchResult"
					:dialogIds="result.recent"
					:isLoading="isRecentLoading"
					:withMyNotes="withMyNotes"
					@clickItem="onClickItem"
				/>
				<SearchExperimentalResult
					v-else
					:dialogIds="result.usersAndChats"
					:isLoading="isServerLoading"
					:query="cleanQuery"
					@clickItem="onClickItem"
				/>
			</div>
		</ScrollWithGradient> 
	`,
};
