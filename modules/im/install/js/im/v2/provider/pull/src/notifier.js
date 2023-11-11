import { Core } from 'im.v2.application.core';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { SoundType, UserStatus } from 'im.v2.const';
import { NotifierManager } from 'im.v2.lib.notifier';
import { DesktopManager } from 'im.v2.lib.desktop';
import { CallManager } from 'im.v2.lib.call';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';

import type { MessageAddParams } from './types/message';
import type { NotifyAddParams } from './types/notification';

export class NotifierPullHandler
{
	constructor()
	{
		this.store = Core.getStore();
	}

	getModuleId(): string
	{
		return 'im';
	}

	handleMessage(params)
	{
		this.handleMessageAdd(params);
	}

	handleMessageChat(params)
	{
		this.handleMessageAdd(params);
	}

	handleMessageAdd(params: MessageAddParams)
	{
		if (!this.#shouldShowNotification(params))
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
		const dialog = this.store.getters['dialogues/get'](params.dialogId, true);
		const user = this.store.getters['users/get'](message.authorId);

		NotifierManager.getInstance().showMessage({
			message,
			dialog,
			user,
			lines: Boolean(params.lines),
		});
	}

	handleNotifyAdd(params: NotifyAddParams)
	{
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
	}

	#shouldShowNotification(params: MessageAddParams): boolean
	{
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

		const counter = this.store.getters['recent/getSpecificLinesCounter'](params.chatId);

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

		const dialog = this.store.getters['dialogues/get'](params.dialogId, true);
		const isMuted = dialog.muteList.includes(Core.getUserId());

		return !this.#isUserDnd() && !isMuted;
	}

	#isUserDnd(): boolean
	{
		const currentUser = this.store.getters['users/get'](Core.getUserId(), true);

		return currentUser.status === UserStatus.dnd;
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
}
