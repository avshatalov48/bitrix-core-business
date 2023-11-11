import { ajax as Ajax } from 'main.core';

import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';

import { SearchItem } from './search-item';
import { EntityId, getSearchConfig } from './helpers/get-search-config';
import { StoreUpdater } from './store-updater';

import type { ImRecentProviderItem } from './types/recent-provider-item';

type SearchItemMap = Map<string, SearchItem>;

const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';
const LOAD_LATEST_RESULTS_ENDPOINT = 'ui.entityselector.load';
const SAVE_ITEM_ENDPOINT = 'ui.entityselector.saveRecentItems';

export class BaseServerSearch
{
	#storeUpdater: StoreUpdater;

	constructor()
	{
		this.#storeUpdater = new StoreUpdater();
	}

	search(query: string): Promise<string[]>
	{
		return this.searchRequest(query).then((items) => {
			const itemsCollection = this.#createItemMap(items);

			return this.#processSearchResponse(itemsCollection);
		}).then((items: SearchItemMap) => {
			return this.#getDialogIds(items);
		});
	}

	searchRequest(query: string): Promise<ImRecentProviderItem[]>
	{
		const config = {
			json: getSearchConfig(),
		};

		config.json.searchQuery = {
			queryWords: Utils.text.getWordsFromString(query),
			query,
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction(SEARCH_REQUEST_ENDPOINT, config).then((response) => {
				Logger.warn('Im.SearchLight: Search request result', response);
				resolve(response.data.dialog.items);
			}).catch((error) => reject(error));
		});
	}

	loadLatestResults(): Promise<string[]>
	{
		return this.loadLatestResultsRequest().then((responseFromServer) => {
			const { items, recentItems } = responseFromServer;
			if (items.length === 0 || recentItems.length === 0)
			{
				return new Map();
			}

			const itemMap = this.#createItemMap(items);
			const itemsFromRecentItems = this.#getItemsFromRecentItems(recentItems, itemMap);

			return this.#processLatestSearchResponse(itemsFromRecentItems);
		}).then((processedItems) => {
			return this.#getDialogIds(processedItems);
		});
	}

	loadLatestResultsRequest(): Promise<ImRecentProviderItem[]>
	{
		const config = {
			json: getSearchConfig(),
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction(LOAD_LATEST_RESULTS_ENDPOINT, config).then((response) => {
				Logger.warn('Im.SearchLight: Recent search request result', response);
				resolve(response.data.dialog);
			}).catch((error) => reject(error));
		});
	}

	addItemsToRecentSearchResults(dialogId: string): Promise
	{
		const recentItems = [{ id: dialogId, entityId: EntityId }];

		const config = {
			json: {
				...getSearchConfig(),
				recentItems,
			},
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction(SAVE_ITEM_ENDPOINT, config).then(() => {
				resolve();
			}).catch((error) => reject(error));
		});
	}

	#processLatestSearchResponse(items: SearchItemMap): Promise<SearchItemMap>
	{
		return this.#storeUpdater.update(items).then(() => {
			return items;
		});
	}

	#processSearchResponse(items: SearchItemMap): Promise<SearchItemMap>
	{
		return this.#storeUpdater.update(items).then(() => {
			return this.#storeUpdater.updateSearchSession(items);
		}).then(() => {
			return items;
		});
	}

	#getDialogIds(items: SearchItemMap): string[]
	{
		return [...items.values()].map((item: SearchItem) => {
			return item.getDialogId();
		});
	}

	#getItemsFromRecentItems(recentItems: [string, string | number][], items: SearchItemMap): SearchItemMap
	{
		const filledRecentItems: SearchItemMap = new Map();
		recentItems.forEach((recentItem) => {
			const [, dialogId] = recentItem;
			const itemFromMap = items.get(dialogId.toString());
			if (itemFromMap)
			{
				filledRecentItems.set(itemFromMap.getDialogId(), itemFromMap);
			}
		});

		return filledRecentItems;
	}

	#createItemMap(items: ImRecentProviderItem[]): SearchItemMap
	{
		const map: SearchItemMap = new Map();

		items.forEach((item) => {
			const mapItem = new SearchItem(item);
			map.set(mapItem.getDialogId(), mapItem);
		});

		return map;
	}

	clearSessionSearch()
	{
		void this.#storeUpdater.clearSessionSearch();
	}
}
