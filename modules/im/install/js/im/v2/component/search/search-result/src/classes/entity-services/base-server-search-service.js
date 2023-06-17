import {ajax as Ajax} from 'main.core';
import {Logger} from 'im.v2.lib.logger';
import {SearchUtils} from '../search-utils';
import type {Config} from '../search-config';

export class BaseServerSearchService
{
	#searchConfig: Config;

	constructor(searchConfig: Config)
	{
		this.#searchConfig = searchConfig;
	}

	searchRequest(query: string): Promise
	{
		const config = {
			json: this.#searchConfig.getSearch()
		};

		config.json.searchQuery = {
			'queryWords': SearchUtils.getWordsFromString(query),
			'query': query,
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.doSearch', config).then(response => {
				Logger.warn(`Im.Search: Search request result`, response);
				resolve(response.data.dialog.items);
			}).catch(error => reject(error));
		});
	}

	loadRecentFromServer(): Promise
	{
		const config = {
			json: this.#searchConfig.getRecentRequestConfig()
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.load', config).then(response => {
				Logger.warn(`Im.Search: Recent search request result`, response);
				resolve(response.data.dialog);
			}).catch(error => reject(error));
		});
	}

	addItemsToRecentSearchResults(recentItem: Array<string, number>): void
	{
		const [entityId, id] = recentItem;
		const recentItems = [{id, entityId}];

		const config = {
			json: {
				...this.#searchConfig.getRecentRequestConfig(),
				recentItems
			},
		};

		Ajax.runAction('ui.entityselector.saveRecentItems', config);
	}
}