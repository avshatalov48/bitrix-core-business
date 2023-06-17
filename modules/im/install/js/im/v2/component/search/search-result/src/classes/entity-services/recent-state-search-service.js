import {Core} from 'im.v2.application.core';
import {DialogType} from 'im.v2.const';

import {LayoutManager} from '../layout-manager';
import {SearchUtils} from '../search-utils';

import type {ImModelUser, ImModelDialog} from 'im.v2.model';

type RecentItem = {
	dialogId: string,
	user: ImModelUser,
	dialog: ImModelDialog
}

const collator = new Intl.Collator(undefined, {sensitivity: 'base'});

export class RecentStateSearchService
{
	#store: Object;

	constructor()
	{
		this.#store = Core.getStore();
		this.layoutManager = new LayoutManager();
	}

	load(): RecentItem[]
	{
		const recentUsers = [];
		this.#store.getters['recent/getSortedCollection'].forEach(recentItem => {
			const dialog = this.#store.getters['dialogues/get'](recentItem.dialogId, true);
			const user = this.#store.getters['users/get'](recentItem.dialogId, true);

			recentUsers.push({dialogId: recentItem.dialogId, dialog, user});
		});

		return recentUsers.filter(item => {
			return item.dialog.type === 'user' && !item.user.bot && item.user.id !== Core.getUserId();
		});
	}

	search(originalLayoutQuery: string): Promise
	{
		let wrongLayoutSearchPromise = Promise.resolve([]);
		if (this.layoutManager.needLayoutChange(originalLayoutQuery))
		{
			const wrongLayoutQuery = this.layoutManager.changeLayout(originalLayoutQuery);
			wrongLayoutSearchPromise = this.getItemsFromRecentListByQuery(wrongLayoutQuery);
		}

		const correctLayoutSearchPromise = this.getItemsFromRecentListByQuery(originalLayoutQuery);

		return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
			return new Map([...result[0], ...result[1]]);
		});
	}

	getItemsFromRecentListByQuery(query: string): Promise
	{
		const queryWords = SearchUtils.getWordsFromString(query);

		return SearchUtils.createItemMap(this.getFromStore(queryWords));
	}

	getFromStore(queryWords: Array<string>)
	{
		const recentListItems = this.getRecentListItems();
		const foundItems = [];

		recentListItems.forEach(recentListItem => {
			if (this.searchByQueryWords(recentListItem, queryWords))
			{
				foundItems.push(recentListItem);
			}
		});

		return foundItems;
	}
	//endregion

	getRecentListItems(): Array
	{
		return this.#store.getters['recent/getSortedCollection'].map(item => {
			const dialog = this.#store.getters['dialogues/get'](item.dialogId, true);
			const isUser = dialog.type === DialogType.user;

			const recentListItem = {
				dialogId: item.dialogId,
				dialog: dialog,
			};

			if (isUser)
			{
				recentListItem.user = this.#store.getters['users/get'](item.dialogId, true);
			}

			return recentListItem;
		});
	}

	searchByQueryWords(recentListItem: Object, queryWords: Array<string>): boolean
	{
		if (recentListItem.user)
		{
			return this.searchByUserFields(recentListItem, queryWords);
		}

		return this.searchByDialogFields(recentListItem, queryWords);
	}

	searchByDialogFields(recentListItem: Object, queryWords: Array<string>): boolean
	{
		const searchField = [];

		if (recentListItem.dialog.name)
		{
			const dialogNameWords = SearchUtils.getWordsFromString(recentListItem.dialog.name.toLowerCase());
			searchField.push(...dialogNameWords);
		}

		return this.doesItemMatchQuery(searchField, queryWords);
	}

	searchByUserFields(recentListItem: Object, queryWords: Array<string>): boolean
	{
		const searchField = [];

		if (recentListItem.user.firstName)
		{
			const userFirstNameWords = SearchUtils.getWordsFromString(recentListItem.user.firstName.toLowerCase());
			searchField.push(...userFirstNameWords);
		}

		if (recentListItem.user.lastName)
		{
			const userLastNameWords = SearchUtils.getWordsFromString(recentListItem.user.lastName.toLowerCase());
			searchField.push(...userLastNameWords);
		}

		if (recentListItem.user.workPosition)
		{
			const userWorkPositionWords = SearchUtils.getWordsFromString(recentListItem.user.workPosition.toLowerCase());
			searchField.push(...userWorkPositionWords);
		}

		return this.doesItemMatchQuery(searchField, queryWords);
	}

	doesItemMatchQuery(fieldsForSearch: Array<string>, queryWords: Array<string>): boolean
	{
		let found = 0;
		queryWords.forEach(queryWord => {
			let queryWordsMatchCount = 0;
			fieldsForSearch.forEach(field => {
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
}