import {Runtime, Type} from 'main.core';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {NotificationTypesCodes, RestMethod, RestMethodHandler} from 'im.v2.const';
import {UserManager} from 'im.v2.lib.user';

type NotificationItemRest = {
	id: number,
	chat_id: number,
	author_id: number,
	date: string,
	notify_type: number,
	notify_module: string,
	notify_event: string,
	notify_tag: string,
	notify_sub_tag: string,
	notify_title?: string,
	notify_read: string,
	setting_name: string,
	text: string,
	notify_buttons: string,
	params?: Object
};

type NotificationGetRestResult = {
	chat_id: number,
	notifications: NotificationItemRest[],
	total_count: number,
	total_unread_count: number,
	users: [],
};

export class NotificationService
{
	store: Object = null;
	restClient: Object = null;
	limitPerPage: Number = 50;
	isLoading: boolean = false;

	lastId: number = 0;
	lastType: number = 0;
	hasMoreItemsToLoad: boolean = true;

	notificationsToDelete: Set<number> = new Set();

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.deleteWithDebounce = Runtime.debounce(this.deleteRequest, 500, this);
		this.userManager = new UserManager();
	}

	loadFirstPage(): Promise
	{
		this.isLoading = true;

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

	delete(notificationId: number)
	{
		this.notificationsToDelete.add(notificationId);
		this.store.dispatch('notifications/delete', {id: notificationId});

		this.deleteWithDebounce();
	}

	sendConfirmAction(notificationId: number, value: string)
	{
		const requestParams = {
			'NOTIFY_ID': notificationId,
			'NOTIFY_VALUE': value,
		};

		this.store.dispatch('notifications/delete', {id: notificationId});

		this.restClient.callMethod('im.notify.confirm', requestParams).then(response => {
			Logger.warn(`NotificationService: sendConfirmAction: success`, response);
		}).catch(error => {
			console.error(error);
			//revert?
		});
	}

	sendQuickAnswer(params)
	{
		const {id, text, callbackSuccess = () => {}, callbackError = () => {}} = params;

		this.restClient.callMethod(RestMethod.imNotifyAnswer, {
			notify_id: id,
			answer_text: text
		}).then(response => {
			callbackSuccess(response);
		}).catch(error => {
			console.error(error);
			callbackError();
		});
	}

	deleteRequest()
	{
		const idsToDelete = [...this.notificationsToDelete];

		this.restClient.callMethod('im.notify.delete', {id: idsToDelete}).then(response => {
			Logger.warn(`NotificationService: deleteRequest: success for ids: ${idsToDelete}`, response);
		}).catch(error => {
			console.error(error);
			//revert?
		});

		this.notificationsToDelete.clear();
	}

	requestItems({firstPage = false} = {}): Promise
	{
		const imNotifyGetQueryParams = {
			'LIMIT': this.limitPerPage,
			'CONVERT_TEXT': 'Y'
		};
		const batchQueryParams = {
			[RestMethodHandler.imNotifyGet]: [RestMethod.imNotifyGet, imNotifyGetQueryParams]
		};

		if (!firstPage)
		{
			imNotifyGetQueryParams.LAST_ID = this.lastId;
			imNotifyGetQueryParams.LAST_TYPE = this.lastType;
		}
		else
		{
			batchQueryParams[RestMethodHandler.imNotifySchemaGet] = [RestMethod.imNotifySchemaGet, {}];
		}

		return new Promise(resolve => {
			this.restClient.callBatch(batchQueryParams, (response) => {
				Logger.warn('im.notify.get: result', response);
				resolve(this.handleResponse(response));
			});
		});
	}

	handleResponse(response: Object): Promise
	{
		const imNotifyGetResponse = response[RestMethodHandler.imNotifyGet].data();
		this.hasMoreItemsToLoad = !this.isLastPage(imNotifyGetResponse.notifications);
		if (imNotifyGetResponse.notifications.length === 0)
		{
			Logger.warn('im.notify.get: no notifications', imNotifyGetResponse);

			return Promise.resolve();
		}

		this.lastId = this.getLastItemId(imNotifyGetResponse.notifications);
		this.lastType = this.getLastItemType(imNotifyGetResponse.notifications);

		return this.updateModels(imNotifyGetResponse).then(() => {
			this.isLoading = false;

			if (response[RestMethodHandler.imNotifySchemaGet])
			{
				return response[RestMethodHandler.imNotifySchemaGet].data();
			}

			return {};
		});
	}

	updateModels(imNotifyGetResponse: NotificationGetRestResult): Promise
	{
		this.userManager.setUsersToModel(imNotifyGetResponse.users);

		return this.store.dispatch('notifications/initialSet', imNotifyGetResponse);
	}

	getLastItemId(collection: NotificationItemRest[]): number
	{
		return collection[collection.length - 1].id;
	}

	getLastItemType(collection: NotificationItemRest[]): number
	{
		return this.getItemType(collection[collection.length - 1]);
	}

	getItemType(item: NotificationItemRest): number
	{
		return item.notify_type === NotificationTypesCodes.confirm
			? NotificationTypesCodes.confirm
			: NotificationTypesCodes.simple
			;
	}

	isLastPage(notifications: Array): boolean
	{
		if (!Type.isArrayFilled(notifications) || notifications.length < this.limitPerPage)
		{
			return true;
		}

		return false;
	}

	destroy()
	{
		Logger.warn('Notification service destroyed');
	}
}