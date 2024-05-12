import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import { LocalSearch } from 'im.v2.lib.search';
import { Utils } from 'im.v2.lib.utils';

import { BaseServerSearch } from './search/base-search';

import type { Store } from 'ui.vue3.vuex';
import type { SearchResultItem } from 'im.v2.lib.search';
import type { ImModelChat, ImModelUser } from 'im.v2.model';

export class MentionSearchService
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

	async loadChatParticipants(dialogId: string): Promise<string[]>
	{
		const items = await this.#baseServerSearch.loadChatParticipants(dialogId);
		if (this.#isSelfDialogId(dialogId))
		{
			return this.#getDialogIds(items);
		}

		const filteredResult = items.filter((item) => !this.#isSelfDialogId(item.dialogId));

		filteredResult.forEach((searchItem) => {
			this.#localCollection.set(searchItem.dialogId, searchItem);
		});

		return this.#getDialogIds(filteredResult);
	}

	searchLocal(query: string): string[]
	{
		const localCollection = [...this.#localCollection.values()];
		const result = this.#localSearch.search(query, localCollection);

		return this.#getDialogIds(result);
	}

	async search(query: string): Promise<string[]>
	{
		const searchResult = await this.#baseServerSearch.search(query);
		searchResult.forEach((searchItem) => {
			this.#localCollection.set(searchItem.dialogId, searchItem);
		});

		return this.#getDialogIds(searchResult);
	}

	#isSelfDialogId(dialogId: string): boolean
	{
		return dialogId === Core.getUserId().toString();
	}

	#getDialogIds(items: SearchResultItem[]): string[]
	{
		return items.map((item) => item.dialogId);
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
