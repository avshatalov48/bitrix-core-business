import {RestMethod} from 'im.v2.const';
import {Base} from './base';

const REQUEST_ITEMS_LIMIT = 50;

export class Task extends Base
{
	hasMoreItemsToLoad: boolean = true;
	lastId: number = 0;

	getInitialRequest()
	{
		return {
			[RestMethod.imChatTaskGet]: [RestMethod.imChatTaskGet, {chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT}]
		};
	}

	getResponseHandler()
	{
		return (response) => {
			if (!response)
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			const requestError = this.extractLoadTaskError(response);
			if (requestError)
			{
				return Promise.reject(new Error(requestError));
			}

			return this.handleResponse(response[RestMethod.imChatTaskGet]);
		};
	}

	extractLoadTaskError(response): ?string
	{
		const taskGetResponse = response[RestMethod.imChatTaskGet];
		if (taskGetResponse?.error())
		{
			return `Sidebar service error: ${RestMethod.imChatTaskGet}: ${taskGetResponse?.error()}`;
		}

		return null;
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
			'CHAT_ID': this.chatId,
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
		return this.restClient.callMethod(RestMethod.imChatTaskGet, queryParams).then(response => {
			return this.handleResponse(response);
		}).catch(error => {
			console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
		});
	}

	handleResponse(response): Promise
	{
		const tasksResult = response.data();
		if (tasksResult.list.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		this.firstPageReceived = true;
		this.lastId = this.getLastElementId(tasksResult.list);

		return this.updateModels(tasksResult);
	}

	updateModels(resultData): Promise
	{
		const {list, users} = resultData;

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setTasksPromise = this.store.dispatch('sidebar/tasks/set', {
			chatId: this.chatId,
			tasks: list
		});

		return Promise.all([setTasksPromise, addUsersPromise]);
	}

	getTasksCountFromModel(): number
	{
		return this.store.getters['sidebar/tasks/getSize'](this.chatId);
	}
}