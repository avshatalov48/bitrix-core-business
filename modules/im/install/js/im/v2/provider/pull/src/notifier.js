import {Core} from 'im.v2.application.core';
import {DialogType, SoundType, UserStatus} from 'im.v2.const';
import {NotifierManager} from 'im.v2.lib.notifier';
import {DesktopManager} from 'im.v2.lib.desktop';
import {CallManager} from 'im.v2.lib.call';
import {SoundNotificationManager} from 'im.v2.lib.sound-notification';

import type {MessageAddParams} from './types/message';
import type {NotifyAddParams} from './types/notification';

export class NotifierPullHandler
{
	constructor()
	{
		this.store = Core.getStore();
	}

	getModuleId()
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

		if (this.#isChatOpened(params))
		{
			SoundNotificationManager.getInstance().playOnce(SoundType.newMessage2);

			return;
		}

		SoundNotificationManager.getInstance().playOnce(SoundType.newMessage1);

		const message = this.store.getters['messages/getById'](params.message.id);
		const dialog = this.store.getters['dialogues/get'](params.dialogId, true);
		const user = this.store.getters['users/get'](message.authorId);

		NotifierManager.getInstance().showMessage(message, dialog, user);
	}

	handleNotifyAdd(params: NotifyAddParams)
	{
		if (params.onlyFlash === true || this.#isUserDnd() || this.#isDesktopActive())
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

		NotifierManager.getInstance().showNotification(notification, user);
	}

	#shouldShowNotification(params: MessageAddParams): boolean
	{
		if (Core.getUserId() === params.message.senderId)
		{
			return false;
		}

		if (
			!params.notify
			|| params.message?.params?.NOTIFY === 'N'
			|| this.#isUserDnd()
			|| this.#isDesktopActive()
		)
		{
			return false;
		}

		const dialog = this.store.getters['dialogues/get'](params.dialogId, true);
		if (dialog.type === DialogType.lines)
		{
			return;
		}

		const isMuted = dialog.muteList.includes(Core.getUserId());
		if (isMuted)
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

	#isChatOpened(params): boolean
	{
		const isChatOpen = this.store.getters['application/isChatOpen'](params.dialogId);

		return !!(document.hasFocus() && isChatOpen);
	}

	#isUserDnd(): boolean
	{
		const currentUser = this.store.getters['users/get'](Core.getUserId(), true);

		return currentUser.status === UserStatus.dnd;
	}

	#isDesktopActive(): boolean
	{
		return DesktopManager.getInstance().isDesktopActive();
	}
}