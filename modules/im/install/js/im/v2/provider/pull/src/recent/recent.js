import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { ImModelMessage } from 'im.v2.model';

import { NewMessageManager } from './classes/new-message-manager';
import { RecentUpdateManager } from './classes/recent-update-manager';

import type { PullExtraParams } from '../types/common';
import type {
	MessageAddParams,
	AddReactionParams,
	MessageDeleteCompleteParams,
} from '../types/message';
import type { ChatUnreadParams } from '../types/chat';
import type { UserInviteParams } from '../types/user';
import type { RecentUpdateParams, UserShowInRecentParams } from '../types/recent';
import type { ImModelRecentItem } from 'im.v2.model';

// noinspection JSUnusedGlobalSymbols
export class RecentPullHandler
{
	getModuleId(): string
	{
		return 'im';
	}

	handleMessage(params, extra)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageChat(params, extra)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageAdd(params: MessageAddParams, extra: PullExtraParams)
	{
		const manager = new NewMessageManager(params, extra);
		if (manager.needToSkipMessageEvent(params))
		{
			return;
		}

		Logger.warn('RecentPullHandler: handleMessageAdd', params);
		const newRecentItem = {
			id: params.dialogId,
			chatId: params.chatId,
			messageId: params.message.id,
		};

		const recentItem: ?ImModelRecentItem = Core.getStore().getters['recent/get'](params.dialogId);
		if (recentItem)
		{
			newRecentItem.isFakeElement = false;
			newRecentItem.isBirthdayPlaceholder = false;
			newRecentItem.liked = false;
		}

		const addActions = manager.getAddActions();
		addActions.forEach((actionName) => {
			Core.getStore().dispatch(actionName, newRecentItem);
		});
	}

	handleMessageDeleteComplete(params: MessageDeleteCompleteParams)
	{
		const lastMessageWasDeleted = Boolean(params.newLastMessage);
		if (lastMessageWasDeleted)
		{
			this.#updateRecentForMessageDelete(params.dialogId, params.newLastMessage.id);
		}
	}

	handleChatUnread(params: ChatUnreadParams)
	{
		Logger.warn('RecentPullHandler: handleChatUnread', params);
		Core.getStore().dispatch('recent/unread', {
			id: params.dialogId,
			action: params.active,
		});
	}

	handleAddReaction(params: AddReactionParams)
	{
		Logger.warn('RecentPullHandler: handleAddReaction', params);
		const recentItem: ?ImModelRecentItem = Core.getStore().getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return;
		}

		const chatIsOpened = Core.getStore().getters['application/isChatOpen'](params.dialogId);
		if (chatIsOpened)
		{
			return;
		}

		const message: ?ImModelMessage = Core.getStore().getters['recent/getMessage'](params.dialogId);
		const isOwnLike = Core.getUserId() === params.userId;
		const isOwnLastMessage = Core.getUserId() === message.authorId;
		if (isOwnLike || !isOwnLastMessage)
		{
			return;
		}

		Core.getStore().dispatch('recent/like', {
			id: params.dialogId,
			messageId: params.actualReactions.reaction.messageId,
			liked: true,
		});
	}

	handleChatPin(params)
	{
		Logger.warn('RecentPullHandler: handleChatPin', params);
		const recentItem: ?ImModelRecentItem = Core.getStore().getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return;
		}

		Core.getStore().dispatch('recent/pin', {
			id: params.dialogId,
			action: params.active,
		});
	}

	handleChatHide(params)
	{
		Logger.warn('RecentPullHandler: handleChatHide', params);
		const recentItem: ?ImModelRecentItem = Core.getStore().getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return;
		}

		Core.getStore().dispatch('recent/delete', {
			id: params.dialogId,
		});
	}

	handleChatUserLeave(params)
	{
		Logger.warn('RecentPullHandler: handleChatUserLeave', params);
		const recentItem: ?ImModelRecentItem = Core.getStore().getters['recent/get'](params.dialogId);
		if (!recentItem || params.userId !== Core.getUserId())
		{
			return;
		}

		Core.getStore().dispatch('recent/delete', {
			id: params.dialogId,
		});
	}

	handleUserInvite(params: UserInviteParams)
	{
		Logger.warn('RecentPullHandler: handleUserInvite', params);

		const messageId = Utils.text.getUuidV4();
		Core.getStore().dispatch('messages/store', {
			id: messageId,
			date: params.date,
		});

		Core.getStore().dispatch('recent/setRecent', {
			id: params.user.id,
			invited: params.invited ?? false,
			isFakeElement: true,
			messageId,
		});
	}

	handleUserShowInRecent(params: UserShowInRecentParams)
	{
		Logger.warn('RecentPullHandler: handleUserShowInRecent', params);
		const { items } = params;

		items.forEach((item) => {
			const messageId = Utils.text.getUuidV4();
			Core.getStore().dispatch('messages/store', {
				id: messageId,
				date: item.date,
			});

			Core.getStore().dispatch('recent/setRecent', {
				id: item.user.id,
				messageId,
			});
		});
	}

	handleRecentUpdate(params: RecentUpdateParams)
	{
		Logger.warn('RecentPullHandler: handleRecentUpdate', params);
		const manager = new RecentUpdateManager(params);
		manager.setLastMessageInfo();

		const newRecentItem = {
			id: manager.getDialogId(),
			messageId: manager.getLastMessageId(),
			lastActivityDate: params.lastActivityDate,
		};

		Core.getStore().dispatch('recent/setRecent', newRecentItem);
	}

	#updateRecentForMessageDelete(dialogId: string, newLastMessageId: number): void
	{
		if (!newLastMessageId)
		{
			Core.getStore().dispatch('recent/delete', { id: dialogId });

			return;
		}

		Core.getStore().dispatch('recent/update', {
			id: dialogId,
			fields: { messageId: newLastMessageId },
		});
	}
}
