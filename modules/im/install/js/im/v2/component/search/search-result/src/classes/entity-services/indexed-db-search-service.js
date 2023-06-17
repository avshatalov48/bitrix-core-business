import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';

import {Config} from '../search-config';
import {IndexedDbConnection} from '../indexed-db-connection';
import {LayoutManager} from '../layout-manager';
import {SearchItem} from '../search-item';
import {SearchUtils} from '../search-utils';

export class IndexedDbSearchService
{
	constructor(config: Config)
	{
		this.store = Core.getStore();
		this.db = IndexedDbConnection.getInstance(Core.getUserId());
		this.config = config;
		this.layoutManager = new LayoutManager();
	}

	load(): Promise<Map<string, SearchItem>>
	{
		return this.db.loadRecentFromCache().then(responseFromCache => {
			Logger.warn('Im.Search: Recent search loaded from cache', responseFromCache);

			return responseFromCache;
		}).then(responseFromCache => {
			const {items, recentItems} = responseFromCache;
			const itemMap = SearchUtils.createItemMap(items);

			return {recentItems, itemMap};
		});
	}

	save(items)
	{
		return this.db.save(items);
	}

	unshiftItem(item)
	{
		return this.db.unshiftItem(item);
	}

	search(originalLayoutQuery: string): Promise<Map<string, SearchItem>>
	{
		let wrongLayoutSearchPromise = Promise.resolve([]);
		if (this.layoutManager.needLayoutChange(originalLayoutQuery))
		{
			const wrongLayoutQuery = this.layoutManager.changeLayout(originalLayoutQuery);
			wrongLayoutSearchPromise = this.getItemsFromCacheByQuery(wrongLayoutQuery);
		}

		const correctLayoutSearchPromise = this.getItemsFromCacheByQuery(originalLayoutQuery);

		return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
			return new Map([...result[0], ...result[1]]);
		}).catch(error => {
			console.error('Unknown exception', error);

			return new Map();
		});
	}

	getItemsFromCacheByQuery(query: string): Promise
	{
		const queryWords = SearchUtils.getWordsFromString(query);

		return this.db.search(queryWords).then(cacheItems => {
			return SearchUtils.createItemMap(cacheItems);
		});
	}

	destroy()
	{
		this.db.destroy();
	}
}