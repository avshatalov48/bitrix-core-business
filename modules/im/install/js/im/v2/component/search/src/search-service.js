import {ajax as Ajax, Type} from 'main.core';
import {Logger} from 'im.v2.lib.logger';
import {ChatTypes, EventType} from 'im.v2.const';
import {EventEmitter} from 'main.core.events';
import {SearchCacheService} from './search-cache-service';
import {EntityIdTypes, ImSearchItem} from './type/search-item';
import {Config} from './search-config';

export class SearchService
{
	store: Object = null;
	cacheService: Object = null;

	constructor($Bitrix)
	{
		Logger.enable('log');
		Logger.enable('warn');

		this.store = $Bitrix.Data.get('controller').store;
		this.cacheService = new SearchCacheService();

		this.onItemSelectHandler = this.onItemSelect.bind(this);
		EventEmitter.subscribe(EventType.search.selectItem, this.onItemSelectHandler);
	}

	getJsonConfig()
	{
		return Config;
	}

	onItemSelect(event)
	{
		const dialogId = event.getData();

		const recentItem = this.dialogIdToRecentItem(dialogId);

		this.cacheService.unshiftItem(recentItem);
		this.addItemsToRecentSearchResults([{id: recentItem[1], entityId: recentItem[0]}]);
	}

