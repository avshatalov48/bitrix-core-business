import { Notifier, NotificationOptions } from 'ui.notification-manager';
import { Store } from 'ui.vue3.vuex';
import { Loc } from 'main.core';
import { BaseEvent } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { Parser } from 'im.v2.lib.parser';
import { DesktopManager } from 'im.v2.lib.desktop';
import { Messenger } from 'im.public';
import { NotificationTypesCodes, ChatType } from 'im.v2.const';
import { NotificationService } from 'im.v2.provider.service';

import type {
	ImModelUser,
	ImModelChat,
	ImModelMessage,
	ImModelNotification,
	ImModelNotificationButton,
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
const COPILOT_MESSAGE_PREFIX = 'im-copilot';
const LINES_MESSAGE_PREFIX = 'im-lines';
const NOTIFICATION_PREFIX = 'im-notify';
const ACTION_BUTTON_PREFIX = 'button_';
const ButtonNumber = {
	first: '1',
	second: '2',
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

	showMessage(params: {message: ImModelMessage, dialog: ImModelChat, user?: ImModelUser, lines: boolean})
	{
		const { message, dialog, user, lines } = params;
		let text = '';
		if (user && dialog.type !== ChatType.user)
		{
			text += `${user.name}: `;
		}

		text += Parser.purifyMessage(message);

		let id = `${CHAT_MESSAGE_PREFIX}-${dialog.dialogId}-${message.id}`;
		if (dialog.type === ChatType.copilot)
		{
			id = `${COPILOT_MESSAGE_PREFIX}-${dialog.dialogId}-${message.id}`;
		}
		else if (lines)
		{
			id = `${LINES_MESSAGE_PREFIX}-${dialog.dialogId}-${message.id}`;
		}
		const notificationOptions = {
			id,
			title: dialog.name,
			icon: dialog.avatar || user?.avatar,
			text,
		};

		const isDesktopFocused: boolean = DesktopManager.isChatWindow() && document.hasFocus();
		if (isDesktopFocused)
		{
			Notifier.notifyViaBrowserProvider(notificationOptions);
		}
		else
		{
			Notifier.notify(notificationOptions);
		}
	}

	showNotification(notification: ImModelNotification, user?: ImModelUser)
	{
		let title = Loc.getMessage('IM_LIB_NOTIFIER_NOTIFY_SYSTEM_TITLE');
		if (notification.title)
		{
			title = notification.title;
		}
		else if (user)
		{
			title = user.name;
		}

		const notificationOptions = this.#prepareNotificationOptions(title, notification, user);

		const isDesktopFocused: boolean = DesktopManager.isChatWindow() && document.hasFocus();
		if (isDesktopFocused)
		{
			Notifier.notifyViaBrowserProvider(notificationOptions);
		}
		else
		{
			Notifier.notify(notificationOptions);
		}
	}

	#prepareNotificationOptions(
		title: string,
		notification: ImModelNotification,
		user?: ImModelUser,
	): NotificationOptions
	{
		const notificationOptions = {
			id: `${NOTIFICATION_PREFIX}-${notification.id}`,
			title,
			icon: user ? user.avatar : '',
			text: Parser.purifyNotification(notification),
		};

		if (notification.sectionCode === NotificationTypesCodes.confirm)
		{
			const [firstButton, secondButton] = notification.notifyButtons;
			notificationOptions.button1Text = firstButton.TEXT;
			notificationOptions.button2Text = secondButton.TEXT;
		}
		else if (notification.params?.canAnswer === 'Y')
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
		const { id } = params;
		if (this.#isChatMessage(id))
		{
			const dialogId = this.#extractDialogId(id);
			Messenger.openChat(dialogId);
		}
		else if (this.#isCopilotMessage(id))
		{
			const dialogId = this.#extractDialogId(id);
			Messenger.openCopilot(dialogId);
		}
		else if (this.#isLinesMessage(id))
		{
			const dialogId = this.#extractDialogId(id);
			Messenger.openLines(dialogId);
		}
		else if (this.#isNotification(id))
		{
			Messenger.openNotifications();
		}
	}

	#onNotifierAction(params: NotifierActionParams)
	{
		const { id, action, userInput } = params;
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
			text,
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

	#isCopilotMessage(id: string): boolean
	{
		return id.startsWith(COPILOT_MESSAGE_PREFIX);
	}

	#isLinesMessage(id: string): boolean
	{
		return id.startsWith(LINES_MESSAGE_PREFIX);
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
