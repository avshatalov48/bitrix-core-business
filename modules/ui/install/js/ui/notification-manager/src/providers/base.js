import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import Notification from '../notification/notification';
import NotificationEvent from '../notification/notification-event';
import NotificationAction from '../notification/notification-action';
import NotificationCloseReason from '../notification/notification-close-reason';

import type { ProviderOptions } from './provider-options';

export default class BaseProvider extends EventEmitter
{
	static NOTIFICATION_LIFETIME = 14400000; //The lifetime of the notification is 4 hours

	constructor(options: ?ProviderOptions = {})
	{
		super();

		if (Type.isStringFilled(options.eventNamespace))
		{
			this.setEventNamespace(options.eventNamespace);
		}
	}

	convertNotificationToNative(notification: Notification): any
	{
		throw new Error('convertNotificationToNative() method must be implemented.');
	}

	sendNotification(nativeNotification: any): void
	{
		throw new Error('sendNotification() method must be implemented.');
	}

	canSendNotification(notification: Notification): boolean
	{
		return true;
	}

	notify(notification: Notification): void
	{
		if (!this.canSendNotification(notification))
		{
			return;
		}

		const nativeNotification = this.convertNotificationToNative(notification);

		this.sendNotification(nativeNotification);
	}

	notificationClick(uid: string = ''): void
	{
		const eventOptions = {
			data: {
				id: Notification.decodeUidToId(uid),
			},
		};

		this.emit(NotificationEvent.CLICK, new NotificationEvent(eventOptions));
	}

	notificationAction(uid: string = '', action: string = '', userInput: ?string = null): void
	{
		if (!NotificationAction.isSupported(action))
		{
			console.warn(`NotificationManager: Unknown notification action "${action}".`);
		}

		const eventOptions = {
			data: {
				id: Notification.decodeUidToId(uid),
				action,
			},
		};

		if (userInput)
		{
			eventOptions.data.userInput = userInput;
		}

		this.emit(NotificationEvent.ACTION, new NotificationEvent(eventOptions));
	}

	notificationClose(uid: string = '', reason: string = ''): void
	{
		if (!NotificationCloseReason.isSupported(reason))
		{
			console.warn(`NotificationManager: Unknown notification close reason "${reason}".`);
		}

		const eventOptions = {
			data: {
				id: Notification.decodeUidToId(uid),
				reason,
			},
		};

		this.emit(NotificationEvent.CLOSE, new NotificationEvent(eventOptions));
	}
}
