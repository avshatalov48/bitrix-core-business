import { Runtime, Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { NotificationTypesCodes, RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';

import type { NotificationGetRestResult, NotificationItemRest } from './types/notification';

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

		return this.requestItems({ firstPage: true });
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
		this.store.dispatch('notifications/delete', { id: notificationId });
		this.store.dispatch('notifications/deleteFromSearch', { id: notificationId });

		this.deleteWithDebounce();
	}

	sendConfirmAction(notificationId: number, value: string)
	{
		const requestParams = {
			NOTIFY_ID: notificationId,
			NOTIFY_VALUE: value,
		};

		this.store.dispatch('notifications/delete', { id: notificationId });

		this.restClient.callMethod('im.notify.confirm', requestParams)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error(error);
			});
	}

	async sendQuickAnswer(params)
	{
		const { id, text, callbackSuccess = () => {}, callbackError = () => {} } = params;

		try
		{
			const response = await this.restClient.callMethod(RestMethod.imNotifyAnswer, {
				notify_id: id,
				answer_text: text,
			});
			callbackSuccess(response);
		}
		catch (error)
		{
			// eslint-disable-next-line no-console
			console.error(error);
			callbackError();
		}
	}

	deleteRequest()
	{
		const idsToDelete = [...this.notificationsToDelete];

		this.restClient.callMethod('im.notify.delete', { id: idsToDelete })
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error(error);
			});

		this.notificationsToDelete.clear();
	}

	requestItems({ firstPage = false } = {}): Promise
	{
		const imNotifyGetQueryParams = {
			LIMIT: this.limitPerPage,
			CONVERT_TEXT: 'Y',
		};
		const batchQueryParams = {
			[RestMethod.imNotifyGet]: [RestMethod.imNotifyGet, imNotifyGetQueryParams],
		};

		if (firstPage)
		{
			batchQueryParams[RestMethod.imNotifySchemaGet] = [RestMethod.imNotifySchemaGet, {}];
		}
		else
		{
			imNotifyGetQueryParams.LAST_ID = this.lastId;
			imNotifyGetQueryParams.LAST_TYPE = this.lastType;
		}

		return new Promise((resolve) => {
			this.restClient.callBatch(batchQueryParams, (response) => {
				Logger.warn('im.notify.get: result', response);
				resolve(this.handleResponse(response));
			});
		});
	}

	handleResponse(response: Object): Promise
	{
		const imNotifyGetResponse = response[RestMethod.imNotifyGet].data();
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

			if (response[RestMethod.imNotifySchemaGet])
			{
				return response[RestMethod.imNotifySchemaGet].data();
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
		if (!Type.isArrayFilled(notifications))
		{
			return true;
		}

		return notifications.length < this.limitPerPage;
	}

	destroy()
	{
		Logger.warn('Notification service destroyed');
	}
}
