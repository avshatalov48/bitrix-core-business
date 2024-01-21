import { Type } from 'main.core';
import 'ui.notification';
import BaseProvider from './base';
import Notification from '../notification/notification';
import NotificationAction from '../notification/notification-action';
import NotificationCloseReason from '../notification/notification-close-reason';
import BrowserNotification from '../views/browser-notification/browser-notification';
import BrowserNotificationAction from '../views/browser-notification/browser-notification-action';
import type { ProviderOptions } from './provider-options';

export default class BrowserPageProvider extends BaseProvider
{
	static BROADCAST_CHANNEL = 'ui-notification-manager-channel';

	static MESSAGE_TYPE = {
		closeNotification: 'close-notification',
		closeAllNotifications: 'close-all-notifications',
	};

	static autoHideDelay = 6000;

	constructor(options: ?ProviderOptions = {})
	{
		super(options);

		this.broadcastChannel = null;
		this.setBroadcast(options);
	}

	setBroadcast(options): void
	{
		this.broadcastChannel = new BroadcastChannel(BrowserPageProvider.BROADCAST_CHANNEL);
		this.broadcastChannel.onmessage = (event) => this.handleMessageEvent(event);
		this.postMessageToBroadcast(BrowserPageProvider.MESSAGE_TYPE.closeAllNotifications);
	}

	convertNotificationToNative(notification: Notification): BX.UI.Notification.BalloonOptions
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

		const contextClickHandler: Function = () => {
			this.closeAllNotifications();
		};

		const userInputHandler: Function = (userInput) => {
			this.notificationAction(notification.getUid(), NotificationAction.BUTTON_1, userInput);
		};

		const balloonOptions: BX.UI.Notification.BalloonOptions = {
			id: notification.getUid(),
			category: notification.getCategory(),
			type: BrowserNotification,
			data: {
				title: notification.getTitle(),
				text: notification.getText(),
				icon: notification.getIcon(),
				closedByUserHandler,
				clickHandler,
				contextClickHandler,
				userInputHandler,
			},
			actions: [],
			width: 380,
			position: 'top-right',
			autoHideDelay: BrowserPageProvider.autoHideDelay,
			events: {
				onClose: (event) => {
					this.onBalloonClose(event);
				},
			},
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
				},
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
				},
			};

			balloonOptions.actions.push(action2Options);
		}

		return balloonOptions;
	}

	onBalloonClose(event): void
	{
		const id = event.getBalloon().id;
		this.postMessageToBroadcast(BrowserPageProvider.MESSAGE_TYPE.closeNotification, id);
	}

	postMessageToBroadcast(action, uid = ''): void
	{
		if (action === BrowserPageProvider.MESSAGE_TYPE.closeNotification && !uid)
		{
			return;
		}

		this.broadcastChannel.postMessage({
			action,
			...(uid ? { uid } : {}),
		});
	}

	handleMessageEvent(event: MessageEvent): void
	{
		if (event.data.action === BrowserPageProvider.MESSAGE_TYPE.closeNotification)
		{
			const uid = event.data.uid;
			const id = Notification.decodeUidToId(uid);
			const balloon = this.findBalloonById(id);

			if (balloon === null)
			{
				return;
			}

			this.closeNotification(balloon);
		}
		else if (event.data.action === BrowserPageProvider.MESSAGE_TYPE.closeAllNotifications)
		{
			this.closeAllNotifications();
		}
	}

	findBalloonById(id: string): UI.Notification.Balloon
	{
		const balloonsKeys = Object.keys(BX.UI.Notification.Center.balloons);
		for (const uid of balloonsKeys)
		{
			if (uid.startsWith(id))
			{
				return BX.UI.Notification.Center.balloons[uid];
			}
		}

		return null;
	}

	closeNotification(balloon: BX.UI.Notification.Balloon): void
	{
		this.notificationClose(balloon.id, NotificationCloseReason.CLOSED_BY_USER);
		balloon.close();
	}

	closeAllNotifications(): void
	{
		BX.UI.Notification.Center.getDefaultStack()?.clear();
	}

	sendNotification(notification: BX.UI.Notification.BalloonOptions): void
	{
		BX.UI.Notification.Center.notify(notification);
	}

	onNotificationAction(event, balloon, action): void
	{
		balloon.close();
		this.notificationAction(balloon.id, action.id);
	}
}
