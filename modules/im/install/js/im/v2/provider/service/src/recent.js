import { RestClient } from 'rest.client';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';

import { RecentDataExtractor } from './classes/recent-data-extractor';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem } from 'im.v2.model';

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
	}

	// region public
	getCollection(): ImModelRecentItem[]
	{
		return this.store.getters['recent/getRecentCollection'];
	}

	loadFirstPage({ ignorePreloadedItems = false } = {}): Promise
	{
		if (this.dataIsPreloaded && !ignorePreloadedItems)
		{
			Logger.warn('Im.RecentList: first page was preloaded');

			return Promise.resolve();
		}
		this.isLoading = true;

		return this.requestItems({ firstPage: true });
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
		Logger.warn('Im.RecentList: setting preloaded data', params);
		const { items, hasMore } = params;

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
		Logger.warn('Im.RecentList: hide chat', dialogId);
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (!recentItem)
		{
			return;
		}

		this.store.dispatch('recent/delete', {
			id: dialogId,
		});

		const chatIsOpened = this.store.getters['application/isChatOpen'](dialogId);
		if (chatIsOpened)
		{
			Messenger.openChat();
		}

		this.restClient.callMethod(RestMethod.imRecentHide, { DIALOG_ID: dialogId }).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('Im.RecentList: hide chat error', error);
		});
	}
	// endregion public

	requestItems({ firstPage = false } = {}): Promise
	{
		const queryParams = this.getQueryParams(firstPage);

		return this.restClient.callMethod(this.getQueryMethod(), queryParams)
			.then((result) => {
				this.pagesLoaded++;
				Logger.warn(`Im.RecentList: ${firstPage ? 'First' : this.pagesLoaded} page request result`, result.data());
				const { items, hasMore } = result.data();

				this.lastMessageDate = this.getLastMessageDate(items);

				if (!hasMore)
				{
					this.hasMoreItemsToLoad = false;
				}

				return this.updateModels(result.data());
			})
			.then(() => {
				this.isLoading = false;

				return true;
			})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Im.RecentList: page request error', error);
			});
	}

	getQueryMethod(): string
	{
		return RestMethod.imRecentList;
	}

	getQueryParams(firstPage: boolean): JsonObject
	{
		return {
			SKIP_OPENLINES: 'Y',
			LIMIT: this.itemsPerPage,
			LAST_MESSAGE_DATE: firstPage ? null : this.lastMessageDate,
			GET_ORIGINAL_TEXT: 'Y',
		};
	}

	getModelSaveMethod(): string
	{
		return 'recent/setRecent';
	}

	updateModels(rawData): Promise
	{
		const extractor = new RecentDataExtractor({ rawData, ...this.getExtractorOptions() });
		const extractedItems = extractor.getItems();
		const { users, chats, recentItems } = extractedItems;
		Logger.warn('RecentService: prepared data for models', extractedItems);

		const usersPromise = this.store.dispatch('users/set', users);
		const dialoguesPromise = this.store.dispatch('chats/set', chats);
		const recentPromise = this.store.dispatch(this.getModelSaveMethod(), recentItems);

		return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	}

	getLastMessageDate(items: Array): string
	{
		if (items.length === 0)
		{
			return '';
		}

		return items.slice(-1)[0].message.date;
	}

	getExtractorOptions(): { withBirthdays?: boolean }
	{
		return {};
	}
}
