import {Notifier, NotificationOptions} from 'ui.notification-manager';
import {Store} from 'ui.vue3.vuex';
import {Loc} from 'main.core';
import {BaseEvent} from 'main.core.events';

import {Core} from 'im.v2.application.core';
import {Parser} from 'im.v2.lib.parser';
import {Messenger} from 'im.public';
import {NotificationTypesCodes} from 'im.v2.const';
import {NotificationService} from 'im.v2.provider.service';

import type {
	ImModelUser,
	ImModelDialog,
	ImModelMessage,
	ImModelNotification,
	ImModelNotificationButton
} from 'im.v2.model';

type NotifierClickParams = {
	id: string // 'im-notify-2558' | 'im-chat-1-2565'
};

type NotifierActionParams = {
	action: string, // 'button_1'
	id: string, // 'im-notify-2561'
	userInput?: string
};

const CHAT_MESSAGE_PREFIX = 'im-chat';
const NOTIFICATION_PREFIX = 'im-notify';
const ACTION_BUTTON_PREFIX = 'button_';
const ButtonNumber = {
	first: '1',
	second: '2'
};

export class NotifierManager
{
	static #instance: NotifierManager;

	#store: Store;
	#notificationService: NotificationService;

	static getInstance(): NotifierManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		NotifierManager.getInstance();
	}

	constructor()
	{
		this.#store = Core.getStore();
		this.#notificationService = new NotificationService();

		this.#subscribeToNotifierEvents();
	}

	showMessage(message: ImModelMessage, dialog: ImModelDialog, user?: ImModelUser)
	{
		let text = '';
		if (user)
		{
			text += `${user.name}: `;
		}

		text += Parser.purifyMessage(message);

		const notificationOptions = {
			id: `im-chat-${dialog.dialogId}-${message.id}`,
			title: dialog.name,
			icon: dialog.avatar || user?.avatar,
			text
		};

		Notifier.notify(notificationOptions);
	}

	showNotification(notification: ImModelNotification, user?: ImModelUser)
	{
		let title;
		if (notification.title)
		{
			title = notification.title;
		}
		else if (user)
		{
			title = user.name;
		}
		else
		{
			title = Loc.getMessage('IM_LIB_NOTIFIER_NOTIFY_SYSTEM_TITLE');
		}

		const notificationOptions = this.#prepareNotificationOptions(title, notification, user);

		Notifier.notify(notificationOptions);
	}

	#prepareNotificationOptions(
		title: string,
		notification: ImModelNotification,
		user?: ImModelUser
	): NotificationOptions
	{
		const notificationOptions = {
			id: `im-notify-${notification.id}`,
			title,
			icon: user? user.avatar: '',
			text: Parser.purifyNotification(notification),
		};

		if (notification.sectionCode === NotificationTypesCodes.confirm)
		{
			const [firstButton, secondButton] = notification.notifyButtons;
			notificationOptions.button1Text = firstButton.TEXT;
			notificationOptions.button2Text = secondButton.TEXT;
		}
		else if (notification.params?.CAN_ANSWER === 'Y')
		{
			notificationOptions.inputPlaceholderText = Loc.getMessage('IM_LIB_NOTIFIER_NOTIFY_REPLY_PLACEHOLDER');
		}

		return notificationOptions;
	}

	#subscribeToNotifierEvents()
	{
		Notifier.subscribe('click', (event: BaseEvent<NotifierClickParams>) => {
			this.#onNotifierClick(event.getData());
		});

		Notifier.subscribe('action', (event: BaseEvent<NotifierActionParams>) => {
			this.#onNotifierAction(event.getData());
		});
	}

	#onNotifierClick(params: NotifierClickParams)
	{
		const {id} = params;
		if (this.#isChatMessage(id))
		{
			const dialogId = this.#extractDialogId(id);
			Messenger.openChat(dialogId);
		}
		else if (this.#isNotification(id))
		{
			Messenger.openNotifications();
		}
	}

	#onNotifierAction(params: NotifierActionParams)
	{
		const {id, action, userInput} = params;
		if (!this.#isNotification(id))
		{
			return;
		}

		const notificationId = this.#extractNotificationId(id);
		const notification = this.#store.getters['notifications/getById'](notificationId);
		if (userInput)
		{
			this.#onNotifierQuickAnswer(notification, userInput);
		}
		else if (this.#isConfirmButtonAction(action, notification))
		{
			this.#onNotifierButtonClick(action, notification);
		}
	}

	#onNotifierQuickAnswer(notification: ImModelNotification, text: string)
	{
		this.#notificationService.sendQuickAnswer({
			id: notification.id,
			text: text,
		});
	}

	#onNotifierButtonClick(action: string, notification: ImModelNotification)
	{
		const [firstButton, secondButton] = notification.notifyButtons;
		const actionButtonNumber = this.#extractButtonNumber(action);
		if (actionButtonNumber === ButtonNumber.first)
		{
			this.#sendButtonAction(notification, firstButton);
		}
		else if (actionButtonNumber === ButtonNumber.second)
		{
			this.#sendButtonAction(notification, secondButton);
		}
	}

	#sendButtonAction(notification: ImModelNotification, button: ImModelNotificationButton)
	{
		const [notificationId, value] = this.#extractButtonParams(button);

		this.#notificationService.sendConfirmAction(notificationId, value);
	}

	#isChatMessage(id: string): boolean
	{
		return id.startsWith(CHAT_MESSAGE_PREFIX);
	}

	#isNotification(id: string): boolean
	{
		return id.startsWith(NOTIFICATION_PREFIX);
	}

	#isConfirmButtonAction(action: string, notification: ImModelNotification): boolean
	{
		const notificationType = notification.sectionCode;

		return action.startsWith(ACTION_BUTTON_PREFIX) && notificationType === NotificationTypesCodes.confirm;
	}

	#extractDialogId(id: string): string
	{
		// 'im-chat-1-2565'
		return id.split('-')[2];
	}

	#extractNotificationId(id: string): string
	{
		// 'im-notify-2558'
		return id.split('-')[2];
	}

	#extractButtonNumber(action: string): string
	{
		// 'button_1'
		return action.split('_')[1];
	}

	#extractButtonParams(button: ImModelNotificationButton): string[]
	{
		// '2568|Y'
		return button.COMMAND_PARAMS.split('|');
	}
}
