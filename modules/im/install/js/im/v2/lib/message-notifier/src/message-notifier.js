import { Loc } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';
import { Notifier, NotificationOptions } from 'ui.notification-manager';

import { SoundNotificationManager } from 'im.v2.lib.sound-notification';
import { Core } from 'im.v2.application.core';
import { Parser } from 'im.v2.lib.parser';
import { DesktopManager, DesktopBroadcastManager } from 'im.v2.lib.desktop';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';
import { Messenger } from 'im.public';
import { NotificationTypesCodes, ChatType, DesktopBroadcastAction, SoundType, EventType } from 'im.v2.const';
import { NotificationService } from 'im.v2.provider.service.notification';

import { NotificationId, NotificationIdPrefix } from './classes/notification-id';

import { MessageOptionsBuilder } from './classes/message-options-builder';

import type {
	ImModelUser,
	ImModelNotification,
	ImModelNotificationButton,
} from 'im.v2.model';

export type NotifierClickParams = {
	id: string // 'im-notify_2558' | 'im-chat_1_2565'
};

type NotifierActionParams = {
	action: string, // 'button_1'
	id: string, // 'im-notify-2561'
	userInput?: string
};

type MessageNotificationParams = {
	messageId: number,
	dialogId: string,
	isLines: boolean,
	isImportant: boolean
}

const ACTION_BUTTON_PREFIX = 'button_';
const ButtonNumber = {
	first: '1',
	second: '2',
};

export const NotifierShowMessageAction = {
	skip: 'skip',
	show: 'show',
};

export class MessageNotifierManager
{
	static #instance: MessageNotifierManager;

	#store: Store;
	#notificationService: NotificationService;

	static getInstance(): MessageNotifierManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		MessageNotifierManager.getInstance();
	}

	constructor()
	{
		this.#store = Core.getStore();
		this.#notificationService = new NotificationService();

		this.#subscribeToNotifierEvents();
	}

	async handleIncomingMessage(params: MessageNotificationParams)
	{
		const { isImportant, dialogId } = params;

		if (await this.#shouldSkipNotification(dialogId))
		{
			this.#playOpenedChatMessageSound(isImportant);

			return;
		}

		this.#playMessageSound(isImportant);
		this.#flashDesktopIcon();

		MessageNotifierManager.getInstance().showMessage(params);
	}

	handleIncomingNotification(params: {notificationId: number, userId: number, isSilent: boolean })
	{
		const { notificationId, userId, isSilent } = params;

		const notification = this.#store.getters['notifications/getById'](notificationId);
		const user = this.#store.getters['users/get'](userId);

		if (!isSilent)
		{
			SoundNotificationManager.getInstance().playOnce(SoundType.reminder);
		}

		this.#flashDesktopIcon();

		MessageNotifierManager.getInstance().showNotification(notification, user);
	}

	showMessage(params: MessageNotificationParams)
	{
		const { messageId, dialogId, isLines } = params;
		const message = this.#store.getters['messages/getById'](messageId);
		const chat = this.#store.getters['chats/get'](dialogId, true);
		const user = this.#store.getters['users/get'](message.authorId);

		const builder = new MessageOptionsBuilder({ message, chat, user, isLines });
		const notificationOptions = builder.build();

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

	onNotifierClick(params: NotifierClickParams)
	{
		const { id } = params;

		const { prefix, dialogId } = NotificationId.parse(id);
		if (prefix === NotificationIdPrefix.chat)
		{
			void Messenger.openChat(dialogId);
		}
		else if (prefix === NotificationIdPrefix.taskComments)
		{
			void Messenger.openTaskComments(dialogId);
		}
		else if (prefix === NotificationIdPrefix.lines)
		{
			void Messenger.openLines(dialogId);
		}
		else if (prefix === NotificationIdPrefix.notify)
		{
			void Messenger.openNotifications();
		}
	}

	#prepareNotificationOptions(
		title: string,
		notification: ImModelNotification,
		user?: ImModelUser,
	): NotificationOptions
	{
		const id = NotificationId.build({
			prefix: NotificationIdPrefix.notify,
			notifyId: notification.id,
		});

		const notificationOptions = {
			id,
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
		Notifier.subscribe('click', async (event: BaseEvent<NotifierClickParams>) => {
			if (!DesktopApi.isAirDesignEnabledInDesktop())
			{
				DesktopApi.activateWindow();

				this.onNotifierClick(event.getData());

				return;
			}

			await DesktopApi.showBrowserWindow();

			if (DesktopApi.isFeatureSupported(DesktopFeature.portalTabActivation.id))
			{
				await DesktopApi.handlePortalTabActivation();
			}

			DesktopBroadcastManager.getInstance().sendActionMessage({
				action: DesktopBroadcastAction.notification,
				params: event.getData(),
			});
		});

		Notifier.subscribe('action', (event: BaseEvent<NotifierActionParams>) => {
			this.#onNotifierAction(event.getData());
		});
	}

	#onNotifierAction(params: NotifierActionParams)
	{
		const { id, action, userInput } = params;

		const { prefix, notifyId } = NotificationId.parse(id);
		if (prefix !== NotificationIdPrefix.notify)
		{
			return;
		}

		const notification = this.#store.getters['notifications/getById'](notifyId);
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

	#isConfirmButtonAction(action: string, notification: ImModelNotification): boolean
	{
		const notificationType = notification.sectionCode;

		return action.startsWith(ACTION_BUTTON_PREFIX) && notificationType === NotificationTypesCodes.confirm;
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

	#isChatOpened(dialogId: string): boolean
	{
		const isChatOpen = this.#store.getters['application/isChatOpen'](dialogId);

		return Boolean(document.hasFocus() && isChatOpen);
	}

	#playOpenedChatMessageSound(isImportant: boolean)
	{
		if (isImportant)
		{
			SoundNotificationManager.getInstance().forcePlayOnce(SoundType.newMessage2);

			return;
		}

		SoundNotificationManager.getInstance().playOnce(SoundType.newMessage2);
	}

	#playMessageSound(isImportant: boolean)
	{
		if (isImportant)
		{
			SoundNotificationManager.getInstance().forcePlayOnce(SoundType.newMessage1);

			return;
		}

		SoundNotificationManager.getInstance().playOnce(SoundType.newMessage1);
	}

	#flashDesktopIcon()
	{
		if (!DesktopManager.isDesktop())
		{
			return;
		}

		DesktopApi.flashIcon();
	}

	async #shouldSkipNotification(dialogId: string): Promise<boolean>
	{
		if (this.#isChatOpened(dialogId))
		{
			return true;
		}

		const eventResult = await EventEmitter.emitAsync(EventType.notifier.onBeforeShowMessage, { dialogId });

		return eventResult.includes(NotifierShowMessageAction.skip);
	}
}
