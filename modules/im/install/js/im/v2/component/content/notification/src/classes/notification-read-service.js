import {Runtime, Type} from 'main.core';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

export class NotificationReadService
{
	store: Object;
	restClient: Object;
	itemsToRead = new Set();
	readRequestWithDebounce: Function;
	readOnClientWithDebounce: Function;
	changeReadStatusBlockTimeout = {};

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.readOnClientWithDebounce = Runtime.debounce(this.readOnClient, 50, this);
		this.readRequestWithDebounce = Runtime.debounce(this.readRequest, 500, this);
	}

	addToReadQueue(notificationIds: number[])
	{
		if (!Type.isArrayFilled(notificationIds))
		{
			return;
		}

		notificationIds.forEach((id: number) => {
			if (!Type.isNumber(id))
			{
				return;
			}

			const notification = this.store.getters['notifications/getById'](id);
			if (notification.read)
			{
				return;
			}

			this.itemsToRead.add(id);
		});
	}

	read()
	{
		this.readOnClientWithDebounce();
		this.readRequestWithDebounce();
	}

	readRequest()
	{
		if (this.itemsToRead.size === 0)
		{
			return;
		}

		const idToReadFrom = Math.min(...this.itemsToRead);

		this.restClient.callMethod(RestMethod.imNotifyRead, {id: idToReadFrom}).then(response => {
			Logger.warn(`I have read all the notifications from id ${idToReadFrom}`, response);
		}).catch(() => {
			// revert?
		});

		this.itemsToRead.clear();
	}

	readOnClient()
	{
		this.store.dispatch('notifications/read', {ids: [...this.itemsToRead], read: true});
	}

	readAll()
	{
		this.store.dispatch('notifications/readAll');

		this.restClient.callMethod(RestMethod.imNotifyRead, {id: 0}).then(response => {
			Logger.warn('I have read ALL the notifications', response);
		}).catch(error => {
			console.error(error);
		});
	}

	changeReadStatus(notificationId: number)
	{
		const notification = this.store.getters['notifications/getById'](notificationId);
		this.store.dispatch('notifications/read', {ids: [notification.id], read: !notification.read});

		clearTimeout(this.changeReadStatusBlockTimeout[notification.id]);
		this.changeReadStatusBlockTimeout[notification.id] = setTimeout(() => {
			this.restClient.callMethod(RestMethod.imNotifyRead, {
				id: notification.id,
				action: notification.read ? 'N' : 'Y',
				only_current: 'Y'
			}).then(() => {
				Logger.warn(`Notification ${notification.id} unread status set to ${!notification.read}`);
			}).catch(error => {
				console.error(error);
				//revert?
			});
		}, 1500);
	}

	destroy()
	{
		Logger.warn('Notification read service destroyed');
	}
}