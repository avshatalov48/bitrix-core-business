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
	GROUP: string,
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

	searchOnServer(query: string, group: string): Promise<number[]>
	{
		if (this.#query !== query)
		{
			this.#query = query;
			this.hasMoreItemsToLoad = true;
		}

		return this.request(group);
	}

	resetSearchState()
	{
		this.#query = '';
		this.hasMoreItemsToLoad = true;
		void this.store.dispatch('sidebar/files/clearSearch', {});
	}

	async request(group: string): Promise<number[]>
	{
		const queryParams = this.getQueryParams(group);
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

		const sortedGroups = {};
		list.forEach((file) => {
			if (!sortedGroups[file.group])
			{
				sortedGroups[file.group] = [];
			}
			sortedGroups[file.group].push(file);
		});

		const setSidebarFilesPromises = [];
		Object.keys(sortedGroups).forEach((group) => {
			const listByType = sortedGroups[group];
			setSidebarFilesPromises.push(
				this.store.dispatch('sidebar/files/setSearch', {
					chatId: this.chatId,
					files: listByType,
					group,
				}),
				this.store.dispatch('sidebar/files/setHasNextPageSearch', {
					chatId: this.chatId,
					group,
					hasNextPage: listByType.length === REQUEST_ITEMS_LIMIT,
				}),
				this.store.dispatch('sidebar/files/setLastIdSearch', {
					chatId: this.chatId,
					group,
					lastId: getLastElementId(listByType),
				}),
			);
		});

		return Promise.all([
			setFilesPromise, addUsersPromise, historyLimitPromise, ...setSidebarFilesPromises,
		]);
	}

	loadNextPage(group: string, searchQuery: string): Promise
	{
		if (this.#query !== searchQuery)
		{
			this.#query = searchQuery;
		}

		return this.request(group);
	}

	getQueryParams(group: string): UrlGetQueryParams
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			SEARCH_FILE_NAME: this.#query,
			GROUP: group.toUpperCase(),
			LIMIT: REQUEST_ITEMS_LIMIT,
		};

		const lastId = this.store.getters['sidebar/files/getSearchResultCollectionLastId'](this.chatId, group);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}
}
