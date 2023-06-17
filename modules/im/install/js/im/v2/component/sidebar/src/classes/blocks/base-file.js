import {RestMethod} from 'im.v2.const';
import {Base} from './base';

const REQUEST_ITEMS_LIMIT = 50;

export class BaseFile extends Base
{
	hasMoreItemsToLoad: boolean = true;
	lastId: number = 0;

	getInitialRequest()
	{
		return {
			[RestMethod.imChatFileCollectionGet]: [RestMethod.imChatFileCollectionGet, {chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT}]
		};
	}

	getResponseHandler()
	{
		return (response) => {
			if (!response)
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			const requestError = this.extractLoadFileError(response);
			if (requestError)
			{
				return Promise.reject(new Error(requestError));
			}

			const fileResult = response[RestMethod.imChatFileCollectionGet].data();

			return this.updateModels(fileResult);
		};
	}

	extractLoadFileError(response): ?string
	{
		const fileGetResponse = response[RestMethod.imChatFileCollectionGet];
		if (fileGetResponse?.error())
		{
			return `Sidebar service error: ${RestMethod.imChatFileCollectionGet}: ${fileGetResponse?.error()}`;
		}

		return null;
	}

	handleResponse(response): Promise
	{
		const fileResult = response.data();
		if (fileResult.list.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		this.lastId = this.getLastElementId(fileResult.list);

		return this.updateModels(fileResult);
	}

	updateModels(resultData): Promise
	{
		const {list, users, files} = resultData;

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setFilesPromise = this.store.dispatch('files/set', files);
		const setSidebarFilesPromise = this.store.dispatch('sidebar/files/set', {
			chatId: this.chatId,
			files: list
		});

		return Promise.all([
			setFilesPromise, setSidebarFilesPromise, addUsersPromise
		]);
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
			'CHAT_ID': this.chatId,
			'SUBTYPE': subType,
			'LIMIT': REQUEST_ITEMS_LIMIT,
		};

		if (this.lastId > 0)
		{
			queryParams['LAST_ID'] = this.lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatFileGet, queryParams).then(response => {
			return this.handleResponse(response);
		}).catch(error => {
			console.error('SidebarInfo: imChatFileGet: page request error', error);
		});
	}

	getFilesCountFromModel(subType): number
	{
		return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	}
}