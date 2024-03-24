import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from './helpers/get-chat-id';
import { getLastElementId } from './helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

export class Task
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

	getInitialQuery(): {[$Values<typeof RestMethod>]: JsonObject}
	{
		return {
			[RestMethod.imChatTaskGet]: { chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT },
		};
	}

	getResponseHandler(): Function
	{
		return (response) => {
			if (!response[RestMethod.imChatTaskGet])
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			return this.updateModels(response[RestMethod.imChatTaskGet]);
		};
	}

	loadFirstPage(): Promise
	{
		const tasksCount = this.getTasksCountFromModel();
		if (tasksCount > REQUEST_ITEMS_LIMIT)
		{
			return Promise.resolve();
		}

		const queryParams = this.getQueryParams();

		return this.requestPage(queryParams);
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

		const lastId = this.store.getters['sidebar/tasks/getLastId'](this.chatId);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatTaskGet, queryParams).then((response) => {
			return this.updateModels(response.data());
		}).catch((error) => {
			console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
		});
	}

	updateModels(resultData): Promise
	{
		const { list, users } = resultData;

		const hasNextPage = list.length === REQUEST_ITEMS_LIMIT;
		const lastId = getLastElementId(list);

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setTasksPromise = this.store.dispatch('sidebar/tasks/set', {
			chatId: this.chatId,
			tasks: list,
			hasNextPage,
			lastId,
		});

		return Promise.all([setTasksPromise, addUsersPromise]);
	}

	getTasksCountFromModel(): number
	{
		return this.store.getters['sidebar/tasks/getSize'](this.chatId);
	}
}
