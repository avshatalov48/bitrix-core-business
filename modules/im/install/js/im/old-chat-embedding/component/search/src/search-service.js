import {ajax as Ajax} from 'main.core';
import {Logger} from 'im.old-chat-embedding.lib.logger';
import {DialogType, EventType, RestMethod} from 'im.old-chat-embedding.const';
import {EventEmitter} from 'main.core.events';
import {SearchCache} from './search-cache';
import {EntityIdTypes, ImSearchItem} from './types/search-item';
import {Config} from './search-config';
import {SearchUtils} from './search-utils';
import {SearchRecentList} from './search-recent-list';
import {SearchItem} from './search-item';

const RestMethodImopenlinesNetworkJoin = 'imopenlines.network.join';

export class SearchService
{
	static instance = null;
	store: Object = null;
	cache: SearchCache = null;
	recentList: SearchRecentList = null;

	static getInstance($Bitrix, cache, recentList)
	{
		if (!this.instance)
		{
			this.instance = new this($Bitrix, cache, recentList);
		}

		return this.instance;
	}

	constructor($Bitrix, cache, recentList)
	{
		this.store = $Bitrix.Data.get('controller').store;
		this.cache = cache;
		this.recentList = recentList;
		this.restClient = $Bitrix.RestClient.get();

		this.onItemSelectHandler = this.onItemSelect.bind(this);
		this.onOpenNetworkItemHandler = this.onOpenNetworkItem.bind(this);
		EventEmitter.subscribe(EventType.search.selectItem, this.onItemSelectHandler);
		EventEmitter.subscribe(EventType.search.openNetworkItem, this.onOpenNetworkItemHandler);
	}

	//region Public methods

	loadRecentSearchFromCache(): Promise
	{
		return this.cache.loadRecentFromCache().then(responseFromCache => {
			Logger.warn('Im.Search: Recent search loaded from cache');

			return responseFromCache;
		}).then(responseFromCache => {
			const {items, recentItems} = responseFromCache;
			const itemMap = SearchUtils.createItemMap(items);

			return this.updateModels(itemMap).then(() => {
				return this.getItemsFromRecentItems(recentItems, itemMap);
			});
		});
	}

	loadRecentSearchFromServer(): Promise
	{
		return this.loadRecentFromServer().then(responseFromServer => {
			Logger.warn('Im.Search: Recent search loaded from server');
			const items = SearchUtils.createItemMap(responseFromServer.items);
			const recentItems = SearchUtils.prepareRecentItems(responseFromServer.recentItems);

			return this.updateModels(items, true).then(() => {
				return this.getItemsFromRecentItems(recentItems, items);
			});
		});
	}

	searchLocal(query: string)
	{
		const originalLayoutQuery = query.trim().toLowerCase();

		const searchInCachePromise = this.searchInCache(originalLayoutQuery);
		const searchInRecentListPromise = this.searchInRecentList(originalLayoutQuery);

		return Promise.all([searchInCachePromise, searchInRecentListPromise]).then(result => {
			// Spread order is important, because we have more data in cache than in recent list
			// (for example contextSort field)
			const items = new Map([...result[1], ...result[0]]);

			return this.getSortedItems(items, originalLayoutQuery);
		});
	}

	searchOnServer(query: string, config: Object): Promise
	{
		const originalLayoutQuery = query.trim().toLowerCase();

		let items = [];
		return this.searchRequest(originalLayoutQuery, config).then(itemsFromServer => {
			items = SearchUtils.createItemMap(itemsFromServer);

			return this.updateModels(items, true);
		}).then(() => {
			return this.allocateSearchResults(items, originalLayoutQuery);
		});
	}

	searchOnNetwork(query: string): Promise
	{
		const originalLayoutQuery = query.trim().toLowerCase();

		return this.searchOnNetworkRequest(originalLayoutQuery).then(items => {
			return SearchUtils.createItemMap(items);
		});
	}

	loadDepartmentUsers(parentItem: ImSearchItem): Promise
	{
		let items = [];
		return this.loadDepartmentUsersFromServer(parentItem).then(responseFromServer => {
			items = SearchUtils.createItemMap(responseFromServer);

			return this.updateModels(items, true);
		}).then(() => {
			return items;
		});
	}

