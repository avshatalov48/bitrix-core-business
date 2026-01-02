import { Type } from 'main.core';

import { RestMethod, SidebarFileGroups } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from './helpers/get-chat-id';
import { getLastElementId } from './helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';

type QueryParams = {
	CHAT_ID: number,
	GROUP: string,
	LIMIT: number,
	LAST_ID?: number,
}

const REQUEST_ITEMS_LIMIT = 50;

export class File
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

	updateModels(resultData, group: string = ''): Promise
	{
		const { list, users, files, tariffRestrictions = {} } = resultData;

		const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
		const historyLimitPromise = this.store.dispatch('sidebar/files/setHistoryLimitExceeded', {
			chatId: this.chatId,
			isHistoryLimitExceeded,
		});

		if (group && !Type.isArrayFilled(list))
		{
			return this.store.dispatch('sidebar/files/setHasNextPage', {
				chatId: this.chatId,
				group,
				hasNextPage: false,
			});
		}

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setFilesPromise = this.store.dispatch('files/set', files);

		const sortedGroups = {};
		list.forEach((file) => {
			const fileGroup = file.group ?? SidebarFileGroups.fileUnsorted;
			if (!sortedGroups[fileGroup])
			{
				sortedGroups[fileGroup] = [];
			}
			sortedGroups[fileGroup].push(file);
		});

		const setSidebarFilesPromises = [];
		Object.entries(sortedGroups).forEach(([groupName, listByGroup]) => {
			setSidebarFilesPromises.push(
				this.store.dispatch('sidebar/files/set', {
					chatId: this.chatId,
					files: listByGroup,
					group: groupName,
				}),
				this.store.dispatch('sidebar/files/setHasNextPage', {
					chatId: this.chatId,
					group: groupName,
					hasNextPage: listByGroup.length === REQUEST_ITEMS_LIMIT,
				}),
				this.store.dispatch('sidebar/files/setLastId', {
					chatId: this.chatId,
					group: groupName,
					lastId: getLastElementId(listByGroup),
				}),
			);
		});

		return Promise.all([
			setFilesPromise, addUsersPromise, historyLimitPromise, ...setSidebarFilesPromises,
		]);
	}

	loadFirstPage(group: string): Promise
	{
		return this.loadFirstPageByGroup(group);
	}

	loadNextPage(group: string): Promise
	{
		return this.loadNextPageByGroup(group);
	}

	loadFirstPageByGroup(group: string): Promise
	{
		const filesCount = this.getFilesCountFromModel(group);
		if (filesCount > REQUEST_ITEMS_LIMIT)
		{
			return Promise.resolve();
		}

		const queryParams = this.getQueryParams(group);

		return this.requestPage(queryParams);
	}

	loadNextPageByGroup(group: string): Promise
	{
		const queryParams = this.getQueryParams(group);

		return this.requestPage(queryParams);
	}

	getQueryParams(group: string): QueryParams
	{
		const queryParams = {
			CHAT_ID: this.chatId,
			GROUP: group,
			LIMIT: REQUEST_ITEMS_LIMIT,
		};

		const lastId = this.store.getters['sidebar/files/getLastId'](this.chatId, group);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	requestPage(queryParams: QueryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatFileGet, queryParams).then((response) => {
			return this.updateModels(response.data(), queryParams.GROUP);
		}).catch((error) => {
			console.error('SidebarInfo: imChatFileGet: page request error', error);
		});
	}

	getFilesCountFromModel(group): number
	{
		return this.store.getters['sidebar/files/getSize'](this.chatId, group);
	}
}
