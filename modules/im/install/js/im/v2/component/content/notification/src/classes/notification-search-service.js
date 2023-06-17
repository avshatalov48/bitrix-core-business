import {Type} from 'main.core';

import {Core} from 'im.v2.application.core';
import {UserManager} from 'im.v2.lib.user';
import {Logger} from 'im.v2.lib.logger';
import {RestMethod} from 'im.v2.const';

const LIMIT_PER_PAGE = 50;

export class NotificationSearchService
{
	searchQuery: string = '';
	searchType: string = '';
	searchDate: Date = null;

	store: Object = null;
	restClient: Object = null;
	userManager: Object = null;
	isLoading: boolean = false;

	lastId: number = 0;
	hasMoreItemsToLoad: boolean = true;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.userManager = new UserManager();
	}

	loadFirstPage({searchQuery, searchType, searchDate}): Promise
	{
		this.isLoading = true;

		this.searchQuery = searchQuery;
		this.searchType = searchType;
		this.searchDate = searchDate;

		return this.requestItems({firstPage: true});
	}

	loadNextPage(): Promise
	{
		if (this.isLoading || !this.hasMoreItemsToLoad)
		{
			return Promise.resolve();
		}
		this.isLoading = true;

		return this.requestItems();
	}

	searchInModel({searchQuery, searchType, searchDate}): Array
	{
		this.searchQuery = searchQuery;
		this.searchType = searchType;
		this.searchDate = searchDate;

		return this.store.getters['notifications/getSortedCollection'].filter(item => {
			let result = false;
			if (this.searchQuery.length >= 3)
			{
				result = item.text.toLowerCase().includes(this.searchQuery.toLowerCase());
				if (!result)
				{
					return result;
				}
			}
			if (this.searchType !== '')
			{
				result = item.settingName === this.searchType; // todo: ???
				if (!result)
				{
					return result;
				}
			}
			if (this.searchDate !== '')
			{
				const date = BX.parseDate(this.searchDate);
				if (date instanceof Date)
				{
					// compare dates excluding time.
					const itemDateForCompare = (new Date(item.date.getTime())).setHours(0, 0, 0, 0);
					const dateFromInput = date.setHours(0, 0, 0, 0);

					result = itemDateForCompare === dateFromInput;
				}
			}

			return result;
		});
	}

	requestItems({firstPage = false} = {}): Promise
	{
		const queryParams = this.getSearchRequestParams(firstPage);

		return this.restClient.callMethod(RestMethod.imNotifyHistorySearch, queryParams).then(response => {
			const responseData = response.data();
			Logger.warn('im.notify.history.search: first page results', responseData);
			this.hasMoreItemsToLoad = !this.isLastPage(responseData.notifications);
			if (!responseData || responseData.notifications.length === 0)
			{
				Logger.warn('im.notify.get: no notifications', responseData);

				return [];
			}

			this.lastId = this.getLastItemId(responseData.notifications);

			this.userManager.setUsersToModel(responseData.users);
			this.isLoading = false;

			return responseData.notifications;
		}).catch(error => {
			Logger.warn('History request error', error);
		});
	}

	getSearchRequestParams(firstPage: boolean): Object
	{
		const requestParams = {
			'SEARCH_TEXT': this.searchQuery,
			'SEARCH_TYPE': this.searchType,
			'LIMIT': LIMIT_PER_PAGE,
			'CONVERT_TEXT': 'Y'
		};
		if (BX.parseDate(this.searchDate) instanceof Date)
		{
			requestParams['SEARCH_DATE'] = BX.parseDate(this.searchDate).toISOString();
		}
		if (!firstPage)
		{
			requestParams['LAST_ID'] = this.lastId;
		}

		return requestParams;
	}

	getLastItemId(collection: Array<Object>): number
	{
		return collection[collection.length - 1].id;
	}

	isLastPage(notifications: Array): boolean
	{
		if (!Type.isArrayFilled(notifications) || notifications.length < LIMIT_PER_PAGE)
		{
			return true;
		}

		return false;
	}

	destroy()
	{
		Logger.warn('Notification search service destroyed');
	}
}