import {Logger} from 'im.v2.lib.logger';
import {Config} from './search-config';
import {SearchItem} from './search-item';
import {StoreUpdater} from './store-updater';
import {SortingResult} from './sorting-result';
import {RecentStateSearchService} from './entity-services/recent-state-search-service';
import {IndexedDbSearchService} from './entity-services/indexed-db-search-service';
import {BaseServerSearchService} from './entity-services/base-server-search-service';
import {NetworkSearchService} from './entity-services/network-search-service';
import {DepartmentSearchService} from './entity-services/department-search-service';
import {SearchUtils} from './search-utils';

import type {ImSearchProviderItem} from '../types/rest';

export class SearchService
{
	constructor(searchConfig)
	{
		this.searchConfig = new Config(searchConfig);

		this.storeUpdater = new StoreUpdater();
		this.sortingResult = new SortingResult();

		this.recentStateSearchService = new RecentStateSearchService();
		this.indexedDbSearchService = new IndexedDbSearchService();
		this.baseServerSearchService = new BaseServerSearchService(this.searchConfig);
		this.networkSearchService = new NetworkSearchService(this.searchConfig);
		this.departmentSearchService = new DepartmentSearchService(this.searchConfig);
	}

	loadRecentSearchFromCache(): Promise<Map<string, SearchItem>>
	{
		return this.indexedDbSearchService.load().then(result => {
			const {recentItems, itemMap} = result;

			return this.#getItemsFromRecentItems(recentItems, itemMap);
		}).then(items => {
			return this.#processResponse({items, onlyAdd: true});
		});
	}

	loadRecentUsers(): Promise<Map<string, SearchItem>>
	{
		const recentUsers = this.recentStateSearchService.load();
		const items = SearchUtils.createItemMap(recentUsers);

		return this.#processResponse({items, updateStore: false});
	}

	loadRecentSearchFromServer(): Promise<Map<string, SearchItem>>
	{
		return this.baseServerSearchService.loadRecentFromServer().then(responseFromServer => {
			this.indexedDbSearchService.save(responseFromServer);

			Logger.warn('Im.Search: Recent search loaded from server');
			const {items, recentItems} = responseFromServer;

			const itemMap = SearchUtils.createItemMap(items);
			const preparedRecentItems = SearchUtils.prepareRecentItems(recentItems);

			return this.#getItemsFromRecentItems(preparedRecentItems, itemMap);
		}).then(items => {
			return this.#processResponse({items});
		});
	}

	searchLocal(query: string): Promise<Map<string, SearchItem>>
	{
		const searchInCachePromise = this.indexedDbSearchService.search(query);
		const searchInRecentListPromise = this.recentStateSearchService.search(query);

		return Promise.all([searchInCachePromise, searchInRecentListPromise]).then(result => {
			const [itemsFromCache, itemsFromRecent] = result;

			return Promise.all([
				this.#processResponse({items: itemsFromCache, onlyAdd: false}),
				this.#processResponse({items: itemsFromRecent, updateStore: false})
			]);
		}).then(result => {
			const [itemsFromCacheProcessed, itemsFromRecentProcessed] = result;
			// Spread order is important, because we have more data in cache than in recent list
			// (for example contextSort field)
			const items = new Map([...itemsFromRecentProcessed, ...itemsFromCacheProcessed]);

			return this.sortingResult.getSortedItems(items, query);
		});
	}

	searchOnServer(query: string): Promise
	{
		return this.baseServerSearchService.searchRequest(query).then(itemsFromServer => {
			this.indexedDbSearchService.save({items: itemsFromServer});

			return SearchUtils.createItemMap(itemsFromServer);
		}).then(items => {
			return this.#processResponse({items});
		}).then(items => {
			return this.sortingResult.allocateSearchResults(items, query);
		});
	}

	searchOnNetwork(query: string): Promise<Map<string, SearchItem>>
	{
		this.searchConfig.enableNetworkSearch();

		return this.networkSearchService.search(query).then(items => {
			return SearchUtils.createItemMap(items);
		});
	}

	loadDepartmentUsers(parentItem: ImSearchProviderItem): Promise<Map<string, SearchItem>>
	{
		return this.departmentSearchService.loadUsers(parentItem).then(responseFromServer => {
			this.indexedDbSearchService.save({items: responseFromServer});
			const items = SearchUtils.createItemMap(responseFromServer);

			return this.#processResponse({items});
		});
	}

	loadNetworkItem(networkCode: string): Promise
	{
		return this.networkSearchService.loadItem(networkCode).then(responseFromServer => {
			const items = SearchUtils.createItemMap([responseFromServer]);

			return this.#processResponse({items});
		});
	}

	addItemToRecent(selectedItem: SearchItem)
	{
		if (selectedItem.isDepartmentType() || selectedItem.isNetworkType())
		{
			return;
		}

		const item = [selectedItem.entityId, selectedItem.id];

		this.indexedDbSearchService.unshiftItem(item);
		this.baseServerSearchService.addItemsToRecentSearchResults(item);
	}

	#processResponse({items, updateStore = true, onlyAdd = false}): Promise<Map<string, SearchItem>>
	{
		const filteredItems = this.#filterByConfig(items);
		if (!updateStore)
		{
			return Promise.resolve(filteredItems);
		}

		return this.storeUpdater.update({items: filteredItems, onlyAdd: onlyAdd}).then(() => {
			return filteredItems;
		});
	}

	#filterByConfig(items: Map<string, SearchItem>): Map<string, SearchItem>
	{
		const filteredItems = [...items].filter(item => {
			const [, value] = item;
			return this.searchConfig.isItemAllowed(value);
		});

		return new Map(filteredItems);
	}

	#getItemsFromRecentItems(recentItems: Array<Object>, items: Map<string, SearchItem>): Map<string, SearchItem>
	{
		const filledRecentItems = new Map();
		recentItems.forEach(recentItem => {
			const itemFromMap = items.get(recentItem.cacheId);
			if (itemFromMap && !itemFromMap.isOpeLinesType())
			{
				filledRecentItems.set(itemFromMap.getEntityFullId(), itemFromMap);
			}
		});

		return filledRecentItems;
	}

	isNetworkAvailable(): boolean
	{
		return this.searchConfig.isNetworkAvailable();
	}

	disableNetworkSearch(): void
	{
		this.searchConfig.disableNetworkSearch();
	}

	destroy()
	{
		this.indexedDbSearchService.destroy();
	}
}