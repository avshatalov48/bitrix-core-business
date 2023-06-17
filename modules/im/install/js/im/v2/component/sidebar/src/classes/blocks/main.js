import {RestMethod} from 'im.v2.const';
import {Base} from './base';

const REQUEST_ITEMS_LIMIT = 50;

export class Main extends Base
{
	hasMoreItemsToLoad: boolean = true;
	lastId: number = 0;

	getInitialRequest()
	{
		return {
			[RestMethod.imDialogUsersList]: [RestMethod.imDialogUsersList, {
				dialog_id: this.dialogId,
				limit: REQUEST_ITEMS_LIMIT
			}]
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

	getQueryParams(): Object
	{
		return {
			'DIALOG_ID': this.dialogId,
			'LIMIT': REQUEST_ITEMS_LIMIT,
			'LAST_ID': this.lastId,
		};
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imDialogUsersList, queryParams).then(response => {
			return this.handleResponse(response);
		}).catch(error => {
			console.error('SidebarMain: Im.DialogUsersList: page request error', error);
		});
	}

	getResponseHandler(): Function
	{
		return (response) => {
			return this.handleResponse(response[RestMethod.imDialogUsersList]);
		};
	}

	updateModels(users: Array): Promise
	{
		const userIds = [];
		const addUsersPromise = this.userManager.setUsersToModel(users);
		users.forEach(user => {
			userIds.push(user.id);
		});

		const setMembersPromise = this.store.dispatch('sidebar/members/set', {
			chatId: this.chatId,
			users: userIds
		});

		return Promise.all([addUsersPromise, setMembersPromise]);
	}

	getMembersCountFromModel(): number
	{
		return this.store.getters['sidebar/members/getSize'](this.chatId);
	}

	handleResponse(response): Promise
	{
		const users = response.data();
		if (users.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		const lastId = this.getLastElementId(users);
		if (lastId)
		{
			this.lastId = lastId;
		}

		return this.updateModels(users);
	}
}
