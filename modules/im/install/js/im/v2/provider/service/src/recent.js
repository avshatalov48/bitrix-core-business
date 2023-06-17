import {EventEmitter} from 'main.core.events';
import {RestClient} from 'rest.client';

import {Messenger} from 'im.public';
import {Core} from 'im.v2.application.core';
import {DialogType, EventType, Layout, RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

import type {ImModelRecentItem} from 'im.v2.model';

export class RecentService
{
	static instance = null;

	store: Object = null;
	restClient: RestClient = null;

	dataIsPreloaded: boolean = false;
	itemsPerPage: number = 50;
	isLoading: boolean = false;
	pagesLoaded: number = 0;
	hasMoreItemsToLoad: boolean = true;
	lastMessageDate: string = null;

	static getInstance(): RecentService
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();

		this.onUpdateStateHandler = this.onUpdateState.bind(this);
		EventEmitter.subscribe(EventType.recent.updateState, this.onUpdateStateHandler);
	}

	// region public
	getCollection(): ImModelRecentItem[]
	{
		return this.store.getters['recent/getRecentCollection'];
	}

	loadFirstPage({ignorePreloadedItems = false} = {}): Promise
	{
		if (this.dataIsPreloaded && !ignorePreloadedItems)
		{
			Logger.warn(`Im.RecentList: first page was preloaded`);

			return Promise.resolve();
		}
		this.isLoading = true;

		return this.requestItems({firstPage: true});
	}

	loadNextPage(): Promise
	{
		if (this.isLoading || !this.hasMoreItemsToLoad)
		{
			return Promise.resolve();
		}

		this.isLoading = true;

		return this.requestItems();
	}

	setPreloadedData(params)
	{
		Logger.warn(`Im.RecentList: setting preloaded data`, params);
		const {items, hasMore} = params;

		this.lastMessageDate = this.getLastMessageDate(items);

		if (!hasMore)
		{
			this.hasMoreItemsToLoad = false;
		}

		this.dataIsPreloaded = true;

		this.updateModels(params);
	}

	hideChat(dialogId)
	{
		Logger.warn(`Im.RecentList: hide chat`, dialogId);
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/delete', {
			id: dialogId
		});

		const chatIsOpened = this.store.getters['application/isChatOpen'](dialogId);
		if (chatIsOpened)
		{
			Messenger.openChat();
		}

		this.restClient.callMethod(RestMethod.imRecentHide, {'DIALOG_ID': dialogId}).catch(error => {
			console.error('Im.RecentList: hide chat error', error);
		});
	}
	// endregion public

	requestItems({firstPage = false} = {}): Promise
	{
		const queryParams = this.getQueryParams(firstPage);

		return this.restClient.callMethod(this.getQueryMethod(), queryParams).then((result) => {
			this.pagesLoaded++;
			Logger.warn(`Im.RecentList: ${firstPage? 'First' : this.pagesLoaded} page request result`, result.data());
			const {items, hasMore} = result.data();

			this.lastMessageDate = this.getLastMessageDate(items);

			if (!hasMore)
			{
				this.hasMoreItemsToLoad = false;
			}

			return this.updateModels(result.data()).then(() => {
				this.isLoading = false;
			});
		}).catch(error => {
			console.error('Im.RecentList: page request error', error);
		});
	}

	getQueryMethod(): string
	{
		return RestMethod.imRecentList;
	}

	getQueryParams(firstPage: boolean): Object
	{
		return {
			'SKIP_OPENLINES': 'Y',
			'LIMIT': this.itemsPerPage,
			'LAST_MESSAGE_DATE': firstPage? null : this.lastMessageDate,
			'GET_ORIGINAL_TEXT': 'Y'
		};
	}

	updateModels(rawData): Promise
	{
		const {users, dialogues, recent} = this.prepareDataForModels(rawData);

		const usersPromise = this.store.dispatch('users/set', users);
		if (rawData.botList)
		{
			this.store.dispatch('users/setBotList', rawData.botList);
		}
		const dialoguesPromise = this.store.dispatch('dialogues/set', dialogues);
		const recentPromise = this.store.dispatch('recent/setRecent', recent);

		return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	}

	onUpdateState({data})
	{
		Logger.warn(`Im.RecentList: setting UpdateState data`, data);
		this.updateModels(data);
	}

	prepareDataForModels({items, birthdayList = []}): {users: Array, dialogues: Array, recent: Array}
	{
		const result = {
			users: [],
			dialogues: [],
			recent: []
		};

		items.forEach(item => {
			// user
			if (item.user && item.user.id && !this.isAddedAlready(result, 'users', item.user.id))
			{
				result.users.push(item.user);
			}

			// chat
			if (item.chat)
			{
				result.dialogues.push(this.prepareGroupChat(item));

				if (item.user.id && !this.isAddedAlready(result, 'dialogues', item.user.id))
				{
					result.dialogues.push(this.prepareChatForAdditionalUser(item.user));
				}
			}
			else if (item.user.id)
			{
				const existingRecentItem = this.store.getters['recent/get'](item.user.id);
				// we should not update real chat with "default" chat data
				if (!existingRecentItem || !item.options.default_user_record)
				{
					result.dialogues.push(this.prepareChatForUser(item));
				}
			}

			// recent
			result.recent.push({...item});
		});

		birthdayList.forEach(item => {
			if (!this.isAddedAlready(result, 'users', item.id))
			{
				result.users.push(item);
				result.dialogues.push(this.prepareChatForAdditionalUser(item));
			}

			if (!this.isAddedAlready(result, 'recent', item.id))
			{
				result.recent.push(this.getBirthdayPlaceholder(item));
			}
		});

		Logger.warn(`Im.RecentList: prepared data for models`, result);

		return result;
	}

	isAddedAlready(result: Object, type: 'users' | 'dialogues' | 'recent', id: string | number): boolean
	{
		if (type === 'users')
		{
			return result.users.some(user => user.id === id);
		}
		else if (type === 'dialogues')
		{
			return result.dialogues.some(chat => chat.dialogId === id);
		}
		else if (type === 'recent')
		{
			return result.recent.some(item => item.id === id);
		}

		return false;
	}

	prepareGroupChat(item)
	{
		return {
			...item.chat,
			counter: item.counter,
			dialogId: item.id
		};
	}

	prepareChatForUser(item)
	{
		return {
			chatId: item.chat_id,
			avatar: item.user.avatar,
			color: item.user.color,
			dialogId: item.id,
			name: item.user.name,
			type: DialogType.user,
			counter: item.counter
		};
	}

	prepareChatForAdditionalUser(user)
	{
		return {
			dialogId: user.id,
			avatar: user.avatar,
			color: user.color,
			name: user.name,
			type: DialogType.user
		};
	}

	getBirthdayPlaceholder(item: Object): Object
	{
		return {
			id: item.id,
			options: {
				birthdayPlaceholder: true
			}
		};
	}

	getLastMessageDate(items: Array): string
	{
		if (items.length === 0)
		{
			return '';
		}

		return items.slice(-1)[0].message.date;
	}
}