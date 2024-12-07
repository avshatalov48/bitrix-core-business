import { EventEmitter } from 'main.core.events';
import { Text, Runtime, Extension } from 'main.core';

import { Loader } from 'im.v2.component.elements';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { SidebarFileTabTypes, SidebarDetailBlock, EventType } from 'im.v2.const';

import { File } from '../../../classes/panels/file';
import { DetailTabs } from './components/detail-tabs';
import { MediaTab } from './components/media-tab';
import { AudioTab } from './components/audio-tab';
import { BriefTab } from './components/brief-tab';
import { OtherTab } from './components/other-tab';
import { DocumentTab } from './components/document-tab';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { TariffLimit } from '../../elements/tariff-limit/tariff-limit';
import { FileSearch } from '../../../classes/panels/search/file-search';
import { concatAndSortSearchResult } from '../../../classes/panels/helpers/concat-and-sort-search-result';

import './css/file-panel.css';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

const DEFAULT_MIN_TOKEN_SIZE = 3;

// @vue/component
export const FilePanel = {
	name: 'FilePanel',
	components: { DetailHeader, DetailTabs, MediaTab, AudioTab, DocumentTab, BriefTab, OtherTab, Loader, TariffLimit },
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
			tab: SidebarFileTabTypes.media,
			isSearchHeaderOpened: false,
			searchQuery: '',
			searchResult: [],
			currentServerQueries: 0,
			isLoading: false,
			minTokenSize: DEFAULT_MIN_TOKEN_SIZE,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		tabComponentName(): string
		{
			return `${Text.capitalize(this.tab)}Tab`;
		},
		tabs(): string[]
		{
			const tabTypes = Object.values(SidebarFileTabTypes);
			const canShowBriefs = FeatureManager.isFeatureAvailable(Feature.sidebarBriefs);
			if (!canShowBriefs)
			{
				return tabTypes.filter((tab) => tab !== SidebarDetailBlock.brief);
			}

			return tabTypes;
		},
		preparedQuery(): string
		{
			return this.searchQuery.trim().toLowerCase();
		},
		isSearchQueryMinimumSize(): boolean
		{
			return this.preparedQuery.length < this.minTokenSize;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		hasHistoryLimit(): boolean
		{
			return this.$store.getters['sidebar/files/isHistoryLimitExceeded'](this.chatId);
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
		this.service = new File({ dialogId: this.dialogId, tab: this.tab });
		this.serviceSearch = new FileSearch({ dialogId: this.dialogId, tab: this.tab });
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 500, this);
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

			this.serviceSearch.searchOnServer(query, this.tab).then((messageIds: string[]) => {
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
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.file });
		},
		onTabSelect(tabName: $Keys<typeof SidebarFileTabTypes>)
		{
			this.tab = tabName;
			if (!this.isSearchQueryMinimumSize)
			{
				this.cleanSearchResult();
				this.startSearch();
			}
		},
		onChangeQuery(query: string)
		{
			this.searchQuery = query;
		},
		toggleSearchPanelOpened()
		{
			this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div>
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_MEDIA_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				withSearch
				@back="onBackClick"
			/>
			<TariffLimit
				v-if="hasHistoryLimit"
				:dialogId="dialogId"
				:panel="SidebarDetailBlock.file"
				class="bx-im-sidebar-file__tariff-limit-container" 
			/>
			<DetailTabs :tabs="tabs" @tabSelect="onTabSelect" />
			<KeepAlive>
				<component 
					:is="tabComponentName" 
					:dialogId="dialogId" 
					:searchResult="searchResult" 
					:isSearch="isSearchHeaderOpened" 
					:searchQuery="searchQuery" 
					:isLoadingSearch="isLoading"
				/>
			</KeepAlive>
		</div>
	`,
};
