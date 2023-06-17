import {Dexie} from 'ui.dexie';
import {Type} from 'main.core';
import {SearchUtils} from './search-utils';
import {EntityIdTypes, ImSearchItem} from './types/search-item';
import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.old-chat-embedding.const';

export class SearchCache
{
	constructor(userId)
	{
		this.userId = userId;
		/** @type {Dexie} */
		this.db = new Dexie('bx-im-search-results');
		this.db.version(2).stores({
			items: 'id, *title, *name, *lastName, *secondName, *position, date',
			recentItems: '++id, cacheId, date',
			settings: '&name'
		}).upgrade(transaction => {
			const clearItemsPromise = transaction.table('items').clear();
			const clearRecentItemsPromise = transaction.table('recentItems').clear();

			return Dexie.Promise.all([clearItemsPromise, clearRecentItemsPromise]);
		});
		this.db.version(3).stores({
			items: 'id, *title, *name, *lastName, *position, date',
			recentItems: '++id, cacheId, date',
			settings: '&name'
		});

		this.checkTables();

		this.onAccessDeniedHandler = this.onAccessDenied.bind(this);
		EventEmitter.subscribe(EventType.dialog.errors.accessDenied, this.onAccessDeniedHandler);
	}

	checkTables()
	{
		this.db.open();
		this.db.on('ready', () => {
			return this.db.transaction('rw', this.db.settings, this.db.items, this.db.recentItems, () => {
				return this.db.settings.where('name').equals('userId').first();
			}).then(settings => {
				const promises = [];
				if (settings?.value !== this.userId)
				{
					const clearItemsPromise = this.db.items.clear();
					const clearRecentItemsPromise = this.db.recentItems.clear();

					promises.push(clearItemsPromise, clearRecentItemsPromise);
				}
				return Dexie.Promise.all(promises);
			}).then(() => {
				return this.db.settings.put({name: 'userId', value: this.userId});
			});
		});
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.errors.accessDenied, this.onAccessDeniedHandler);
	}

	loadRecentFromCache()
	{
		const searchResults = {};

		return this.db.transaction('rw', this.db.items, this.db.recentItems, () => {
			return this.deleteExpiredItems().then(() => {
				return this.db.recentItems.orderBy('id').toArray();
			});
		}).then(recentItemsFromCache => {
			searchResults.recentItems = recentItemsFromCache;

			const resultItemsPromises = [];
			searchResults.recentItems.forEach(recentItem => {
				resultItemsPromises.push(this.db.items.get({id: recentItem.cacheId}));
			});

			return Dexie.Promise.all(resultItemsPromises);
		}).then(result => {
			searchResults.items = result.filter(item => !Type.isUndefined(item)).map(item => item.json);

			return searchResults;
		});
	}

	save(searchResults: Object): void
	{
		const preparedItems = searchResults.items ? this.prepareItems(searchResults.items) : [];
		const preparedRecentItems = searchResults.recentItems ? SearchUtils.prepareRecentItems(searchResults.recentItems) : [];

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

	deleteExpiredItems()
	{
		const oneMonthAgo = new Date(Date.now() - 60*60*1000*24*7*30);

		return this.db.items.where('date').below(oneMonthAgo).delete().then(() => {
			return this.db.recentItems.where('date').below(oneMonthAgo).delete();
		});
	}

	onAccessDenied({data: eventData}): Dexie.Promise
	{
		const cacheId = this.convertDialogIdToCacheItemId(eventData.dialogId);

		return this.db.items.where('id').equals(cacheId).delete().then(() => {
			return this.db.recentItems.where('cacheId').equals(cacheId).delete();
		});
	}

	convertDialogIdToCacheItemId(dialogId: string): string
	{
		if (dialogId.startsWith('chat'))
		{
			return `chat|${dialogId.slice(4)}`;
		}

		return `user|${dialogId}`;
	}

	prepareItems(items: Array<ImSearchItem>): Array<Object>
	{
		return items
			.filter(item => {
				return item.entityId !== EntityIdTypes.department
					&& item.entityId !== EntityIdTypes.network
					&& item.entityType !== 'LINES'
				;
			})
			.map(item => {
				const type = SearchUtils.getTypeByEntityId(item.entityId);
				return {
					id: `${type}|${item.id}`,
					name: item.customData?.name ? SearchUtils.getWordsFromString(item.customData.name) : [],
					lastName: item.customData?.lastName ? SearchUtils.getWordsFromString(item.customData.lastName) : [],
					position: item.customData.imUser?.WORK_POSITION ? SearchUtils.getWordsFromString(item.customData.imUser?.WORK_POSITION) : [],
					title: item.title ? SearchUtils.getWordsFromString(item.title) : [],
					json: item,
					date: new Date()
				};
			});
	}

	/**
	 * Moves item to the top of the recent search items list.
	 *
	 * @param itemToMove Array<string, number>
	 */
	unshiftItem(itemToMove: Array<string, number>): void
	{
		const [itemToMoveEntityId, itemToMoveId] = itemToMove;
		const type = SearchUtils.getTypeByEntityId(itemToMoveEntityId);
		const itemToMoveCacheId = `${type}|${itemToMoveId}`;

		this.db.transaction('rw', this.db.recentItems, () => {
			return this.db.recentItems.toArray();
		}).then(recentItems => {
			const itemIndexToUpdate = recentItems.findIndex(recentItem => {
				return recentItem.cacheId === itemToMoveCacheId;
			});

			if (itemIndexToUpdate === 0)
			{
				return;
			}

			if (itemIndexToUpdate !== -1)
			{
				const item = recentItems.splice(itemIndexToUpdate, 1);
				item[0].date = new Date();
				recentItems.unshift(item[0]);
			}
			else
			{
				const item = {
					cacheId: `${itemToMoveCacheId}|${itemToMoveId}`,
					date: new Date(),
				};
				recentItems.unshift(item);
			}

			recentItems.forEach(item => delete item.id);

			this.db.recentItems.clear().then(() => {
				this.db.recentItems.bulkPut(recentItems);
			});
		});
	}

	search(words: Array<string>): Array<ImSearchItem>
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

	getQueryResultByWords(words: Array<string>): Dexie.Promise
	{
		return Dexie.Promise.all(words.map(word => {
			return this.db.items
				.where('name')
				.startsWithIgnoreCase(word)
				.or('lastName')
				.startsWithIgnoreCase(word)
				.or('position')
				.startsWithIgnoreCase(word)
				.or('title')
				.startsWithIgnoreCase(word)
				.distinct()
				.primaryKeys();
		}));
	}

	intersectArrays(firstArray: Array, secondArray: Array, ...restArrays: Array): Array
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