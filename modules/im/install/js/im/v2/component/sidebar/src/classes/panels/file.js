import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from './helpers/get-chat-id';
import { getLastElementId } from './helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';

const REQUEST_ITEMS_LIMIT = 50;

export class File
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
			[RestMethod.imChatFileCollectionGet]: { chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT },
		};
	}

	getResponseHandler(): Function
	{
		return (response) => {
			if (!response[RestMethod.imChatFileCollectionGet])
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			return this.updateModels(response[RestMethod.imChatFileCollectionGet]);
		};
	}

	updateModels(resultData): Promise
	{
		const { list, users, files } = resultData;

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
				this.store.dispatch('sidebar/files/set', {
					chatId: this.chatId,
					files: listByType,
					subType,
				}),
				this.store.dispatch('sidebar/files/setHasNextPage', {
					chatId: this.chatId,
					subType,
					hasNextPage: listByType.length === REQUEST_ITEMS_LIMIT,
				}),
				this.store.dispatch('sidebar/files/setLastId', {
					chatId: this.chatId,
					subType,
					lastId: getLastElementId(listByType),
				}),
			);
		});

		return Promise.all([
			setFilesPromise, addUsersPromise, ...setSidebarFilesPromises,
		]);
	}

	loadFirstPage(subType): Promise
	{
		return this.loadFirstPageBySubType(subType);
	}

	loadNextPage(subType): Promise
	{
		return this.loadNextPageBySubType(subType);
	}

	loadFirstPageBySubType(subType: string): Promise
	{
		const filesCount = this.getFilesCountFromModel(subType);
		if (filesCount > REQUEST_ITEMS_LIMIT)
		{
			return Promise.resolve();
		}

		const queryParams = this.getQueryParams(subType);

		return this.requestPage(queryParams);
	}

	loadNextPageBySubType(subType: string): Promise
	{
		const queryParams = this.getQueryParams(subType);

		return this.requestPage(queryParams);
	}

	getQueryParams(subType: string): Object
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			SUBTYPE: subType,
			LIMIT: REQUEST_ITEMS_LIMIT,
		};

		const lastId = this.store.getters['sidebar/files/getLastId'](this.chatId, subType);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatFileGet, queryParams).then((response) => {
			return this.updateModels(response.data());
		}).catch((error) => {
			console.error('SidebarInfo: imChatFileGet: page request error', error);
		});
	}

	getFilesCountFromModel(subType): number
	{
		return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	}
}
