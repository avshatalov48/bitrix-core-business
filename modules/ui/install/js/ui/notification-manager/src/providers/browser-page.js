import { Type } from 'main.core';
import { UI } from 'ui.notification';
import BaseProvider from './base';
import Notification from '../notification/notification';
import NotificationAction from '../notification/notification-action';
import NotificationCloseReason from '../notification/notification-close-reason';
import BrowserNotification from '../views/browser-notification/browser-notification';
import BrowserNotificationAction from '../views/browser-notification/browser-notification-action';

export default class BrowserPageProvider extends BaseProvider
{
	convertNotificationToNative(notification: Notification): UI.Notification.BalloonOptions
	{
		if (!Type.isStringFilled(notification.getId()))
		{
			throw new Error(`NotificationManager: You cannot send a notification without an ID.`);
		}

		const closedByUserHandler: Function = () => {
			this.notificationClose(notification.getUid(), NotificationCloseReason.CLOSED_BY_USER);
		};

		const clickHandler: Function = () => {
			this.notificationClick(notification.getUid());
		};

		const userInputHandler: Function = (userInput) => {
			this.notificationAction(notification.getUid(), NotificationAction.BUTTON_1, userInput);
		};

		const balloonOptions: UI.Notification.BalloonOptions = {
			id: notification.getUid(),
			category: notification.getCategory(),
			type: BrowserNotification,
			data: {
				title: notification.getTitle(),
				text: notification.getText(),
				icon: notification.getIcon(),
				closedByUserHandler,
				clickHandler,
				userInputHandler,
			},
			actions: [],
			width: 380,
			position: 'top-right',
			autoHideDelay: 6000,
		};

		if (notification.getInputPlaceholderText())
		{
			balloonOptions.data.inputPlaceholderText = notification.getInputPlaceholderText();

			return balloonOptions;
		}

		const showButton1 = notification.getButton1Text() && Type.isStringFilled(notification.getButton1Text());
		const showButton2 = notification.getButton2Text() && Type.isStringFilled(notification.getButton2Text());

		if (showButton1)
		{
			const action1Options = {
				id: NotificationAction.BUTTON_1,
				title: notification.getButton1Text(),
				events: {
					click: (event, balloon, action) => this.onNotificationAction(event, balloon, action),
				}
			};

			if (showButton2)
			{
				action1Options.buttonType = BrowserNotificationAction.TYPE_ACCEPT;
			}

			balloonOptions.actions.push(action1Options);
		}

		if (showButton2)
		{
			const action2Options = {
				id: NotificationAction.BUTTON_2,
				title: notification.getButton2Text(),
				events: {
					click: (event, balloon, action) => this.onNotificationAction(event, balloon, action),
				}
			};

			balloonOptions.actions.push(action2Options);
		}

		return balloonOptions;
	}

	sendNotification(notification: UI.Notification.BalloonOptions): void
	{
		UI.Notification.Center.notify(notification);
	}

	onNotificationAction(event, balloon, action): void
	{
		balloon.close();
		this.notificationAction(balloon.id, action.id);
	}
}
