import { Core } from 'im.v2.application.core';

import { ChatType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { LocalSearch, type SearchResultItem } from 'im.v2.lib.search';

import { BaseServerSearch } from './search-service/base-server-search';

import type { Store } from 'ui.vue3.vuex';
import type { ImModelUser, ImModelChat } from 'im.v2.model';

export class SearchService
{
	#store: Store;
	#localSearch: LocalSearch;
	#baseServerSearch: BaseServerSearch;
	#localCollection: Map<string, Date> = new Map();

	constructor(searchConfig)
	{
		this.#store = Core.getStore();
		this.#localSearch = new LocalSearch(searchConfig);
		this.#baseServerSearch = new BaseServerSearch(searchConfig);
	}

	loadLatestResults(): Promise<SearchResultItem[]>
	{
		return this.#baseServerSearch.loadLatestResults();
	}

	searchLocal(query: string): SearchResultItem[]
	{
		const localCollection = [...this.#localCollection.values()];

		return this.#localSearch.search(query, localCollection);
	}

	async search(query: string): Promise<SearchResultItem[]>
	{
		const searchResult = await this.#baseServerSearch.search(query);

		searchResult.forEach((searchItem) => {
			this.#localCollection.set(searchItem.dialogId, searchItem);
		});

		return searchResult;
	}

	saveItemToRecentSearch(dialogId: string): Promise
	{
		return this.#baseServerSearch.addItemsToRecentSearchResults(dialogId);
	}

	clearSessionResult()
	{
		this.#localCollection.clear();
	}

	sortByDate(items: SearchResultItem[]): SearchResultItem[]
	{
		items.sort((firstItem, secondItem) => {
			if (!firstItem.dateMessage || !secondItem.dateMessage)
			{
				if (!firstItem.dateMessage && !secondItem.dateMessage)
				{
					if (this.#isExtranet(firstItem.dialogId))
					{
						return 1;
					}

					if (this.#isExtranet(secondItem.dialogId))
					{
						return -1;
					}

					return 0;
				}

				return firstItem.dateMessage ? -1 : 1;
			}

			return Utils.date.cast(secondItem.dateMessage) - Utils.date.cast(firstItem.dateMessage);
		});

		return items;
	}

	#isExtranet(dialogId: string): boolean
	{
		const dialog: ImModelChat = this.#store.getters['chats/get'](dialogId);
		if (!dialog)
		{
			return false;
		}

		if (dialog.type === ChatType.user)
		{
			const user: ImModelUser = this.#store.getters['users/get'](dialogId);

			return user && user.extranet;
		}

		return dialog.extranet;
	}
}
