import { Runtime, Extension } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Loader } from 'im.v2.component.elements';
import { EventType, SidebarDetailBlock } from 'im.v2.const';

import { concatAndSortSearchResult } from '../../../classes/panels/helpers/concat-and-sort-search-result';
import { TariffLimit } from '../../elements/tariff-limit/tariff-limit';
import { LinkItem } from './link-item';
import { Link } from '../../../classes/panels/link';
import { LinkSearch } from '../../../classes/panels/search/link-search';
import { DateGroup } from '../../elements/date-group/date-group';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { DetailEmptySearchState } from '../../elements/detail-empty-search-state/detail-empty-search-state';
import { DetailEmptyState as StartState, DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';
import { LinkMenu } from '../../../classes/context-menu/link/link-menu';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';

import './css/link-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelSidebarLinkItem } from 'im.v2.model';

const DEFAULT_MIN_TOKEN_SIZE = 3;

// @vue/component
export const LinkPanel = {
	name: 'LinkPanel',
	components: {
		DetailHeader,
		LinkItem,
		DateGroup,
		DetailEmptyState,
		StartState,
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
		links(): ImModelSidebarLinkItem[]
		{
			if (this.isSearchHeaderOpened)
			{
				return this.$store.getters['sidebar/links/getSearchResultCollection'](this.chatId);
			}

			return this.$store.getters['sidebar/links/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.links);
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
			return this.$store.getters['sidebar/links/isHistoryLimitExceeded'](this.chatId);
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
		this.contextMenu = new LinkMenu();
		this.service = new Link({ dialogId: this.dialogId });
		this.serviceSearch = new LinkSearch({ dialogId: this.dialogId });
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

			this.serviceSearch.searchOnServer(query).then((messageIds: string[]) => {
				if (query !== this.preparedQuery)
				{
					this.isLoading = false;

					return;
				}
				this.searchResult = concatAndSortSearchResult(this.searchResult, messageIds);
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
				source: event.source,
				authorId: event.authorId,
			};

			this.contextMenu.openMenu(item, event.target);
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.link });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const nameGetter = this.searchQuery.length > 0 ? 'sidebar/links/hasNextPageSearch' : 'sidebar/links/hasNextPage';
			const hasNextPage = this.$store.getters[nameGetter](this.chatId);

			return isAtThreshold && hasNextPage;
		},
		async onScroll(event: Event): Promise<void>
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
		<div class="bx-im-sidebar-link-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_LINK_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				withSearch
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-link-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<template v-for="link in dateGroup.items">
						<LinkItem
							:contextDialogId="dialogId"
							:searchQuery="searchQuery"
							:link="link" 
							@contextMenuClick="onContextMenuClick"
						/>
					</template>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.link"
					class="bx-im-sidebar-link-detail__tariff-limit-container"
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
						:title="loc('IM_SIDEBAR_LINKS_EMPTY')"
						:iconType="SidebarDetailBlock.link"
					/>
				</template>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
