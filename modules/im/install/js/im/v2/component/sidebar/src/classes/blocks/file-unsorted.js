import {RestMethod, SidebarDetailBlock} from 'im.v2.const';
import {Base} from './base';

const REQUEST_ITEMS_LIMIT = 50; // temporary value. Should be 50

export class FileUnsorted extends Base
{
	hasMoreItemsToLoad: boolean = true;
	lastId: number = 0;

	getInitialRequest()
	{
		return {
			[RestMethod.imDiskFolderListGet]: [RestMethod.imDiskFolderListGet, {chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT}],
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

			const fileResult = response[RestMethod.imDiskFolderListGet].data();

			return this.updateModels(fileResult);
		};
	}

	extractLoadFileError(response): ?string
	{
		const diskFolderListGetResult = response[RestMethod.imDiskFolderListGet];
		if (diskFolderListGetResult?.error())
		{
			return `SidebarInfo service error: ${RestMethod.imDiskFolderListGet}: ${diskFolderListGetResult?.error()}`;
		}

		return null;
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
			'CHAT_ID': this.chatId,
			'LIMIT': REQUEST_ITEMS_LIMIT,
		};

		if (this.lastId > 0)
		{
			queryParams.LAST_ID = this.lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imDiskFolderListGet, queryParams).then(response => {
			return this.handleResponse(response);
		}).catch(error => {
			console.error('SidebarInfo: Im.imDiskFolderListGet: page request error', error);
		});
	}

	handleResponse(response): Promise
	{
		const diskFolderListGetResult = response.data();
		if (diskFolderListGetResult.files.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		const lastId = this.getLastElementId(diskFolderListGetResult.files);
		if (lastId)
		{
			this.lastId = lastId;
		}

		return this.updateModels(diskFolderListGetResult);
	}

	updateModels(resultData): Promise
	{
		const {users, files} = resultData;

		const preparedFiles = files.map(file => {
			return {...file, subType: SidebarDetailBlock.fileUnsorted};
		});

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setFilesPromise = this.store.dispatch('files/set', preparedFiles);
		const setSidebarFilesPromise = this.store.dispatch('sidebar/files/set', {
			chatId: this.chatId,
			files: preparedFiles
		});

		return Promise.all([
			setFilesPromise, setSidebarFilesPromise, addUsersPromise
		]);
	}

	getFilesCountFromModel(subType): number
	{
		return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	}
}