import { Core } from 'im.v2.application.core';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { SoundType, UserStatus, LocalStorageKey, Settings, RawSettings } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { NotifierManager } from 'im.v2.lib.notifier';
import { DesktopManager } from 'im.v2.lib.desktop';
import { CallManager } from 'im.v2.lib.call';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';

import type { MessageAddParams } from './types/message';
import type { NotifyAddParams } from './types/notification';

export class NotifierPullHandler
{
	lastNotificationId: number = 0;

	constructor()
	{
		this.store = Core.getStore();

		this.#setCurrentUserStatus();
		this.#restoreLastNotificationId();
	}

	getModuleId(): string
	{
		return 'im';
	}

	handleMessage(params, extraData)
	{
		this.handleMessageAdd(params, extraData);
	}

	handleMessageChat(params, extraData)
	{
		this.handleMessageAdd(params, extraData);
	}

	handleMessageAdd(params: MessageAddParams, extraData: PullExtraData)
	{
		if (!this.#shouldShowNotification(params, extraData))
		{
			return;
		}

		if (this.#isChatOpened(params.dialogId))
		{
			this.#playOpenedChatMessageSound(params);

			return;
		}

		this.#playMessageSound(params);
		this.#flashDesktopIcon();

		const message = this.store.getters['messages/getById'](params.message.id);
		const dialog = this.store.getters['chats/get'](params.dialogId, true);
		const user = this.store.getters['users/get'](message.authorId);

		NotifierManager.getInstance().showMessage({
			message,
			dialog,
			user,
			lines: Boolean(params.lines),
		});

		this.#updateLastNotificationId(params.message.id);
	}

	handleNotifyAdd(params: NotifyAddParams, extraData: PullExtraData)
	{
		if (extraData.server_time_ago > 10)
		{
			Logger.warn('NotifierPullHandler: notification arrived to the user 30 seconds after it was actually sent, ignore notification');

			return;
		}

		if (params.id <= this.lastNotificationId)
		{
			Logger.warn('NotifierPullHandler: new notification id is smaller than lastNotificationId');

			return;
		}

		if (
			params.onlyFlash === true
			|| this.#isUserDnd()
			|| this.#desktopWillShowNotification()
			|| CallManager.getInstance().hasCurrentCall()
		)
		{
			return;
		}

		if (document.hasFocus())
		{
			const areNotificationsOpen = this.store.getters['application/areNotificationsOpen'];
			if (areNotificationsOpen)
			{
				return;
			}
		}

		const notification = this.store.getters['notifications/getById'](params.id);
		const user = this.store.getters['users/get'](params.userId);

		if (params.silent !== 'Y')
		{
			SoundNotificationManager.getInstance().playOnce(SoundType.reminder);
		}

		this.#flashDesktopIcon();

		NotifierManager.getInstance().showNotification(notification, user);

		this.#updateLastNotificationId(params.id);
	}

	#shouldShowNotification(params: MessageAddParams, extraData: PullExtraData): boolean
	{
		if (extraData.server_time_ago > 10)
		{
			Logger.warn('NotifierPullHandler: message arrived to the user 30 seconds after it was actually sent, ignore message');

			return false;
		}

		if (params.message.id <= this.lastNotificationId)
		{
			Logger.warn('NotifierPullHandler: new message id is smaller than lastNotificationId');

			return false;
		}

		if (Core.getUserId() === params.message.senderId)
		{
			return false;
		}

		if (params.lines && !this.#shouldShowLinesNotification(params))
		{
			return false;
		}

		const messageWithoutNotification = !params.notify || params.message?.params?.NOTIFY === 'N';
		if (messageWithoutNotification || !this.#shouldShowToUser(params) || this.#desktopWillShowNotification())
		{
			return false;
		}

		const callIsActive = CallManager.getInstance().hasCurrentCall();
		if (callIsActive && CallManager.getInstance().getCurrentCallDialogId() !== params.dialogId.toString())
		{
			return false;
		}

		const screenSharingIsActive = CallManager.getInstance().hasCurrentScreenSharing();
		if (screenSharingIsActive)
		{
			return false;
		}

		return true;
	}

	#shouldShowLinesNotification(params: MessageAddParams): boolean
	{
		if (this.#isLinesChatOpened(params.dialogId))
		{
			return false;
		}

		const authorId = params.message.senderId;
		if (authorId > 0 && params.users[authorId].extranet === false)
		{
			return true;
		}

		const counter = this.store.getters['counters/getSpecificLinesCounter'](params.chatId);

		return counter === 0;
	}

	#isChatOpened(dialogId: string): boolean
	{
		const isChatOpen = this.store.getters['application/isChatOpen'](dialogId);

		return Boolean(document.hasFocus() && isChatOpen);
	}

	#isLinesChatOpened(dialogId: string): boolean
	{
		const isLinesChatOpen = this.store.getters['application/isLinesChatOpen'](dialogId);

		return Boolean(document.hasFocus() && isLinesChatOpen);
	}

	#isImportantMessage(params: MessageAddParams): boolean
	{
		const { message } = params;

		return message.isImportant || message.importantFor.includes(Core.getUserId());
	}

	#shouldShowToUser(params: MessageAddParams): boolean
	{
		if (this.#isImportantMessage(params))
		{
			return true;
		}

		const dialog = this.store.getters['chats/get'](params.dialogId, true);
		const isMuted = dialog.muteList.includes(Core.getUserId());

		return !this.#isUserDnd() && !isMuted;
	}

	#isUserDnd(): boolean
	{
		const status = this.store.getters['application/settings/get'](Settings.user.status);

		return status === UserStatus.dnd;
	}

	#desktopWillShowNotification(): boolean
	{
		const isDesktopChatWindow = DesktopManager.isChatWindow();

		return !isDesktopChatWindow && DesktopManager.getInstance().isDesktopActive();
	}

	#flashDesktopIcon(): void
	{
		if (!DesktopManager.isDesktop())
		{
			return;
		}

		DesktopApi.flashIcon();
	}

	#playOpenedChatMessageSound(params: MessageAddParams)
	{
		if (this.#isImportantMessage(params))
		{
			SoundNotificationManager.getInstance().forcePlayOnce(SoundType.newMessage2);

			return;
		}

		SoundNotificationManager.getInstance().playOnce(SoundType.newMessage2);
	}

	#playMessageSound(params: MessageAddParams)
	{
		if (this.#isImportantMessage(params))
		{
			SoundNotificationManager.getInstance().forcePlayOnce(SoundType.newMessage1);

			return;
		}

		SoundNotificationManager.getInstance().playOnce(SoundType.newMessage1);
	}

	#restoreLastNotificationId()
	{
		const rawLastNotificationId = LocalStorageManager.getInstance().get(LocalStorageKey.lastNotificationId, 0);

		this.lastNotificationId = Number.parseInt(rawLastNotificationId, 10);
	}

	#updateLastNotificationId(notificationId: number)
	{
		const WRITE_TO_STORAGE_TIMEOUT = 2000;

		this.lastNotificationId = notificationId;
		clearTimeout(this.writeToStorageTimeout);
		this.writeToStorageTimeout = setTimeout(() => {
			LocalStorageManager.getInstance().set(LocalStorageKey.lastNotificationId, notificationId);
		}, WRITE_TO_STORAGE_TIMEOUT);
	}

	#setCurrentUserStatus()
	{
		const applicationData: { settings: RawSettings } = Core.getApplicationData();
		if (!applicationData.settings?.status)
		{
			return;
		}

		Core.getStore().dispatch('application/settings/set', {
			[Settings.user.status]: applicationData.settings.status,
		});
	}
}
