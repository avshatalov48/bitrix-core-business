import { Type } from 'main.core';

import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from '../helpers/get-chat-id';

import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

type UrlGetQueryParams = {
	CHAT_ID: number,
	LIMIT: number,
	OFFSET?: number,
	SEARCH_URL?: string,
}

type RestResponse = {
	list: [],
	users: [],
};

export class LinkSearch
{
	store: Store;
	dialogId: string;
	chatId: number;
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

	searchOnServer(query: string): Promise<number[]>
	{
		if (this.#query !== query)
		{
			this.#query = query;
			this.hasMoreItemsToLoad = true;
		}

		return this.request();
	}

	resetSearchState()
	{
		this.#query = '';
		this.hasMoreItemsToLoad = true;
		void this.store.dispatch('sidebar/links/clearSearch', {});
	}

	async request(): Promise<number[]>
	{
		const queryParams = this.getQueryParams();
		let responseData: RestResponse = {};
		try
		{
			const response = await this.restClient.callMethod(RestMethod.imChatUrlGet, queryParams);
			responseData = response.data();
		}
		catch (error)
		{
			console.error('SidebarSearch: Im.imChatUrlGet: page request error', error);
		}

		return this.#processSearchResponse(responseData);
	}

	#processSearchResponse(response: RestResponse): Promise<number[]>
	{
		return this.#updateModels(response).then(() => {
			return response.list.map((message) => message.messageId);
		});
	}

	#updateModels(resultData: RestResponse): Promise
	{
		const { list, users, tariffRestrictions = {} } = resultData;

		const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setLinksPromise = this.store.dispatch('sidebar/links/setSearch', {
			chatId: this.chatId,
			links: list,
			hasNextPage: list.length === REQUEST_ITEMS_LIMIT,
			isHistoryLimitExceeded,
		});

		return Promise.all([setLinksPromise, addUsersPromise]);
	}

	getQueryParams(): UrlGetQueryParams
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			LIMIT: REQUEST_ITEMS_LIMIT,
			SEARCH_URL: this.#query,
		};
		const linksCount = this.getLinksCountFromModel();
		if (Type.isNumber(linksCount) && linksCount > 0)
		{
			queryParams.OFFSET = linksCount;
		}

		return queryParams;
	}

	getLinksCountFromModel(): number
	{
		return this.store.getters['sidebar/links/getSearchResultCollectionSize'](this.chatId);
	}
}
