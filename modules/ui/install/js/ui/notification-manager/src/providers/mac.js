import { Type, Loc } from 'main.core';
import BaseProvider from './base';
import DesktopProvider from './desktop';
import Notification from '../notification/notification';
import NotificationAction from '../notification/notification-action';

export default class MacProvider extends DesktopProvider
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
			//this.addTextToNotification(notificationUid, notification.getText());
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
				Loc.getMessage('UI_NOTIFICATION_MANAGER_CLOSE'),
				NotificationAction.BUTTON_2
			);
		}

		BXDesktopSystem.NotificationSetExpiration(notificationUid, BaseProvider.NOTIFICATION_LIFETIME);

		return notificationUid;
	}

	addTextToNotification(notificationUid: string, text: string): void
	{
		if (text.trim() === '')
		{
			return;
		}

		const languageSafeRowLength = 44;

		if (text.length <= languageSafeRowLength)
		{
			BXDesktopSystem.NotificationAddText(notificationUid, text);
			return;
		}

		const space = ' ';

		let firstRow = '';
		let words: Array<string> = text.split(space);

		while (words.length > 0)
		{
			if (firstRow.length + words[0].length + 1 > languageSafeRowLength)
			{
				break;
			}

			firstRow += words.shift() + space;
		}

		BXDesktopSystem.NotificationAddText(notificationUid, firstRow);

		let secondRow = words.join(space);
		if (secondRow !== '')
		{
			BXDesktopSystem.NotificationAddText(notificationUid, secondRow);
		}
	}
}
