import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { ajax as Ajax } from 'main.core';

import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { getSearchConfig, StoreUpdater } from 'im.v2.lib.search';

import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';
import type { ImRecentProviderItem, SearchConfig, SearchResultItem } from 'im.v2.lib.search';

const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';

export class BaseServerSearch
{
	#storeUpdater: StoreUpdater;
	#restClient: RestClient;
	#searchConfig: SearchConfig;

	constructor(searchConfig)
	{
		this.#searchConfig = searchConfig;
		this.#storeUpdater = new StoreUpdater();
		this.#restClient = Core.getRestClient();
	}

	async search(query: string): Promise<SearchResultItem[]>
	{
		const items = await this.#searchRequest(query);
		await this.#storeUpdater.update(items);

		return this.#getDialogIdAndDate(items);
	}

	async loadChatParticipants(dialogId: string): Promise<SearchResultItem[]>
	{
		const queryParams = {
			order: { lastSendMessageId: 'desc' },
			dialogId,
			limit: 50,
		};

		let users: JsonObject[] = [];
		try
		{
			const response = await this.#restClient.callMethod(RestMethod.imV2ChatUserList, queryParams);
			users = response.data();
		}
		catch (error)
		{
			console.error('Mention search service: load chat participants error', error);
		}

		void this.#storeUpdater.updateUsers(users);

		return this.#getDialogIdAndDate(users);
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
			Logger.warn('Mention search service: request result', response);
			items = response.data.dialog.items;
		}
		catch (error)
		{
			Logger.warn('Mention search service: request error', error);
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
}
