import { Type } from 'main.core';
import BaseProvider from './base';
import Notification from '../notification/notification';
import DesktopHelper from '../helpers/desktop';

type BrowserNotificationOptions = {
	title: string,
	options?: {
		body?: string,
		tag?: string,
		icon?: string,
	},
	onclick: Function,
};

export default class BrowserProvider extends BaseProvider
{
	convertNotificationToNative(notification: Notification): BrowserNotificationOptions
	{
		const notificationOptions: BrowserNotificationOptions = {
			title: notification.getTitle() ? notification.getTitle() : '',
			options: {
				body: '',
				tag: notification.getUid(),
				renotify: true,
			},
			onclick: (event: Event) => {
				event.preventDefault();
				window.focus();

				this.notificationClick(notification.getUid());
			},
		};

		if (Type.isStringFilled(notification.getIcon()))
		{
			notificationOptions.options.icon = notification.getIcon();
		}

		if (Type.isStringFilled(notification.getText()))
		{
			notificationOptions.options.body = notification.getText();
		}

		return notificationOptions;
	}

	sendNotification(notificationOptions: BrowserNotificationOptions): void
	{
		if (!DesktopHelper.isRunningOnAnyDevice())
		{
			return;
		}

		DesktopHelper.checkRunningOnThisDevice()
			.then(isRunningOnThisDevice => {
				if (isRunningOnThisDevice)
				{
					return;
				}

				const notification = new window.Notification(notificationOptions.title, notificationOptions.options);

				notification.onclick = notificationOptions.onclick;
			});
	}
}