	loadDepartmentUsers(parentItem)
	{
		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.getChildren', {
					json: {
						...this.getJsonConfig(),
						parentItem: parentItem
					}
				})
				.then(response => {
					Logger.warn(`Im.Search: load department users result`, response);
					this.cacheService.saveToCache(response.data.dialog);

					resolve(response.data.dialog.items);
				})
				.catch(error => reject(error));
		});
	}

	loadRecentSearch()
	{
		return this.cacheService.loadRecentFromCache().then(result => {
			if (result.recentItems.length === 0)
			{
				return this.requestRecentSearch().then(response => {
					return this.updateModels(response.items).then(() => {
						return this.convertRecentItemsToDialogIds(response.recentItems);
					});
				});
			}
			this.updateModels(result.items).then(() => {
				this.requestRecentSearch();
			});

			return this.convertRecentItemsToDialogIds(result.recentItems);
		});
	}

	searchInCache(query: string)
	{
		let wrongLayoutSearchPromise = Promise.resolve([]);
		if (this.store.state.application.common.languageId === 'ru' && BX.correctText)
		{
			// eslint-disable-next-line bitrix-rules/no-bx
			const wrongLayoutQueryWords = this.splitQueryByWords(BX.correctText(query.trim()));
			wrongLayoutSearchPromise = this.getDialogIdsByQueryWords(wrongLayoutQueryWords);
		}

		const correctLayoutQueryWords = this.splitQueryByWords(query.trim());
		const correctLayoutSearchPromise = this.getDialogIdsByQueryWords(correctLayoutQueryWords);

		return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
			return [...new Set([...result[0], ...result[1]])];
		});
	}

	getDialogIdsByQueryWords(queryWords: Array<string>)
	{
		return this.cacheService.search(queryWords).then(items => {
			return this.updateModels(items).then(() => {
				return this.convertItemsToDialogIds(items);
			});
		});
	}

	requestRecentSearch()
	{
		return Ajax.runAction('ui.entityselector.load', {
				json: this.getJsonConfig(),
			})
			.then(response => {
				Logger.warn(`Im.Search: Recent search request result`, response);
				this.cacheService.saveToCache(response.data.dialog);

				return response.data.dialog;
			});
	}

	searchOnServer(query)
	{
		return this.searchRequest(query).then(items => {
			return this.updateModels(items, true).then(() => {
				return {
					items: this.convertItemsToDialogIds(items),
					departments: items.filter(item => item.entityId === EntityIdTypes.department)
				};
			});
		});
	}

	searchRequest(query)
	{
		const config = this.getJsonConfig();
		const queryWords = this.splitQueryByWords(query);

		config.searchQuery = {
			'queryWords': queryWords,
			'query': query,
			'dynamicSearchEntities': []
		};

		return new Promise((resolve, reject) => {
			Ajax.runAction('ui.entityselector.doSearch', {
					json: config,
				})
				.then(response => {
					Logger.warn(`Im.Search: Search request result`, response);
					this.cacheService.saveToCache(response.data.dialog);

					resolve(response.data.dialog.items);
				})
				.catch(error => reject(error));
		});
	}

	// todo: refactor (debounce & queue)
	addItemsToRecentSearchResults(recentItems)
	{
		if (!Type.isArrayFilled(recentItems))
		{
			return;
		}

		return Ajax.runAction('ui.entityselector.saveRecentItems', {
			json: {
				...this.getJsonConfig(),
				recentItems
			},
		});
	}

	updateModels(rawItems, set = false): Promise
	{
		const {users, dialogues} = this.prepareDataForModels(rawItems);

		const usersActionName = set ? 'users/set' : 'users/add';
		const dialoguesActionName = set ? 'dialogues/set' : 'dialogues/add';

		const usersPromise = this.store.dispatch(usersActionName, users);
		const dialoguesPromise = this.store.dispatch(dialoguesActionName, dialogues);

		return Promise.all([usersPromise, dialoguesPromise]);
	}

	prepareDataForModels(items: Array<ImSearchItem>): Object
	{
		const result = {
			users: [],
			dialogues: [],
		};

		items.forEach(item => {
			if (!item.customData)
			{
				return;
			}

			// user
			if (item.customData.imUser && item.customData.imUser.ID > 0)
			{
				const preparedUser = this.toLowerCaseKeys(item.customData.imUser);
				result.users.push(preparedUser);

				result.dialogues.push({
					avatar: preparedUser.avatar,
					color: preparedUser.color,
					name: preparedUser.name,
					type: ChatTypes.user,
					dialogId: item.id
				});
			}

			// chat
			if (item.customData.imChat && item.customData.imChat.ID > 0)
			{
				if (item.entityType === 'LINES')
				{
					return;
				}
				const chat = this.toLowerCaseKeys(item.customData.imChat);
				result.dialogues.push({
					...chat,
					dialogId: `chat${chat.id}`
				});
			}
		});

		return result;
	}

	// todo: move somewhere else
	splitQueryByWords(query)
	{
		const clearedQuery = query
			.replace('(', ' ')
			.replace(')', ' ')
			.replace('[', ' ')
			.replace(']', ' ')
			.replace('{', ' ')
			.replace('}', ' ')
			.replace('<', ' ')
			.replace('>', ' ')
			.replace('-', ' ')
			.replace('#', ' ')
			.replace('"', ' ')
			.replace('\'', ' ')
			.replace('/ss+/', ' ')
		;

		return clearedQuery
			.toLowerCase()
			.split(' ')
			.filter(word => word !== '')
			;
	}

	toLowerCaseKeys(object)
	{
		const result = {};
		Object.keys(object).forEach(key => {
			result[key.toLowerCase()] = object[key];
		});

		return result;
	}

	convertRecentItemsToDialogIds(recentItems: Array<Array<string, number>>): Array<string>
	{
		const dialogIds = [];

		recentItems.forEach(item => {
			if (item[0] === EntityIdTypes.chat)
			{
				dialogIds.push(`chat${item[1]}`);
			}
			else if (item[0] === EntityIdTypes.user || item[0] === EntityIdTypes.bot)
			{
				dialogIds.push(item[1].toString());
			}
		});

		return dialogIds;
	}

	convertItemsToDialogIds(items: Array<ImSearchItem>): Array<string>
	{
		const dialogIds = [];

		items.forEach(item => {
			if (item.entityType === 'LINES')
			{
				return;
			}

			if (item.customData && item.customData.imChat && item.customData.imChat.ID > 0)
			{
				dialogIds.push(`chat${item.customData.imChat.ID}`);
			}
			else if (item.customData && item.customData.imUser && item.customData.imUser.ID > 0)
			{
				dialogIds.push(item.customData.imUser.ID.toString());
			}
		});

		return dialogIds;
	}

	dialogIdToRecentItem(dialogId: string): Array<string, number>
	{
		return dialogId.startsWith('chat')
			? [EntityIdTypes.chat, Number.parseInt(dialogId.replace('chat', ''), 10)]
			: [EntityIdTypes.user, Number.parseInt(dialogId, 10)];
	}
}