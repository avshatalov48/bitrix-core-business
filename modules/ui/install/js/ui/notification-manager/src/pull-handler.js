import { Notifier } from './notifier';
import PushNotification from './notification/push-notification';

import type { NotificationOptions } from './notification/notification-options';

export default class PullHandler
{
	getModuleId()
	{
		return 'ui';
	}

	handleNotify(params, extra, command)
	{
		const notification = params.notification;
		if (!notification)
		{
			throw new Error('NotificationManager: Incorrect notification format');
		}

		const notificationOptions: NotificationOptions = notification;

		const pushNotification = new PushNotification(notificationOptions);

		Notifier.sendNotification(pushNotification);
	}
}