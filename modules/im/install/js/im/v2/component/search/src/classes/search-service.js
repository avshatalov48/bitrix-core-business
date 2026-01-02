import { ajax as Ajax } from 'main.core';

import {
	EntityId,
	getSearchConfig,
	LocalSearch,
	StoreUpdater,
	SearchConfig,
	type ImRecentProviderItem,
	type SearchResultItem,
} from 'im.v2.lib.search';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';

type RecentItem = [string, string | number];

const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';
const LOAD_LATEST_RESULTS_ENDPOINT = 'ui.entityselector.load';
const SAVE_ITEM_ENDPOINT = 'ui.entityselector.saveRecentItems';

export class SearchService
{
	#localSearch: LocalSearch;
	#localCollection: Map<string, Date> = new Map();
	#searchConfig: SearchConfig;
	#storeUpdater: StoreUpdater;

	constructor(searchConfig)
	{
		this.#searchConfig = searchConfig;
		this.#storeUpdater = new StoreUpdater();
		this.#localSearch = new LocalSearch(searchConfig);
	}

	async loadLatestResults(): Promise<SearchResultItem[]>
	{
		const response = await this.#loadLatestResultsRequest();
		const { items, recentItems } = response;
		if (items.length === 0 || recentItems.length === 0)
		{
			return [];
		}

		const itemsFromRecentItems = this.#getItemsFromRecentItems(recentItems, items);
		await this.#storeUpdater.update(itemsFromRecentItems);

		return this.#getDialogIdAndDate(itemsFromRecentItems);
	}

	searchLocal(query: string): SearchResultItem[]
	{
		const localCollection = [...this.#localCollection.values()];

		return this.#localSearch.search(query, localCollection);
	}

	async search(query: string): Promise<SearchResultItem[]>
	{
		const items = await this.#searchRequest(query);
		await this.#storeUpdater.update(items);

		const searchResult = this.#getDialogIdAndDate(items);

		searchResult.forEach((searchItem) => {
			this.#localCollection.set(searchItem.dialogId, searchItem);
		});

		return searchResult;
	}

	saveItemToRecentSearch(dialogId: string): void
	{
		const recentItems = [{ id: dialogId, entityId: EntityId }];

		const config = {
			json: {
				...getSearchConfig(this.#searchConfig),
				recentItems,
			},
		};

		void Ajax.runAction(SAVE_ITEM_ENDPOINT, config);
	}

	clearSessionResult()
	{
		this.#localCollection.clear();
	}

	async #loadLatestResultsRequest(): Promise<{items: ImRecentProviderItem[], recentItems: Object[]}>
	{
		const config = {
			json: getSearchConfig(this.#searchConfig),
		};

		let items = { items: [], recentItems: [] };
		try
		{
			const response = await Ajax.runAction(LOAD_LATEST_RESULTS_ENDPOINT, config);
			Logger.warn('Search service: latest search request result', response);
			items = response.data.dialog;
		}
		catch (error)
		{
			Logger.warn('Search service: latest search request error', error);
		}

		return items;
	}

	async #searchRequest(query: string): Promise<ImRecentProviderItem[]>
	{
		const config = {
			json: getSearchConfig(this.#searchConfig),
		};

		config.json.searchQuery = {
			queryWords: Utils.text.getWordsFromString(query),
			query,
		};

		let items = [];
		try
		{
			const response = await Ajax.runAction(SEARCH_REQUEST_ENDPOINT, config);
			Logger.warn('Search service: request result', response);
			items = response.data.dialog.items;
		}
		catch (error)
		{
			Logger.warn('Search service: error', error);
		}

		return items;
	}

	#getDialogIdAndDate(items: ImRecentProviderItem[]): SearchResultItem[]
	{
		return items.map((item) => {
			return {
				dialogId: item.id.toString(),
				dateMessage: item.customData?.dateMessage ?? '',
			};
		});
	}

	#getItemsFromRecentItems(recentItems: RecentItem[], items: ImRecentProviderItem[]): ImRecentProviderItem[]
	{
		const filledRecentItems = [];
		recentItems.forEach(([, dialogId]) => {
			const found = items.find((recentItem) => {
				return recentItem.id === dialogId.toString();
			});
			if (found)
			{
				filledRecentItems.push(found);
			}
		});

		return filledRecentItems;
	}
}
