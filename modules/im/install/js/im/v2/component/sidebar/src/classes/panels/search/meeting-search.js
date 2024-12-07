import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from '../helpers/get-chat-id';
import { getLastElementId } from '../helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

type RestResponse = {
	list: [],
	users: [],
};

type UrlGetQueryParams = {
	CHAT_ID: number,
	LIMIT: number,
	LAST_ID?: number,
	SEARCH_TITLE?: string,
}

export class MeetingSearch
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
		void this.store.dispatch('sidebar/meetings/clearSearch', {});
	}

	async request(): Promise<number[]>
	{
		const queryParams = this.getQueryParams();
		let responseData: RestResponse = {};
		try
		{
			const response = await this.restClient.callMethod(RestMethod.imChatCalendarGet, queryParams);
			responseData = response.data();
		}
		catch (error)
		{
			console.error('SidebarSearch: Im.imChatCalendarGet: page request error', error);
		}

		return this.#processSearchResponse(responseData);
	}

	#processSearchResponse(response: RestResponse): Promise<number[]>
	{
		return this.updateModels(response).then(() => {
			return response.list.map((message) => message.messageId);
		});
	}

	getQueryParams(): UrlGetQueryParams
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			LIMIT: REQUEST_ITEMS_LIMIT,
			SEARCH_TITLE: this.#query,
		};

		const lastId = this.store.getters['sidebar/meetings/getSearchResultCollectionLastId'](this.chatId);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	updateModels(resultData: RestResponse): Promise
	{
		const { list, users, tariffRestrictions = {} } = resultData;

		const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
		const hasNextPage = list.length === REQUEST_ITEMS_LIMIT;
		const lastId = getLastElementId(list);

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setMeetingsPromise = this.store.dispatch('sidebar/meetings/setSearch', {
			chatId: this.chatId,
			meetings: list,
			hasNextPage,
			lastId,
			isHistoryLimitExceeded,
		});

		return Promise.all([setMeetingsPromise, addUsersPromise]);
	}
}