	destroy()
	{
		this.cache.destroy();
		EventEmitter.unsubscribe(EventType.search.selectItem, this.onItemSelectHandler);
		EventEmitter.unsubscribe(EventType.search.openNetworkItem, this.onOpenNetworkItemHandler);
	}

	//endregion

	searchInCache(originalLayoutQuery: string): Promise
	{
		let wrongLayoutSearchPromise = Promise.resolve([]);
		if (this.needLayoutChange(originalLayoutQuery))
		{
			const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
			wrongLayoutSearchPromise = this.getItemsFromCacheByQuery(wrongLayoutQuery);
		}

		const correctLayoutSearchPromise = this.getItemsFromCacheByQuery(originalLayoutQuery);

		return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
			return new Map([...result[0], ...result[1]]);
		}).catch(error => {
			console.error('Unknown exception', error);

			return new Map();
		});
	}

	searchInRecentList(originalLayoutQuery: string): Promise
	{
		let wrongLayoutSearchPromise = Promise.resolve([]);
		if (this.needLayoutChange(originalLayoutQuery))
		{
			const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
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

		return this.recentList.search(queryWords);
	}

	getSearchConfig(): Object
	{
		return Config.get();
	}

	onItemSelect(event): void
	{
		const {selectedItem, onlyOpen} = event.getData();
		const item = [selectedItem.entityId, selectedItem.id];

		if (!onlyOpen)
		{
			this.cache.unshiftItem(item);
			this.addItemsToRecentSearchResults(item);
		}
	}

	onOpenNetworkItem(event): void
	{
		const code = event.getData();

		return new Promise((resolve, reject) => {
			this.restClient.callBatch(
				this.getDataRequestQuery(code),
				(result) => resolve(this.handleBatchRequestResult(result)),
				(error) => reject(error)
			);
		});
	}

	handleBatchRequestResult(result: Object): Object
	{
		if (result[RestMethodImopenlinesNetworkJoin] && result[RestMethodImopenlinesNetworkJoin].error())
		{
			return {
				error: result[RestMethodImopenlinesNetworkJoin].error().ex.error_description
			};
		}

		if (result[RestMethod.imUserGet] && result[RestMethod.imUserGet].error())
		{
			return {
				error: result[RestMethod.imUserGet].error().ex.error_description
			};
		}

		const user = result[RestMethod.imUserGet].data();
		this.store.dispatch('users/set', [user]);
		const dialogue = this.prepareChatForAdditionalUser(user);
		this.store.dispatch('dialogues/set', [dialogue]);

		return user;
	}

	prepareChatForAdditionalUser(user: Object): Object
	{
		return {
			dialogId: user.id,
			avatar: user.avatar,
			color: user.color,
			name: user.name,
			type: DialogType.user
		};
	}

	getDataRequestQuery(code: string): Object
	{
		const query = {
			[RestMethodImopenlinesNetworkJoin]: [RestMethodImopenlinesNetworkJoin, {code: code}]
		};

		query[RestMethod.imUserGet] = [
			RestMethod.imUserGet,
			{
				id: `$result[${RestMethodImopenlinesNetworkJoin}]`
			}
		];

		return query;
	}

	getItemsFromCacheByQuery(query: string): Promise
	{
		const queryWords = SearchUtils.getWordsFromString(query);

		return this.cache.search(queryWords).then(cacheItems => {
			const items = SearchUtils.createItemMap(cacheItems);
			return this.updateModels(items).then(() => items);
		});
	}

	getSortedItems(items: Map<string, SearchItem>, originalLayoutQuery: string): Map<string, SearchItem>
	{
		let sortedItems = this.sortItemsBySearchField(items, originalLayoutQuery);
		sortedItems = this.sortItemsByEntityIdAndContextSort(sortedItems);

		return sortedItems;
	}

	sortItemsBySearchField(items: Map<string, SearchItem>, originalLayoutQuery: string): Map<string, SearchItem>
	{
		let queryWords = SearchUtils.getWordsFromString(originalLayoutQuery);
		if (this.needLayoutChange(originalLayoutQuery))
		{
			const wrongLayoutQueryWords = SearchUtils.getWordsFromString(this.changeLayout(originalLayoutQuery));
			queryWords = [...queryWords, ...wrongLayoutQueryWords];
		}
		const uniqueWords = [...new Set(queryWords)];

		const searchFieldsWeight = {
			title: 10_000,
			name: 1000,
			lastName: 100,
			position: 1,
		};

		items.forEach(item => {
			uniqueWords.forEach(word => {
				if (item.getTitle().toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.title);
				}
				else if (item.getName()?.toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.name);
				}
				else if (item.getLastName()?.toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.lastName);
				}
				else if (item.getPosition()?.toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.position);
				}
			});
		});

		return new Map([...items.entries()].sort((firstItem, secondItem) => {
			const [, firstItemValue] = firstItem;
			const [, secondItemValue] = secondItem;

			return secondItemValue.getCustomSort() - firstItemValue.getCustomSort();
		}));
	}

	sortItemsByEntityIdAndContextSort(items: Map<string, SearchItem>): Map<string, SearchItem>
	{
		const entityWeight = {
			'user': 100,
			'im-chat': 80,
			'im-chat-user': 80,
			'im-bot': 70,
			'department': 60,
			'extranet': 10,
		};

		return new Map([...items.entries()].sort((firstItem, secondItem) => {
			const [, firstItemValue] = firstItem;
			const [, secondItemValue] = secondItem;

			const secondItemEntityId = secondItemValue.isExtranet() ? 'extranet' : secondItemValue.getEntityId();
			const firstItemEntityId = firstItemValue.isExtranet() ? 'extranet' : firstItemValue.getEntityId();

			if (entityWeight[secondItemEntityId] < entityWeight[firstItemEntityId])
			{
				return -1;
			}
			else if (entityWeight[secondItemEntityId] > entityWeight[firstItemEntityId])
			{
				return 1;
			}
			else
			{
				return secondItemValue.getContextSort() - firstItemValue.getContextSort();
			}
		}));
	}

	loadRecentFromServer(): Promise
	{
		const config = {
			json: this.getSearchConfig()
		};

		const chatEntity = Config.getChatEntity();
		chatEntity.options.searchableChatTypes = ['C', 'O'];
		config.json.dialog.entities.push(chatEntity);

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.load', config).then(response => {
				Logger.warn(`Im.Search: Recent search request result`, response);
				this.cache.save(response.data.dialog);

				resolve(response.data.dialog);
			}).catch(error => reject(error));
		});
	}

	loadDepartmentUsersFromServer(parentItem: ImSearchItem): Promise
	{
		const config = {
			json: {
				...this.getSearchConfig(),
				parentItem
			}
		};

		const departmentEntity = Config.getDepartmentEntity();
		config.json.dialog.entities.push(departmentEntity);

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.getChildren', config).then(response => {
				Logger.warn('Im.Search: load department users result', response);
				this.cache.save(response.data.dialog);
				resolve(response.data.dialog.items);
			}).catch(error => reject(error));
		});
	}

	searchRequest(query: string, requestConfig: Object): Promise
	{
		const config = {
			json: this.getSearchConfig()
		};

		if (requestConfig.network)
		{
			const networkEntity = Config.getNetworkEntity();
			config.json.dialog.entities.push(networkEntity);
		}

		if (requestConfig.departments)
		{
			const departmentEntity = Config.getDepartmentEntity();
			config.json.dialog.entities.push(departmentEntity);
		}

		const chatEntity = Config.getChatEntity();
		config.json.dialog.entities.push(chatEntity);

		config.json.searchQuery = {
			'queryWords': SearchUtils.getWordsFromString(query.trim()),
			'query': query.trim(),
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.doSearch', config).then(response => {
				Logger.warn(`Im.Search: Search request result`, response);
				this.cache.save(response.data.dialog);

				resolve(response.data.dialog.items);
			}).catch(error => reject(error));
		});
	}

	searchOnNetworkRequest(query: string): Promise
	{
		const config = {
			json: this.getSearchConfig()
		};

		const networkEntity = Config.getNetworkEntity();

		config.json.dialog.entities = [networkEntity];
		config.json.searchQuery = {
			'queryWords': SearchUtils.getWordsFromString(query.trim()),
			'query': query.trim(),
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.doSearch', config).then(response => {
				Logger.warn(`Im.Search: Network Search request result`, response);

				resolve(response.data.dialog.items);
			}).catch(error => reject(error));
		});
	}

	addItemsToRecentSearchResults(recentItem: Array<string, number>): void
	{
		const [entityId, id] = recentItem;
		const recentItems = [{id, entityId}];

		const config = {
			json: {
				...this.getSearchConfig(),
				recentItems
			},
		};

		const chatEntity = Config.getChatEntity();
		config.json.dialog.entities.push(chatEntity);

		Ajax.runAction('ui.entityselector.saveRecentItems', config);
	}

	updateModels(items: Map<string, SearchItem>, set: boolean = false): Promise
	{
		const {users, dialogues} = this.prepareDataForModels(items);

		const usersActionName = set ? 'users/set' : 'users/add';
		const dialoguesActionName = set ? 'dialogues/set' : 'dialogues/add';

		const usersPromise = this.store.dispatch(usersActionName, users);
		const dialoguesPromise = this.store.dispatch(dialoguesActionName, dialogues);

		return Promise.all([usersPromise, dialoguesPromise]);
	}

	prepareDataForModels(items: Map<string, SearchItem>): { users: Array<Object>, dialogues: Array<Object> }
	{
		const result = {
			users: [],
			dialogues: [],
		};

		items.forEach(item => {
			if (!item.getCustomData())
			{
				return;
			}

			// user
			if (item.isUser())
			{
				const preparedUser = SearchUtils.convertKeysToLowerCase(item.getUserCustomData());
				result.users.push(preparedUser);

				result.dialogues.push({
					avatar: preparedUser.avatar,
					color: preparedUser.color,
					name: preparedUser.name,
					type: DialogType.user,
					dialogId: item.getId()
				});
			}

			// chat
			if (item.isChat() && !item.isOpeLinesType())
			{
				const chat = SearchUtils.convertKeysToLowerCase(item.getChatCustomData());

				result.dialogues.push({
					...chat,
					dialogId: `chat${chat.id}`
				});
			}
		});

		return result;
	}

	getItemsFromRecentItems(recentItems: Array<Object>, items: Map<string, SearchItem>): Map<string, SearchItem>
	{
		const filledRecentItems = new Map();
		recentItems.forEach(recentItem => {
			const itemFromMap = items.get(recentItem.cacheId);
			if (itemFromMap && !itemFromMap.isOpeLinesType())
			{
				filledRecentItems.set(itemFromMap.getEntityFullId(), itemFromMap);
			}
		});

		return filledRecentItems;
	}

	allocateSearchResults(items: Map<string, SearchItem>, originalLayoutQuery: string): Object
	{
		const usersAndChats = new Map();
		const chatUsers = new Map();
		const departments = new Map();
		const openLines = new Map();
		const network = new Map();

		items.forEach(item => {
			switch (item.getEntityId())
			{
				case EntityIdTypes.chatUser:
				{
					chatUsers.set(item.getEntityFullId(), item);
					break;
				}
				case EntityIdTypes.department:
				{
					departments.set(item.getEntityFullId(), item);
					break;
				}
				case EntityIdTypes.network:
				{
					network.set(item.getEntityFullId(), item);
					break;
				}
				default:
				{
					if (item.isOpeLinesType())
					{
						openLines.set(item.getEntityFullId(), item);
					}
					else
					{
						usersAndChats.set(item.getEntityFullId(), item);
					}
				}
			}
		});

		return {
			usersAndChats: this.getSortedItems(usersAndChats, originalLayoutQuery),
			chatUsers: chatUsers,
			departments: departments,
			openLines: openLines,
			network: network
		};
	}

	isRussianInterface(): boolean
	{
		return this.store.state.application.common.languageId === 'ru';
	}

	changeLayout(query: string): string
	{
		if (this.isRussianInterface() && BX.correctText)
		{
			// eslint-disable-next-line bitrix-rules/no-bx
			return BX.correctText(query, {replace_way: 'AUTO'});
		}

		return query;
	}

	needLayoutChange(originalLayoutQuery: string): boolean
	{
		const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
		const isIdenticalQuery = wrongLayoutQuery === originalLayoutQuery;

		return this.isRussianInterface() && !isIdenticalQuery;
	}
}