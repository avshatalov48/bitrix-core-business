import { ajax as Ajax } from 'main.core';

import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { StoreUpdater, type ImRecentProviderItem, type SearchConfig } from 'im.v2.lib.search';
import { EntityId, getSearchConfig, SearchResultItem } from 'im.v2.lib.search';

type RecentItem = [string, string | number];

const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';
const LOAD_LATEST_RESULTS_ENDPOINT = 'ui.entityselector.load';
const SAVE_ITEM_ENDPOINT = 'ui.entityselector.saveRecentItems';

export class BaseServerSearch
{
	#searchConfig: SearchConfig;
	#storeUpdater: StoreUpdater;

	constructor(searchConfig: SearchConfig)
	{
		this.#searchConfig = searchConfig;
		this.#storeUpdater = new StoreUpdater();
	}

	async search(query: string): Promise<SearchResultItem[]>
	{
		const items = await this.#searchRequest(query);
		await this.#storeUpdater.update(items);

		return this.#getDialogIdAndDate(items);
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

	addItemsToRecentSearchResults(dialogId: string): Promise
	{
		const recentItems = [{ id: dialogId, entityId: EntityId }];

		const config = {
			json: {
				...getSearchConfig(this.#searchConfig),
				recentItems,
			},
		};

		return Ajax.runAction(SAVE_ITEM_ENDPOINT, config);
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
