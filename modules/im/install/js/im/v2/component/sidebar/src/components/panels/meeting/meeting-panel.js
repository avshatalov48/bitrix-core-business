import { EventEmitter } from 'main.core.events';
import { Runtime, Extension } from 'main.core';

import { EventType, SidebarDetailBlock, ActionByRole } from 'im.v2.const';
import { Loader } from 'im.v2.component.elements';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { PermissionManager } from 'im.v2.lib.permission';

import { concatAndSortSearchResult } from '../../../classes/panels/helpers/concat-and-sort-search-result';
import { TariffLimit } from '../../elements/tariff-limit/tariff-limit';
import { MeetingItem } from './meeting-item';
import { DateGroup } from '../../elements/date-group/date-group';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { DetailEmptyState as StartState, DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';
import { DetailEmptySearchState } from '../../elements/detail-empty-search-state/detail-empty-search-state';
import { MeetingMenu } from '../../../classes/context-menu/meeting/meeting-menu';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';
import { Meeting } from '../../../classes/panels/meeting';
import { MeetingSearch } from '../../../classes/panels/search/meeting-search';

import './css/meeting-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarMeetingItem, ImModelChat } from 'im.v2.model';

const DEFAULT_MIN_TOKEN_SIZE = 3;

// @vue/component
export const MeetingPanel = {
	name: 'MeetingPanel',
	components: {
		MeetingItem,
		DateGroup,
		DetailEmptyState,
		StartState,
		DetailHeader,
		DetailEmptySearchState,
		Loader,
		TariffLimit,
	},
	props: {
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
		meetings(): ImModelSidebarMeetingItem[]
		{
			if (this.isSearchHeaderOpened)
			{
				return this.$store.getters['sidebar/meetings/getSearchResultCollection'](this.chatId);
			}

			return this.$store.getters['sidebar/meetings/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.meetings);
		},
		isEmptyState(): boolean
		{
			return this.formattedCollection.length === 0;
		},
		showAddButton(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.createMeeting, this.dialogId);
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
			return this.$store.getters['sidebar/meetings/isHistoryLimitExceeded'](this.chatId);
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
		this.contextMenu = new MeetingMenu();
		this.service = new Meeting({ dialogId: this.dialogId });
		this.serviceSearch = new MeetingSearch({ dialogId: this.dialogId });
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 500, this);
	},
	beforeUnmount()
	{
		this.collectionFormatter.destroy();
		this.contextMenu.destroy();
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
			this.serviceSearch.resetSearchState();
			this.searchResult = [];
		},
		onChangeQuery(query: string)
		{
			this.searchQuery = query;
		},
		toggleSearchPanelOpened()
		{
			this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
		},
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId,
			};

			this.contextMenu.openMenu(item, target);
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.meeting });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const nameGetter = this.searchQuery.length > 0 ? 'sidebar/meetings/hasNextPageSearch' : 'sidebar/meetings/hasNextPage';
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
		onAddClick()
		{
			(new EntityCreator(this.chatId)).createMeetingForChat();
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-sidebar-meeting-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_MEETING_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:withAddButton="showAddButton"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				withSearch
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				@addClick="onAddClick"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-meeting-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-meeting-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<MeetingItem
						v-for="meeting in dateGroup.items"
						:meeting="meeting"
						:searchQuery="searchQuery"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.meeting"
					class="bx-im-sidebar-meeting-detail__tariff-limit-container"
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
						:title="loc('IM_SIDEBAR_MEETINGS_EMPTY')"
						:iconType="SidebarDetailBlock.meeting"
					/>
				</template>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
