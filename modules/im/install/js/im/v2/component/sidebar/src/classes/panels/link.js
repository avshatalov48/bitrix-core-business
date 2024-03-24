import { Type } from 'main.core';

import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from './helpers/get-chat-id';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

type UrlGetQueryParams = {
	CHAT_ID: number,
	LIMIT: number,
	OFFSET?: number
}

export class Link
{
	store: Store;
	dialogId: string;
	chatId: number;
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

	getInitialQuery(): {[string]: JsonObject}
	{
		return {
			[RestMethod.imChatUrlCounterGet]: { chat_id: this.chatId },
			[RestMethod.imChatUrlGet]: { chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT },
		};
	}

	getResponseHandler(): Function
	{
		return (response) => {
			if (!response[RestMethod.imChatUrlCounterGet] || !response[RestMethod.imChatUrlGet])
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			const urlGetResult = this.handleUrlGetResponse(response[RestMethod.imChatUrlGet]);
			const counterGetResult = this.handleCounterGetResponse(response[RestMethod.imChatUrlCounterGet]);

			return Promise.all([urlGetResult, counterGetResult]);
		};
	}

	loadNextPage(): Promise
	{
		const linksCount = this.getLinksCountFromModel();
		if (linksCount === 0)
		{
			return Promise.resolve();
		}

		const queryParams = this.getQueryParams(linksCount);

		return this.requestPage(queryParams);
	}

	getQueryParams(offset: number = 0): UrlGetQueryParams
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			LIMIT: REQUEST_ITEMS_LIMIT,
		};

		if (Type.isNumber(offset) && offset > 0)
		{
			queryParams.OFFSET = offset;
		}

		return queryParams;
	}

	requestPage(queryParams: UrlGetQueryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatUrlGet, queryParams).then((response) => {
			return this.handleUrlGetResponse(response.data());
		}).catch((error) => {
			console.error('SidebarInfo: Im.chatUrlList: page request error', error);
		});
	}

	handleUrlGetResponse(response: {list: [], users: []}): Promise
	{
		const { list, users } = response;

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setLinksPromise = this.store.dispatch('sidebar/links/set', {
			chatId: this.chatId,
			links: list,
			hasNextPage: list.length === REQUEST_ITEMS_LIMIT,
		});

		return Promise.all([setLinksPromise, addUsersPromise]);
	}

	handleCounterGetResponse(response: {counter: number}): Promise
	{
		const counter = response.counter;

		return this.store.dispatch('sidebar/links/setCounter', {
			chatId: this.chatId,
			counter,
		});
	}

	getLinksCountFromModel(): number
	{
		return this.store.getters['sidebar/links/getSize'](this.chatId);
	}
}
