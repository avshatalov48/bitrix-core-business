import {Dexie} from 'ui.dexie';
import {Type} from 'main.core';
import {EntityIdTypes} from './type/search-item';

export class SearchCacheService
{
	constructor()
	{
		/** @type {Dexie} */
		this.db = new Dexie('bx-im-search-results');
		this.db.version(1).stores({
			items: 'id, title, name, lastName, secondName, position, date',
			recentItems: '++id'
		});
	}

	loadRecentFromCache()
	{
		const searchResults = {};

		return this.db.transaction('r', this.db.items, this.db.recentItems, () => {
			return this.db.recentItems.orderBy('id').toArray();
		}).then(result => {
			searchResults.recentItems = result.map(item => item.json);

			const resultItemsPromises = [];
			searchResults.recentItems.forEach(recentItem => {
				const recentItemId = `${recentItem[1]}${recentItem[0]}`;
				resultItemsPromises.push(this.db.items.get({id: recentItemId}));
			});

			return Dexie.Promise.all(resultItemsPromises);
		}).then(result => {
			searchResults.items = result.filter(item => !Type.isUndefined(item)).map(item => item.json);

			return searchResults;
		});
	}

	//todo refactor because of complexity
	saveToCache(searchResults)
	{
		let preparedItems = [];
		if (searchResults.items)
		{
			preparedItems = searchResults.items
				.filter(item => item.entityId !== EntityIdTypes.department)
				.map(item => {
					return {
						id: `${item.id}${item.entityId}`,
						name: item.customData?.name ? item.customData.name : '',
						lastName: item.customData?.lastName ? item.customData.lastName : '',
						secondName: item.customData?.secondName ? item.customData.secondName : '',
						position: item.customData?.position ? item.customData.position : '',
						title: item.title ? item.title : '',
						json: item,
						date: new Date(),
					};
			});
		}

		let preparedRecentItems = [];
		if (searchResults.recentItems)
		{
			preparedRecentItems = searchResults.recentItems.map(item => {
				return {
					json: item,
					date: new Date(),
				};
			});
		}

		this.db.transaction('rw', this.db.items, this.db.recentItems, () => {
			if (preparedItems.length > 0)
			{
				this.db.items.bulkPut(preparedItems);
			}
			if (preparedRecentItems.length > 0)
			{
				this.db.recentItems.clear().then(() => {
					this.db.recentItems.bulkPut(preparedRecentItems);
				});
			}
		});
	}

	/**
	 * Moves item to the top of the recent search items list.
	 *
	 * @param itemToMove Array<string, number>
	 */
	unshiftItem(itemToMove: Array<string, number>)
	{
		this.db.transaction('rw', this.db.recentItems, () => {
			return this.db.recentItems.toArray();
		}).then(recentItems => {
			const recentItemsPairs = recentItems.map(item => item.json);
			const itemIndexToUpdate = recentItemsPairs.findIndex(item => {
				return item[1] === itemToMove[1] && item[0] === itemToMove[0];
			});

			if (itemIndexToUpdate === 0)
			{
				return;
			}

			if (itemIndexToUpdate !== -1)
			{
				const item = recentItemsPairs.splice(itemIndexToUpdate, 1);
				recentItemsPairs.unshift(item[0]);
			}
			else
			{
				recentItemsPairs.unshift(itemToMove);
			}

			this.saveToCache({recentItems: recentItemsPairs});
		});
	}

	search(words: Array<string>)
	{
		return this.db.transaction('r', this.db.items, function* () {
			// Parallel search for all words - just select resulting primary keys
			const results = yield this.getQueryResultByWords(words);
			if (!Type.isArrayFilled(results))
			{
				return [];
			}

			const intersectedResult = this.intersectArrays(...results);
			const distinctIds = [...new Set(intersectedResult.flat())];

			// Finally, select entire items from intersection
			return yield this.db.items.where(':id').anyOf(distinctIds).toArray();
		}.bind(this)).then(items => {
			return items.map(item => item.json);
		});
	}

	getQueryResultByWords(words: Array<string>)
	{
		return Dexie.Promise.all(words.map(word => {
			return this.db.items
				.where('name')
				.startsWithIgnoreCase(word)
				.or('lastName')
				.startsWithIgnoreCase(word)
				.or('position')
				.startsWithIgnoreCase(word)
				.or('secondName')
				.startsWithIgnoreCase(word)
				.or('title')
				.startsWithIgnoreCase(word)
				.primaryKeys();
		}));
	}

	intersectArrays(firstArray, secondArray, ...restArrays)
	{
		if (Type.isUndefined(secondArray))
		{
			return firstArray;
		}

		const intersectedArray = firstArray.filter(value => secondArray.includes(value));
		if (restArrays.length === 0)
		{
			return intersectedArray;
		}

		return this.intersectArrays(intersectedArray, ...restArrays);
	}
}