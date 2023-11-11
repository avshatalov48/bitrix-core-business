import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { DialogType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import type { ImModelUser, ImModelDialog, ImModelRecentItem } from 'im.v2.model';

export type RecentItem = {
	dialogId: string,
	dialog: ImModelDialog,
	user?: ImModelUser,
	dateUpdate: string,
}
type RecentCollection = Map<string, RecentItem>;

const collator = new Intl.Collator(undefined, { sensitivity: 'base' });

export class RecentStateSearch
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	search(originalLayoutQuery: string): string[]
	{
		const recentCollection = this.#getItemsFromRecentListByQuery(originalLayoutQuery);

		return this.#getDialogIds(recentCollection);
	}

	#getItemsFromRecentListByQuery(query: string): RecentCollection
	{
		const queryWords = Utils.text.getWordsFromString(query);

		return this.#getFromStore(queryWords);
	}

	#getFromStore(queryWords: string[]): RecentCollection
	{
		const recentItems = this.#getAllRecentItems();

		const foundItems: RecentCollection = new Map();
		recentItems.forEach((recentItem) => {
			if (this.#searchByQueryWords(recentItem, queryWords))
			{
				foundItems.set(recentItem.dialogId, recentItem);
			}
		});

		return foundItems;
	}

	#getRecentListItems(): RecentItem[]
	{
		return this.#store.getters['recent/getRecentCollection'].map((item: ImModelRecentItem) => {
			return this.#prepareRecentItem(item);
		});
	}

	#getSearchSessionListItems(): RecentItem[]
	{
		return this.#store.getters['recent/search/getCollection'].map((item: ImModelRecentItem) => {
			return this.#prepareRecentItem(item);
		});
	}

	#prepareRecentItem(item: ImModelRecentItem): RecentItem[]
	{
		const dialog = this.#store.getters['dialogues/get'](item.dialogId, true);
		const isUser = dialog.type === DialogType.user;

		const recentItem = {
			dialogId: item.dialogId,
			dialog,
			dateUpdate: item.dateUpdate,
		};

		if (isUser)
		{
			recentItem.user = this.#store.getters['users/get'](item.dialogId, true);
		}

		return recentItem;
	}

	#searchByQueryWords(recentItem: RecentItem, queryWords: string[]): boolean
	{
		if (recentItem.user)
		{
			return this.#searchByUserFields(recentItem, queryWords);
		}

		return this.#searchByDialogFields(recentItem, queryWords);
	}

	#searchByDialogFields(recentItem: RecentItem, queryWords: string[]): boolean
	{
		const searchField = [];

		if (recentItem.dialog.name)
		{
			const dialogNameWords = Utils.text.getWordsFromString(recentItem.dialog.name.toLowerCase());
			searchField.push(...dialogNameWords);
		}

		return this.#doesItemMatchQuery(searchField, queryWords);
	}

	#searchByUserFields(recentItem: RecentItem, queryWords: string[]): boolean
	{
		const searchField = [];

		if (recentItem.user.name)
		{
			const userNameWords = Utils.text.getWordsFromString(recentItem.user.name.toLowerCase());
			searchField.push(...userNameWords);
		}

		if (recentItem.user.workPosition)
		{
			const workPositionWords = Utils.text.getWordsFromString(recentItem.user.workPosition.toLowerCase());
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

	#getDialogIds(items: RecentCollection): string[]
	{
		return [...items.values()].map((item) => {
			return item.dialogId;
		});
	}

	#getAllRecentItems(): RecentItem[]
	{
		const recentItems = this.#getRecentListItems();
		const searchSessionItems = this.#getSearchSessionListItems();

		const itemsMap = new Map();
		const mergedArray = [...recentItems, ...searchSessionItems];

		for (const recentItem of mergedArray)
		{
			if (!itemsMap.has(recentItem.dialogId))
			{
				itemsMap.set(recentItem.dialogId, recentItem);
			}
		}

		return [...itemsMap.values()];
	}
}
