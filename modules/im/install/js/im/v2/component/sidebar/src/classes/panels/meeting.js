import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from './helpers/get-chat-id';
import { getLastElementId } from './helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

export class Meeting
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
			[RestMethod.imChatCalendarGet]: {
				chat_id: this.chatId,
				limit: REQUEST_ITEMS_LIMIT,
			},
		};
	}

	getResponseHandler(): Function
	{
		return (response) => {
			if (!response[RestMethod.imChatCalendarGet])
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			return this.updateModels(response[RestMethod.imChatCalendarGet]);
		};
	}

	loadFirstPage(): Promise
	{
		const meetingsCount = this.getMeetingsCountFromState();
		if (meetingsCount > REQUEST_ITEMS_LIMIT)
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

		const lastId = this.store.getters['sidebar/meetings/getLastId'](this.chatId);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatCalendarGet, queryParams).then((response) => {
			return this.updateModels(response.data());
		}).catch((error) => {
			console.error('SidebarInfo: Im.imChatCalendarGet: page request error', error);
		});
	}

	updateModels(resultData): Promise
	{
		const { list, users } = resultData;

		const hasNextPage = list.length === REQUEST_ITEMS_LIMIT;
		const lastId = getLastElementId(list);

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setMeetingsPromise = this.store.dispatch('sidebar/meetings/set', {
			chatId: this.chatId,
			meetings: list,
			hasNextPage,
			lastId,
		});

		return Promise.all([setMeetingsPromise, addUsersPromise]);
	}

	getMeetingsCountFromState(): number
	{
		return this.store.getters['sidebar/meetings/getSize'](this.chatId);
	}
}
