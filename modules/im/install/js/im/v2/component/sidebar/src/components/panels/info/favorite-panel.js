import { EventEmitter } from 'main.core.events';

import { EventType, SidebarDetailBlock } from 'im.v2.const';
import { Loader } from 'im.v2.component.elements';
import { Runtime, Extension } from 'main.core';

import { FavoriteMenu } from '../../../classes/context-menu/favorite/favorite-menu';
import { Favorite } from '../../../classes/panels/favorite';
import { FavoriteSearch } from '../../../classes/panels/search/favorite-search';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { TariffLimit } from '../../elements/tariff-limit/tariff-limit';
import { FavoriteItem } from './favorite-item';
import { DateGroup } from '../../elements/date-group/date-group';
import { DetailEmptySearchState } from '../../elements/detail-empty-search-state/detail-empty-search-state';
import { DetailEmptyState as StartState, DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';

import './css/favorite-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarFavoriteItem, ImModelChat } from 'im.v2.model';

const DEFAULT_MIN_TOKEN_SIZE = 3;

// @vue/component
export const FavoritePanel = {
	name: 'FavoritePanel',
	components: {
		FavoriteItem,
		DateGroup,
		StartState,
		DetailEmptyState,
		DetailHeader,
		DetailEmptySearchState,
		Loader,
		TariffLimit,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
			isSearchHeaderOpened: false,
			searchQuery: '',
			searchResult: [],
			currentServerQueries: 0,
			minTokenSize: DEFAULT_MIN_TOKEN_SIZE,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		favorites(): ImModelSidebarFavoriteItem[]
		{
			if (this.isSearchHeaderOpened)
			{
				return this.$store.getters['sidebar/favorites/getSearchResultCollection'](this.chatId);
			}

			return this.$store.getters['sidebar/favorites/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.favorites);
		},
		isEmptyState(): boolean
		{
			return this.formattedCollection.length === 0;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		preparedQuery(): string
		{
			return this.searchQuery.trim().toLowerCase();
		},
		isSearchQueryMinimumSize(): boolean
		{
			return this.preparedQuery.length < this.minTokenSize;
		},
		hasHistoryLimit(): boolean
		{
			return this.$store.getters['sidebar/favorites/isHistoryLimitExceeded'](this.chatId);
		},
	},
	watch:
	{
		preparedQuery(newQuery: string, previousQuery: string)
		{
			if (newQuery === previousQuery)
			{
				return;
			}
			this.cleanSearchResult();
			this.startSearch();
		},
	},
	created()
	{
		this.initSettings();
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.contextMenu = new FavoriteMenu();
		this.service = new Favorite({ dialogId: this.dialogId });
		this.serviceSearch = new FavoriteSearch({ dialogId: this.dialogId });
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 500, this);
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
		this.collectionFormatter.destroy();
	},
	methods:
	{
		initSettings()
		{
			const settings = Extension.getSettings('im.v2.component.sidebar');
			this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE);
		},
		searchOnServer(query: string)
		{
			this.currentServerQueries++;

			this.serviceSearch.searchOnServer(query).then(() => {
				if (query !== this.preparedQuery)
				{
					this.isLoading = false;
				}
			}).catch((error) => {
				console.error(error);
			}).finally(() => {
				this.currentServerQueries--;
				this.stopLoader();
				if (this.isSearchQueryMinimumSize)
				{
					this.cleanSearchResult();
				}
			});
		},
		stopLoader()
		{
			if (this.currentServerQueries > 0)
			{
				return;
			}

			this.isLoading = false;
		},
		startSearch()
		{
			if (this.isSearchQueryMinimumSize)
			{
				this.cleanSearchResult();
			}
			else
			{
				this.isLoading = true;
				this.searchOnServerDelayed(this.preparedQuery);
			}
		},
		cleanSearchResult()
		{
			this.searchResult = [];
			this.serviceSearch.resetSearchState();
		},
		onChangeQuery(query: string)
		{
			this.searchQuery = query;
		},
		toggleSearchPanelOpened()
		{
			this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
		},
		onContextMenuClick(event)
		{
			const item = {
				id: event.id,
				messageId: event.messageId,
				dialogId: this.dialogId,
				chatId: this.chatId,
			};

			this.contextMenu.openMenu(item, event.target);
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.favorite });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const nameGetter = this.searchQuery.length > 0 ? 'sidebar/favorites/hasNextPageSearch' : 'sidebar/favorites/hasNextPage';
			const hasNextPage = this.$store.getters[nameGetter](this.chatId);

			return isAtThreshold && hasNextPage;
		},
		async onScroll(event: Event)
		{
			this.contextMenu.destroy();

			if (this.isLoading || !this.needToLoadNextPage(event))
			{
				return;
			}

			this.isLoading = true;
			if (this.isSearchQueryMinimumSize)
			{
				await this.service.loadNextPage();
			}
			else
			{
				await this.serviceSearch.request();
			}
			this.isLoading = false;
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-sidebar-favorite-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_FAVORITE_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				withSearch
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-favorite-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div
					v-for="dateGroup in formattedCollection"
					class="bx-im-sidebar-favorite-detail__date-group_container"
				>
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<FavoriteItem
						v-for="favorite in dateGroup.items"
						:favorite="favorite"
						:chatId="chatId"
						:dialogId="dialogId"
						:searchQuery="searchQuery"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.favorite"
					class="bx-im-sidebar-favorite-detail__tariff-limit-container"
				/>
				<template v-if="!isLoading">
					<template v-if="isSearchHeaderOpened">
						<StartState
							v-if="preparedQuery.length === 0"
							:title="loc('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
							:iconType="SidebarDetailBlock.messageSearch"
						/>
						<DetailEmptySearchState
							v-else-if="isEmptyState"
							:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
							:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
						/>
					</template>
					<DetailEmptyState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_FAVORITES_EMPTY')"
						:iconType="SidebarDetailBlock.favorite"
					/>
				</template>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
