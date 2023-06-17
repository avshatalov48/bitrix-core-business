import {DialogType} from 'im.v2.const';
import {SearchUtils} from './search-utils';

export class SearchRecentList
{
	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
	}

	// region public methods
	search(queryWords: Array<string>): Promise
	{
		const recentListItems = this.getRecentListItems();
		const foundItems = [];

		recentListItems.forEach(recentListItem => {
			if (this.searchByQueryWords(recentListItem, queryWords))
			{
				foundItems.push(recentListItem);
			}
		});

		return Promise.resolve(SearchUtils.createItemMap(foundItems));
	}
	//endregion

	getRecentListItems(): Array
	{
		return this.store.getters['recent/getSortedCollection'].map(item => {
			const dialog = this.store.getters['dialogues/get'](item.dialogId, true);
			const isUser = dialog.type === DialogType.user;

			const recentListItem = {
				dialogId: item.dialogId,
				dialog: dialog,
			};

			if (isUser)
			{
				recentListItem.user = this.store.getters['users/get'](item.dialogId, true);
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
				if (field.startsWith(queryWord))
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