import BaseProvider from './base';
import DesktopHelper from '../helpers/desktop';
import Notification from '../notification/notification';
import PushNotification from '../notification/push-notification';

import type { ProviderOptions } from './provider-options';


export default class DesktopProvider extends BaseProvider
{
	constructor(options: ?ProviderOptions = {})
	{
		super(options);

		if (this.getEventNamespace())
		{
			this.registerEvents();
		}
	}

	convertNotificationToNative(notification: Notification): string
	{
		throw new Error('convertNotificationToNative() method must be implemented.');
	}

	canSendNotification(notification: Notification): boolean
	{
		//Desktop push & pull notifications, unlike regular ones, can be sent from only one tab to avoid duplication.
		return DesktopHelper.isMainTab() || !(notification instanceof PushNotification);
	}

	sendNotification(notificationUid: string): void
	{
		BXDesktopSystem.NotificationShow(notificationUid);
	}

	registerEvents(): void
	{
		window.addEventListener('BXNotificationClick', (event) => this.onNotificationClick(event));
		window.addEventListener('BXNotificationAction', (event) => this.onNotificationAction(event));
		window.addEventListener('BXNotificationDismissed', (event) => this.onNotificationClose(event));
	}

	onNotificationClick(event): void
	{
		const [id] = event.detail;

		BXDesktopSystem.SetActiveTab();
		this.notificationClick(id);
	}

	onNotificationAction(event): void
	{
		const [id, action, userInput] = event.detail;

		this.notificationAction(id, action, userInput);
	}

	onNotificationClose(event): void
	{
		const [id, reason] = event.detail;

		this.notificationClose(id, reason);
	}
}
