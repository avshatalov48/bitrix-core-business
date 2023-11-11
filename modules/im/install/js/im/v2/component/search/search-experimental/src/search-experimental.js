import 'ui.design-tokens';
import 'ui.fonts.opensans';

import { Runtime, Extension } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { MessengerSlider } from 'im.v2.lib.slider';
import { EventType, PathPlaceholder } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';

import { SearchService } from 'im.v2.provider.service';
import { SearchContextMenu } from './classes/search-context-menu';
import { LatestSearchResult } from './components/latest-search-result';
import { SearchExperimentalResult } from './components/search-experimental-result';

import './css/search-experimental.css';

// @vue/component
export const SearchExperimental = {
	name: 'SearchExperimental',
	components: {
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
	},
	data(): Object
	{
		return {
			isRecentLoading: false,
			isServerLoading: false,

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
	},
	created()
	{
		this.initSettings();
		this.contextMenuManager = new SearchContextMenu();

		this.searchService = new SearchService();

		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 400, this);

		EventEmitter.subscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.subscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.subscribe(EventType.search.keyPressed, this.onPressEnterKey);

		this.loadRecentSearchFromServer();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
		EventEmitter.unsubscribe(EventType.search.openContextMenu, this.onOpenContextMenu);
		EventEmitter.unsubscribe(EventType.dialog.errors.accessDenied, this.onDelete);
		EventEmitter.unsubscribe(EventType.search.keyPressed, this.onPressEnterKey);
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
			if (query.length > 0)
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

				const mergedItems = this.mergeResults(this.result.usersAndChats, dialogIds);
				this.result.usersAndChats = this.searchService.sortByDate(mergedItems);
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

			if (Utils.key.isCmdOrCtrl(nativeEvent))
			{
				MessengerSlider.getInstance().openNewTab(
					PathPlaceholder.dialog.replace('#DIALOG_ID#', dialogId),
				);
			}
			else
			{
				Messenger.openChat(dialogId);
			}

			if (!Utils.key.isAltOrOption(nativeEvent))
			{
				EventEmitter.emit(EventType.search.close);
			}
		},
		onPressEnterKey(event: BaseEvent)
		{
			const { keyboardEvent } = event.getData();

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
				dialogId: firstItem,
				nativeEvent: keyboardEvent,
			});
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
	},
	template: `
		<div class="bx-im-search-experimental__container bx-im-search-experimental__scope" @scroll="onScroll">
			<LatestSearchResult 
				v-if="showLatestSearchResult" 
				:dialogIds="result.recent" 
				:isLoading="isRecentLoading" 
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
	`,
};
