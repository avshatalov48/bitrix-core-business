import {RestMethod} from 'im.v2.const';
import {Base} from './base';

const REQUEST_ITEMS_LIMIT = 50;

export class Meeting extends Base
{
	getInitialRequest()
	{
		return {
			[RestMethod.imChatCalendarGet]: [RestMethod.imChatCalendarGet, {
				chat_id: this.chatId,
				limit: REQUEST_ITEMS_LIMIT
			}]
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

			return this.handleResponse(response[RestMethod.imChatCalendarGet]);
		};
	}

	extractLoadTaskError(response): ?string
	{
		const calendarGetResponse = response[RestMethod.imChatCalendarGet];
		if (calendarGetResponse?.error())
		{
			return `Sidebar service error: ${RestMethod.imChatCalendarGet}: ${calendarGetResponse?.error()}`;
		}

		return null;
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
		return this.restClient.callMethod(RestMethod.imChatCalendarGet, queryParams).then(response => {
			return this.handleResponse(response);
		}).catch(error => {
			console.error('SidebarInfo: Im.imChatCalendarGet: page request error', error);
		});
	}

	updateModels(resultData): Promise
	{
		const {list, users} = resultData;

		const addUsersPromise = this.userManager.setUsersToModel(users);
		const setMeetingsPromise = this.store.dispatch('sidebar/meetings/set', {
			chatId: this.chatId,
			meetings: list
		});

		return Promise.all([setMeetingsPromise, addUsersPromise]);
	}

	handleResponse(response): Promise
	{
		const meetingsResult = response.data();
		if (meetingsResult.list.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		this.lastId = this.getLastElementId(meetingsResult.list);

		return this.updateModels(meetingsResult);
	}

	getMeetingsCountFromState(): number
	{
		return this.store.getters['sidebar/meetings/getSize'](this.chatId);
	}
}