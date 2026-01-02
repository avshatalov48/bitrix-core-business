import { Core } from 'im.v2.application.core';
import { UserStatus, LocalStorageKey, Settings, RawSettings, UserType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { MessageNotifierManager } from 'im.v2.lib.message-notifier';
import { DesktopManager } from 'im.v2.lib.desktop';
import { CallManager } from 'im.v2.lib.call';
import { LocalStorageManager } from 'im.v2.lib.local-storage';

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

	handleMessage(params: MessageAddParams, extraData: PullExtraData)
	{
		this.handleMessageAdd(params, extraData);
	}

	handleMessageChat(params: MessageAddParams, extraData: PullExtraData)
	{
		this.handleMessageAdd(params, extraData);
	}

	handleMessageAdd(params: MessageAddParams, extraData: PullExtraData)
	{
		if (!this.#shouldHandleMessageNotification(params, extraData))
		{
			return;
		}

		void MessageNotifierManager.getInstance().handleIncomingMessage({
			dialogId: params.dialogId.toString(),
			isImportant: this.#isImportantMessage(params),
			messageId: params.message.id,
			isLines: Boolean(params.lines),
		});

		this.#updateLastNotificationId(params.message.id);
	}

	handleNotifyAdd(params: NotifyAddParams, extraData: PullExtraData)
	{
		if (!this.#shouldHandleNotification(params, extraData))
		{
			return;
		}

		MessageNotifierManager.getInstance().handleIncomingNotification({
			notificationId: params.id,
			userId: params.userId,
			isSilent: params.silent === 'Y',
		});

		this.#updateLastNotificationId(params.id);
	}

	#shouldHandleMessageNotification(params: MessageAddParams, extraData: PullExtraData): boolean
	{
		if (this.#isExpired(extraData))
		{
			return false;
		}

		if (!this.#checkLastNotificationId(params.message.id))
		{
			return false;
		}

		if (this.#isCurrentUserSender(params))
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

		return !screenSharingIsActive;
	}

	#shouldHandleNotification(params: NotifyAddParams, extraData: PullExtraData): boolean
	{
		if (this.#isExpired(extraData) || !this.#checkLastNotificationId(params.id))
		{
			return false;
		}

		if (
			params.onlyFlash === true
			|| this.#isUserDnd()
			|| this.#desktopWillShowNotification()
			|| CallManager.getInstance().hasCurrentCall()
		)
		{
			return false;
		}

		if (document.hasFocus())
		{
			const areNotificationsOpen = this.store.getters['application/areNotificationsOpen'];
			if (areNotificationsOpen)
			{
				return false;
			}
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
		if (authorId > 0 && params.users[authorId].type !== UserType.extranet)
		{
			return true;
		}

		const counter = this.store.getters['counters/getSpecificLinesCounter'](params.chatId);

		return counter === 0;
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

	#isExpired(extraData: PullExtraData): boolean
	{
		if (extraData.server_time_ago > 10)
		{
			Logger.warn('NotifierPullHandler: received notification 10 seconds after it was actually sent, ignore');

			return true;
		}

		return false;
	}

	#checkLastNotificationId(id: number): boolean
	{
		if (id <= this.lastNotificationId)
		{
			Logger.warn('NotifierPullHandler: new message id is smaller than lastNotificationId');

			return false;
		}

		return true;
	}

	#isCurrentUserSender(params: MessageAddParams): boolean
	{
		return Core.getUserId() === params.message.senderId;
	}
}
