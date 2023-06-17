import {RestMethod} from 'im.v2.const';
import {Base} from './base';
import {Type} from 'main.core';

const REQUEST_ITEMS_LIMIT = 50;
export class Link extends Base
{
	hasMoreItemsToLoad: boolean = true;

	getInitialRequest()
	{
		return {
			[RestMethod.imChatUrlCounterGet]: [RestMethod.imChatUrlCounterGet, {chat_id: this.chatId}],
			[RestMethod.imChatUrlGet]: [RestMethod.imChatUrlGet, {chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT}],
		};
	}

	getResponseHandler()
	{
		return (response) => {
			if (!response)
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			const requestError = this.extractLoadCountersError(response);
			if (requestError)
			{
				return Promise.reject(new Error(requestError));
			}

			const linkCounterGetResponse = response[RestMethod.imChatUrlCounterGet].data();
			const setCounterResult = this.store.dispatch('sidebar/links/setCounter', {
				chatId: this.chatId,
				counter: linkCounterGetResponse.counter
			});

			const setLinksResult = this.handleResponse(response[RestMethod.imChatUrlGet]);

			return Promise.all([setCounterResult, setLinksResult]);
		};
	}

	extractLoadCountersError(response): string
	{
		const linkCounterGetResult = response[RestMethod.imChatUrlCounterGet];
		if (linkCounterGetResult?.error())
		{
			return `SidebarInfo service error: ${RestMethod.imChatUrlCounterGet}: ${linkCounterGetResult?.error()}`;
		}

		const linkGetResult = response[RestMethod.imChatUrlGet];
		if (linkGetResult?.error())
		{
			return `SidebarInfo service error: ${RestMethod.imChatUrlGet}: ${linkGetResult?.error()}`;
		}

		return null;
	}

	loadFirstPage(): Promise
	{
		const linksCount = this.getLinksCountFromModel();
		if (linksCount > REQUEST_ITEMS_LIMIT)
		{
			return Promise.resolve();
		}

		const queryParams = this.getQueryParams(linksCount);

		return this.requestPage(queryParams);
	}

	loadNextPage(): Promise
	{
		const linksCount = this.getLinksCountFromModel();
		const queryParams = this.getQueryParams(linksCount);

		return this.requestPage(queryParams);
	}

	getQueryParams(offset: number = 0): Object
	{
		const queryParams = {
			'CHAT_ID': this.chatId,
			'LIMIT': REQUEST_ITEMS_LIMIT,
		};

		if (Type.isNumber(offset) && offset > 0)
		{
			queryParams.OFFSET = offset;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatUrlGet, queryParams).then(response => {
			return this.handleResponse(response);
		}).catch(error => {
			console.error('SidebarInfo: Im.chatUrlList: page request error', error);
		});
	}

	handleResponse(response): Promise
	{
		const resultData = response.data();
		if (resultData.list.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		return this.updateModels(resultData);
	}

	updateModels(resultData): Promise
	{
		const {list, users} = resultData;

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setLinksPromise = this.store.dispatch('sidebar/links/set', {
			chatId: this.chatId,
			links: list
		});

		return Promise.all([setLinksPromise, addUsersPromise]);
	}

	getLinksCountFromModel(): number
	{
		return this.store.getters['sidebar/links/getSize'](this.chatId);
	}
}
