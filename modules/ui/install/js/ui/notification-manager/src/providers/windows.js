import { Type } from 'main.core';
import BaseProvider from './base';
import DesktopProvider from './desktop';
import Notification from '../notification/notification';
import NotificationAction from '../notification/notification-action';

export default class WindowsProvider extends DesktopProvider
{
	convertNotificationToNative(notification: Notification): string
	{
		if (!Type.isStringFilled(notification.getId()))
		{
			throw new Error(`NotificationManager: You cannot send a notification without an ID.`);
		}

		const notificationUid = notification.getUid();

		BXDesktopSystem.NotificationCreate(notificationUid);

		if (Type.isStringFilled(notification.getTitle()))
		{
			BXDesktopSystem.NotificationAddText(notificationUid, notification.getTitle());
		}

		if (Type.isStringFilled(notification.getText()))
		{
			BXDesktopSystem.NotificationAddText(notificationUid, notification.getText());
		}

		if (Type.isStringFilled(notification.getIcon()))
		{
			BXDesktopSystem.NotificationAddImage(notificationUid, notification.getIcon());
		}

		if (
			notification.getInputPlaceholderText()
			&& Type.isString(notification.getInputPlaceholderText())
		)
		{
			BXDesktopSystem.NotificationAddInput(
				notificationUid,
				notification.getInputPlaceholderText(),
				NotificationAction.USER_INPUT
			);
		}

		if (notification.getButton1Text() && Type.isStringFilled(notification.getButton1Text()))
		{
			BXDesktopSystem.NotificationAddAction(
				notificationUid,
				notification.getButton1Text(),
				NotificationAction.BUTTON_1
			);
		}

		if (notification.getButton2Text() && Type.isStringFilled(notification.getButton2Text()))
		{
			BXDesktopSystem.NotificationAddAction(
				notificationUid,
				notification.getButton2Text(),
				NotificationAction.BUTTON_2
			);
		}

		BXDesktopSystem.NotificationSetExpiration(notificationUid, BaseProvider.NOTIFICATION_LIFETIME);

		return notificationUid;
	}
}
