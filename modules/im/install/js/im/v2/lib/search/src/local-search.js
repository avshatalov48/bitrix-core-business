import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import type { SearchConfig } from 'im.v2.lib.search';
import type { ImModelUser, ImModelChat, ImModelRecentItem } from 'im.v2.model';

type LocalSearchItem = {
	dialogId: string,
	dialog: ImModelChat,
	user?: ImModelUser,
	dateMessage: string,
}

export type SearchResultItem = {
	dialogId: string,
	dateMessage: string,
};

const collator = new Intl.Collator(undefined, { sensitivity: 'base' });

export class LocalSearch
{
	#searchConfig: SearchConfig | undefined;
	#store: Store;

	constructor(searchConfig: SearchConfig)
	{
		this.#searchConfig = searchConfig;
		this.#store = Core.getStore();
	}

	search(query: string, localCollection: SearchResultItem[]): SearchResultItem[]
	{
		const localItems = this.#getLocalItems(localCollection);
		const result = this.#search(query, localItems);

		return this.#filterByConfig(result);
	}

	#search(query: string, localItems: LocalSearchItem[]): SearchResultItem[]
	{
		const queryWords = Utils.text.getWordsFromString(query);

		const foundItems: Map<string, SearchResultItem> = new Map();
		localItems.forEach((localItem) => {
			if (this.#searchByQueryWords(localItem, queryWords))
			{
				foundItems.set(localItem.dialogId, {
					dialogId: localItem.dialogId,
					dateMessage: localItem.dateMessage,
				});
			}
		});

		return [...foundItems.values()];
	}

	#getRecentListItems(): LocalSearchItem[]
	{
		return this.#store.getters['recent/getSortedCollection'].map((item: ImModelRecentItem) => {
			const itemDate = this.#getRecentItemDate(item);

			return this.#prepareRecentItem(item.dialogId, itemDate);
		});
	}

	#prepareRecentItem(dialogId: string, dateMessage: string): LocalSearchItem[]
	{
		const dialog = this.#store.getters['chats/get'](dialogId, true);
		const isUser = dialog.type === ChatType.user;

		const recentItem = { dialogId, dialog, dateMessage };

		if (isUser)
		{
			recentItem.user = this.#store.getters['users/get'](dialogId, true);
		}

		return recentItem;
	}

	#searchByQueryWords(localItem: LocalSearchItem, queryWords: string[]): boolean
	{
		if (localItem.user)
		{
			return this.#searchByUserFields(localItem, queryWords);
		}

		return this.#searchByDialogFields(localItem, queryWords);
	}

	#searchByDialogFields(localItem: LocalSearchItem, queryWords: string[]): boolean
	{
		const searchField = [];

		if (localItem.dialog.name)
		{
			const dialogNameWords = Utils.text.getWordsFromString(localItem.dialog.name.toLowerCase());
			searchField.push(...dialogNameWords);
		}

		return this.#doesItemMatchQuery(searchField, queryWords);
	}

	#searchByUserFields(localItem: LocalSearchItem, queryWords: string[]): boolean
	{
		const searchField = [];

		if (localItem.user.name)
		{
			const userNameWords = Utils.text.getWordsFromString(localItem.user.name.toLowerCase());
			searchField.push(...userNameWords);
		}

		if (localItem.user.workPosition)
		{
			const workPositionWords = Utils.text.getWordsFromString(localItem.user.workPosition.toLowerCase());
			searchField.push(...workPositionWords);
		}

		return this.#doesItemMatchQuery(searchField, queryWords);
	}

	#doesItemMatchQuery(fieldsForSearch: string[], queryWords: string[]): boolean
	{
		let found = 0;
		queryWords.forEach((queryWord) => {
			let queryWordsMatchCount = 0;
			fieldsForSearch.forEach((field) => {
				const word = field.slice(0, queryWord.length);
				if (collator.compare(queryWord, word) === 0)
				{
					queryWordsMatchCount++;
				}
			});
			if (queryWordsMatchCount > 0)
			{
				found++;
			}
		});

		return found >= queryWords.length;
	}

	#getLocalItems(localCollection: SearchResultItem[]): LocalSearchItem[]
	{
		const recentItems = this.#getRecentListItems();
		const localItems = this.#getLocalItemsFromDialogIds(localCollection);

		return this.#mergeItems(localItems, recentItems);
	}

	#getLocalItemsFromDialogIds(localCollection: SearchResultItem[]): LocalSearchItem[]
	{
		return localCollection.map((item) => {
			return this.#prepareRecentItem(item.dialogId, item.dateMessage);
		});
	}

	#mergeItems(items1: LocalSearchItem[], items2: LocalSearchItem[]): LocalSearchItem[]
	{
		const itemsMap = new Map();
		const mergedArray = [...items1, ...items2];

		for (const recentItem of mergedArray)
		{
			if (!itemsMap.has(recentItem.dialogId))
			{
				itemsMap.set(recentItem.dialogId, recentItem);
			}
		}

		return [...itemsMap.values()];
	}

	#filterByConfig(items: SearchResultItem[]): SearchResultItem[]
	{
		if (!this.#searchConfig)
		{
			return items;
		}

		return items.filter((item) => {
			if (this.#searchConfig.chats && item.dialogId.startsWith('chat'))
			{
				return true;
			}

			return !item.dialogId.startsWith('chat') && this.#searchConfig.users;
		});
	}

	#getRecentItemDate(item: ImModelRecentItem): string
	{
		const dateMessage: Date = this.#store.getters['recent/getMessage'](item.dialogId)?.date;
		if (!dateMessage)
		{
			return '';
		}

		return dateMessage.toISOString();
	}
}
