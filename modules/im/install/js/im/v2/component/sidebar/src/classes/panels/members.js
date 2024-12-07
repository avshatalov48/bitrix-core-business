import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from './helpers/get-chat-id';
import { getLastElementId } from './helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

type QueryParams = {
	DIALOG_ID: string,
	LAST_ID: number,
	LIMIT: number
}

export class MembersService
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
			[RestMethod.imDialogUsersList]: {
				dialog_id: this.dialogId,
				limit: REQUEST_ITEMS_LIMIT,
				LAST_ID: 0
			},
		};
	}

	loadFirstPage(): Promise
	{
		const membersCount = this.getMembersCountFromModel();
		if (membersCount > REQUEST_ITEMS_LIMIT)
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

	getQueryParams(): QueryParams
	{
		return {
			DIALOG_ID: this.dialogId,
			LIMIT: REQUEST_ITEMS_LIMIT,
			LAST_ID: this.store.getters['sidebar/members/getLastId'](this.chatId),
		};
	}

	async requestPage(queryParams: QueryParams): Promise
	{
		let users = [];

		try
		{
			const response = await this.restClient.callMethod(RestMethod.imDialogUsersList, queryParams);
			users = response.data();
		}
		catch (error)
		{
			console.error('SidebarMain: Im.DialogUsersList: page request error', error);
		}

		return this.updateModels(users);
	}

	getResponseHandler(): Function
	{
		return (response) => {
			return this.updateModels(response[RestMethod.imDialogUsersList]);
		};
	}

	updateModels(users: {id: number}[]): Promise
	{
		const userIds = [];
		const addUsersPromise = this.userManager.setUsersToModel(users);
		users.forEach((user) => {
			userIds.push(user.id);
		});

		const setMembersPromise = this.store.dispatch('sidebar/members/set', {
			chatId: this.chatId,
			users: userIds,
			lastId: getLastElementId(users, 'DESC'),
			hasNextPage: users.length === REQUEST_ITEMS_LIMIT,
		});

		return Promise.all([addUsersPromise, setMembersPromise]);
	}

	getMembersCountFromModel(): number
	{
		return this.store.getters['sidebar/members/getSize'](this.chatId);
	}
}
