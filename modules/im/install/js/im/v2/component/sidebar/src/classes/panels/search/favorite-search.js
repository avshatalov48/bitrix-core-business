import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';

import { getChatId } from '../helpers/get-chat-id';
import { getLastElementId } from '../helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

type RestResponse = {
	files: [],
	list: [],
	reactions: [],
	reminders: [],
	users: [],
	usersShort: []
};

type UrlGetQueryParams = {
	CHAT_ID: number,
	LIMIT: number,
	SEARCH_MESSAGE?: string,
	LAST_ID?: number,
};

export class FavoriteSearch
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
		void this.store.dispatch('sidebar/favorites/clearSearch', {});
	}

	async request(): Promise<number[]>
	{
		const queryParams = this.getQueryParams();
		let responseData: RestResponse = {};
		try
		{
			const response = await this.restClient.callMethod(RestMethod.imChatFavoriteGet, queryParams);
			responseData = response.data();
		}
		catch (error)
		{
			console.error('SidebarSearch: Im.imChatFavoriteGet: page request error', error);
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
			SEARCH_MESSAGE: this.#query,
		};

		const lastId = this.store.getters['sidebar/favorites/getSearchResultCollectionLastId'](this.chatId);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	updateModels(resultData: {list: [], users: [], files: []}): Promise
	{
		const { list = [], users = [], files = [], tariffRestrictions = {} } = resultData;
		const addUsersPromise = this.userManager.setUsersToModel(users);

		const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);

		const rawMessages = list.map((favorite) => favorite.message);
		const hasNextPage = list.length === REQUEST_ITEMS_LIMIT;
		const lastId = getLastElementId(list);

		const setFilesPromise = this.store.dispatch('files/set', files);
		const storeMessagesPromise = this.store.dispatch('messages/store', rawMessages);
		const setFavoritesPromise = this.store.dispatch('sidebar/favorites/setSearch', {
			chatId: this.chatId,
			favorites: list,
			hasNextPage,
			lastId,
			isHistoryLimitExceeded,
		});

		return Promise.all([
			setFilesPromise, storeMessagesPromise, setFavoritesPromise, addUsersPromise,
		]);
	}
}
