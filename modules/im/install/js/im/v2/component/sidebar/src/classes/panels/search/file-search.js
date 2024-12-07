import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from '../helpers/get-chat-id';
import { getLastElementId } from '../helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

type RestResponse = {
	files: [],
	list: [],
	users: [],
};

type UrlGetQueryParams = {
	CHAT_ID: number,
	LIMIT: number,
	SUBTYPE: string,
	SEARCH_FILE_NAME?: string,
	LAST_ID?: number,
};

export class FileSearch
{
	store: Store;
	dialogId: string;
	userManager: UserManager;
	restClient: RestClient;
	hasMoreItemsToLoad: boolean = true;
	#query: string = '';

	constructor({ dialogId }: {dialogId: string})
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.dialogId = dialogId;
		this.chatId = getChatId(dialogId);
		this.userManager = new UserManager();
	}

	searchOnServer(query: string, subType: string): Promise<number[]>
	{
		if (this.#query !== query)
		{
			this.#query = query;
			this.hasMoreItemsToLoad = true;
		}

		return this.request(subType);
	}

	resetSearchState()
	{
		this.#query = '';
		this.hasMoreItemsToLoad = true;
		void this.store.dispatch('sidebar/files/clearSearch', {});
	}

	async request(subType: string): Promise<number[]>
	{
		const queryParams = this.getQueryParams(subType);
		let responseData: RestResponse = {};
		try
		{
			const response = await this.restClient.callMethod(RestMethod.imChatFileGet, queryParams);
			responseData = response.data();
		}
		catch (error)
		{
			console.error('SidebarSearch: Im.imChatFileGet: page request error', error);
		}

		return this.#processSearchResponse(responseData);
	}

	#processSearchResponse(response: RestResponse): Promise<number[]>
	{
		return this.updateModels(response).then(() => {
			return response.files.map((file) => file.id);
		});
	}

	updateModels(resultData: RestResponse): Promise
	{
		const { list, users, files, tariffRestrictions = {} } = resultData;

		const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
		const historyLimitPromise = this.store.dispatch('sidebar/files/setHistoryLimitExceeded', {
			chatId: this.chatId,
			isHistoryLimitExceeded,
		});
		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setFilesPromise = this.store.dispatch('files/set', files);

		const sortedList = {};
		list.forEach((file) => {
			if (!sortedList[file.subType])
			{
				sortedList[file.subType] = [];
			}
			sortedList[file.subType].push(file);
		});

		const setSidebarFilesPromises = [];
		Object.keys(sortedList).forEach((subType) => {
			const listByType = sortedList[subType];
			setSidebarFilesPromises.push(
				this.store.dispatch('sidebar/files/setSearch', {
					chatId: this.chatId,
					files: listByType,
					subType,
				}),
				this.store.dispatch('sidebar/files/setHasNextPageSearch', {
					chatId: this.chatId,
					subType,
					hasNextPage: listByType.length === REQUEST_ITEMS_LIMIT,
				}),
				this.store.dispatch('sidebar/files/setLastIdSearch', {
					chatId: this.chatId,
					subType,
					lastId: getLastElementId(listByType),
				}),
			);
		});

		return Promise.all([
			setFilesPromise, addUsersPromise, historyLimitPromise, ...setSidebarFilesPromises,
		]);
	}

	loadNextPage(subType: string, searchQuery: string): Promise
	{
		if (this.#query !== searchQuery)
		{
			this.#query = searchQuery;
		}

		return this.request(subType);
	}

	getQueryParams(subType: string): UrlGetQueryParams
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			SEARCH_FILE_NAME: this.#query,
			SUBTYPE: subType.toUpperCase(),
			LIMIT: REQUEST_ITEMS_LIMIT,
		};

		const lastId = this.store.getters['sidebar/files/getSearchResultCollectionLastId'](this.chatId, subType);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}
}
