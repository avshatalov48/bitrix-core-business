import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';
import { RestMethod, SidebarDetailBlock } from 'im.v2.const';

import { getLastElementId } from './helpers/get-last-element-id';

import type { JsonObject } from 'main.core';
import type { RestClient } from 'rest.client';
import type { Store } from 'ui.vue3.vuex';

const REQUEST_ITEMS_LIMIT = 50;

export class FileUnsorted
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
		this.chatId = this.getChatId();
		this.userManager = new UserManager();
	}

	getInitialQuery(): {[$Values<typeof RestMethod>]: JsonObject}
	{
		return {
			[RestMethod.imDiskFolderListGet]: { chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT },
		};
	}

	getResponseHandler(): Function
	{
		return (response) => {
			if (!response[RestMethod.imDiskFolderListGet])
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			return this.updateModels(response[RestMethod.imDiskFolderListGet]);
		};
	}

	loadFirstPage(): Promise
	{
		const filesCount = this.getFilesCountFromModel(SidebarDetailBlock.fileUnsorted);
		if (filesCount > REQUEST_ITEMS_LIMIT)
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

		const lastId = this.store.getters['sidebar/files/getLastId'](this.chatId, SidebarDetailBlock.fileUnsorted);
		if (lastId > 0)
		{
			queryParams.LAST_ID = lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imDiskFolderListGet, queryParams).then((response) => {
			return this.handleResponse(response.data());
		}).catch((error) => {
			console.error('SidebarInfo: Im.imDiskFolderListGet: page request error', error);
		});
	}

	handleResponse(response): Promise
	{
		const diskFolderListGetResult = response;
		if (diskFolderListGetResult.files.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		const lastId = getLastElementId(diskFolderListGetResult.files);
		if (lastId)
		{
			this.lastId = lastId;
		}

		return this.updateModels(diskFolderListGetResult);
	}

	updateModels(resultData): Promise
	{
		const { users, files } = resultData;

		const preparedFiles = files.map((file) => {
			return { ...file, subType: SidebarDetailBlock.fileUnsorted };
		});

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setFilesPromise = this.store.dispatch('files/set', preparedFiles);
		const setSidebarFilesPromise = this.store.dispatch('sidebar/files/set', {
			chatId: this.chatId,
			files: preparedFiles,
			subType: SidebarDetailBlock.fileUnsorted,
		});

		const hasNextPagePromise = this.store.dispatch('sidebar/files/setHasNextPage', {
			chatId: this.chatId,
			subType: SidebarDetailBlock.fileUnsorted,
			hasNextPage: preparedFiles.length === REQUEST_ITEMS_LIMIT,
		});

		const setLastIdPromise = this.store.dispatch('sidebar/files/setLastId', {
			chatId: this.chatId,
			subType: SidebarDetailBlock.fileUnsorted,
			lastId: getLastElementId(preparedFiles),
		});

		return Promise.all([
			setFilesPromise, setSidebarFilesPromise, addUsersPromise, hasNextPagePromise, setLastIdPromise,
		]);
	}

	getFilesCountFromModel(subType): number
	{
		return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	}

	getChatId(): number
	{
		const dialog = this.store.getters['chats/get'](this.dialogId, true);

		return dialog.chatId;
	}
}
