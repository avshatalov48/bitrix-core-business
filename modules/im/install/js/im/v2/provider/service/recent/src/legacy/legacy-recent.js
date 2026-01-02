import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { CopilotManager } from 'im.v2.lib.copilot';
import { LayoutManager } from 'im.v2.lib.layout';

import { RecentDataExtractor } from './classes/recent-data-extractor';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem } from 'im.v2.model';

export class LegacyRecentService
{
	static instance = null;

	dataIsPreloaded: boolean = false;
	firstPageIsLoaded: boolean = false;
	itemsPerPage: number = 50;
	isLoading: boolean = false;
	pagesLoaded: number = 0;
	hasMoreItemsToLoad: boolean = true;
	lastMessageDate: string = null;

	static getInstance(): LegacyRecentService
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	// region public
	getCollection(): ImModelRecentItem[]
	{
		return Core.getStore().getters['recent/getRecentCollection'];
	}

	async loadFirstPage({ ignorePreloadedItems = false } = {}): Promise
	{
		if (this.dataIsPreloaded && !ignorePreloadedItems)
		{
			Logger.warn('Im.RecentList: first page was preloaded');

			return Promise.resolve();
		}
		this.isLoading = true;

		const result = await this.requestItems({ firstPage: true });
		this.firstPageIsLoaded = true;

		return result;
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

		void this.updateModels(params);
	}

	hideChat(dialogId: string): void
	{
		Logger.warn('Im.RecentList: hide chat', dialogId);
		const recentItem = Core.getStore().getters['recent/get'](dialogId);
		if (!recentItem)
		{
			return;
		}

		void Core.getStore().dispatch('recent/delete', { id: dialogId });

		const chatIsOpened = Core.getStore().getters['application/isChatOpen'](dialogId);
		if (chatIsOpened)
		{
			LayoutManager.getInstance().clearCurrentLayoutEntityId();
			void LayoutManager.getInstance().deleteLastOpenedElementById(dialogId);
		}

		Core.getRestClient().callMethod(RestMethod.imRecentHide, { DIALOG_ID: dialogId })
			.catch((result: RestResult) => {
				console.error('Im.RecentList: hide chat error', result.error());
			});
	}
	// endregion public

	async requestItems({ firstPage = false } = {}): Promise
	{
		const queryParams = this.getQueryParams(firstPage);

		const result: RestResult = await Core.getRestClient().callMethod(RestMethod.imRecentList, queryParams)
			.catch((errorResult: RestResult) => {
				console.error('Im.RecentList: page request error', errorResult.error());
			});

		this.pagesLoaded++;
		Logger.warn(`Im.RecentList: ${firstPage ? 'First' : this.pagesLoaded} page request result`, result.data());
		const { items, hasMore } = result.data();
		this.lastMessageDate = this.getLastMessageDate(items);
		if (!hasMore)
		{
			this.hasMoreItemsToLoad = false;
		}

		this.isLoading = false;

		return this.updateModels(result.data());
	}

	getQueryParams(firstPage: boolean): JsonObject
	{
		return {
			SKIP_OPENLINES: 'Y',
			LIMIT: this.itemsPerPage,
			LAST_MESSAGE_DATE: firstPage ? null : this.lastMessageDate,
			GET_ORIGINAL_TEXT: 'Y',
			PARSE_TEXT: 'Y',
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
		const {
			users,
			chats,
			messages,
			files,
			recentItems,
			copilot,
			messagesAutoDeleteConfigs,
		} = extractedItems;
		Logger.warn('LegacyRecentService: prepared data for models', extractedItems);

		const usersPromise = Core.getStore().dispatch('users/set', users);
		const dialoguesPromise = Core.getStore().dispatch('chats/set', chats);
		const autoDeletePromise = Core.getStore().dispatch('chats/autoDelete/set', messagesAutoDeleteConfigs);
		const messagesPromise = Core.getStore().dispatch('messages/store', messages);
		const filesPromise = Core.getStore().dispatch('files/set', files);
		const recentPromise = Core.getStore().dispatch(this.getModelSaveMethod(), recentItems);

		const copilotManager = new CopilotManager();
		const copilotPromise = copilotManager.handleRecentListResponse(copilot);

		return Promise.all([
			usersPromise,
			dialoguesPromise,
			messagesPromise,
			filesPromise,
			recentPromise,
			copilotPromise,
			autoDeletePromise,
		]);
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
