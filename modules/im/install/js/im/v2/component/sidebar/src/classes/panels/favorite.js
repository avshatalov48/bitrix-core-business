import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';

import { getChatId } from './helpers/get-chat-id';
import { getLastElementId } from './helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

export class Favorite
{
	store: Store;
	dialogId: string;
	userManager: UserManager;
	restClient: RestClient;

	constructor({ dialogId }: {dialogId: string})
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.dialogId = dialogId;
		this.chatId = getChatId(dialogId);
		this.userManager = new UserManager();
	}

	getInitialQuery(): {[$Values<typeof RestMethod>]: JsonObject}
	{
		return {
			[RestMethod.imChatFavoriteCounterGet]: { chat_id: this.chatId },
			[RestMethod.imChatFavoriteGet]: { chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT },
		};
	}

	getResponseHandler(): Function
	{
		return (response) => {
			if (!response[RestMethod.imChatFavoriteCounterGet])
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			const favoriteCounterGetResponse = response[RestMethod.imChatFavoriteCounterGet];
			const setCounterResult = this.store.dispatch('sidebar/favorites/setCounter', {
				chatId: this.chatId,
				counter: favoriteCounterGetResponse.counter,
			});

			const setFavoriteResult = this.handleResponse(response[RestMethod.imChatFavoriteGet]);

			return Promise.all([setCounterResult, setFavoriteResult]);
		};
	}

	loadNextPage(): Promise
	{
		const queryParams = this.getQueryParams();

		return this.requestPage(queryParams);
	}

	getQueryParams(): Object
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			LIMIT: REQUEST_ITEMS_LIMIT,
		};

		const lastId = this.store.getters['sidebar/favorites/getLastId'](this.chatId);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatFavoriteGet, queryParams).then((response) => {
			return this.handleResponse(response.data());
		}).catch((error) => {
			console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
		});
	}

	handleResponse(response): Promise
	{
		return this.updateModels(response);
	}

	updateModels(resultData: {list: [], users: [], files: []}): Promise
	{
		const { list = [], users = [], files = [] } = resultData;
		const addUsersPromise = this.userManager.setUsersToModel(users);

		const rawMessages = list.map((favorite) => favorite.message);
		const hasNextPage = list.length === REQUEST_ITEMS_LIMIT;
		const lastId = getLastElementId(list);

		const setFilesPromise = this.store.dispatch('files/set', files);
		const storeMessagesPromise = this.store.dispatch('messages/store', rawMessages);
		const setFavoritesPromise = this.store.dispatch('sidebar/favorites/set', {
			chatId: this.chatId,
			favorites: list,
			hasNextPage,
			lastId,
		});

		return Promise.all([
			setFilesPromise, storeMessagesPromise, setFavoritesPromise, addUsersPromise,
		]);
	}
}
