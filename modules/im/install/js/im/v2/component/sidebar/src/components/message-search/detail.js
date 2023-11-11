import { Runtime } from 'main.core';

import { SidebarDetailBlock } from 'im.v2.const';
import { Loader } from 'im.v2.component.elements';
import { Logger } from 'im.v2.lib.logger';

import { SidebarCollectionFormatter } from '../../classes/sidebar-collection-formatter';
import { EmptyState } from './empty-state';
import { DetailEmptyState as StartState } from '../detail-empty-state';
import { DateGroup } from '../date-group';
import { SearchItem } from './search-item';

import '../../css/message-search/detail.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const MessageSearchDetail = {
	name: 'MessageSearchDetail',
	components: { DateGroup, EmptyState, SearchItem, Loader, StartState },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		chatId: {
			type: Number,
			required: true,
		},
		service: {
			type: Object,
			required: true,
		},
		searchQuery: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
			searchResult: [],
			currentServerQueries: 0,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		formattedCollection(): Array
		{
			const messages = this.searchResult.map((messageId) => {
				return this.$store.getters['messages/getById'](messageId);
			}).filter((item) => Boolean(item));

			return this.collectionFormatter.format(messages);
		},
		isEmptyState(): boolean
		{
			return this.preparedQuery.length > 0 && this.formattedCollection.length === 0;
		},
		preparedQuery(): string
		{
			return this.searchQuery.trim().toLowerCase();
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

			this.service.resetSearchState();
			this.searchResult = [];
			this.startSearch(newQuery);
		},
	},
	created()
	{
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 500, this);
	},
	beforeUnmount()
	{
		this.collectionFormatter.destroy();
	},
	methods:
	{
		searchOnServer(query: string)
		{
			this.currentServerQueries++;

			this.service.searchOnServer(query).then((messageIds: string[]) => {
				if (query !== this.preparedQuery)
				{
					this.isLoading = false;

					return;
				}

				this.searchResult = this.mergeResult(messageIds);
			}).catch((error) => {
				console.error(error);
			}).finally(() => {
				this.currentServerQueries--;
				this.stopLoader();
			});
		},
		startSearch(query: string)
		{
			if (query.length < 3)
			{
				return;
			}

			if (query.length >= 3)
			{
				this.isLoading = true;
				this.searchOnServerDelayed(query);
			}

			if (query.length === 0)
			{
				this.cleanSearchResult();
			}
		},
		stopLoader()
		{
			if (this.currentServerQueries > 0)
			{
				return;
			}

			this.isLoading = false;
		},
		cleanSearchResult()
		{
			this.searchResult = [];
		},
		needToLoadNextPage(event)
		{
			const target = event.target;

			return target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
		},
		onScroll(event)
		{
			this.contextMenu.destroy();

			if (this.isLoading)
			{
				return;
			}

			if (!this.needToLoadNextPage(event) || !this.service.hasMoreItemsToLoad)
			{
				return;
			}

			this.isLoading = true;
			this.service.loadNextPage().then((messageIds) => {
				this.searchResult = this.mergeResult(messageIds);
				this.isLoading = false;
			}).catch((error) => {
				Logger.warn('Message Search: loadNextPage error', error);
			});
		},
		mergeResult(messageIds: string[]): string[]
		{
			return [...this.searchResult, ...messageIds].sort((a, z) => z - a);
		},
	},
	template: `
		<div class="bx-im-message-search-detail__container bx-im-message-search-detail__scope" @scroll="onScroll">
			<StartState 
				v-if="!isLoading && preparedQuery.length === 0"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
				:iconType="SidebarDetailBlock.messageSearch"
			/>
			<EmptyState v-if="!isLoading && isEmptyState"/>
			<Loader v-if="isLoading && isEmptyState" class="bx-im-message-search-detail__loader" />
			<div v-for="dateGroup in formattedCollection" class="bx-im-message-search-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<SearchItem
					v-for="item in dateGroup.items"
					:messageId="item.id"
					:dialogId="dialogId"
					:query="preparedQuery"
				/>
			</div>
		</div>
	`,
};
