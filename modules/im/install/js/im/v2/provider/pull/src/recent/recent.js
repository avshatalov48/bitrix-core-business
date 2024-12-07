import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { ChatType } from 'im.v2.const';
import { ImModelMessage } from 'im.v2.model';

import { NewMessageManager } from './classes/new-message-manager';
import { RecentUpdateManager } from './classes/recent-update-manager';

import type { PullExtraParams } from '../types/common';
import type {
	MessageAddParams,
	AddReactionParams,
	MessageDeleteCompleteParams,
	ReadMessageParams,
	UnreadMessageParams,
} from '../types/message';
import type { UserInviteParams } from '../types/user';
import type { RecentUpdateParams } from '../types/recent';
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
		if (manager.isCommentChat())
		{
			this.#updateCommentCounter({
				channelChatId: manager.getParentChatId(),
				commentChatId: manager.getChatId(),
				commentCounter: params.counter,
			});
		}

		if (manager.needToSkipMessageEvent(params))
		{
			return;
		}

		Logger.warn('RecentPullHandler: handleMessageAdd', params);
		const newRecentItem = {
			id: params.dialogId,
			messageId: params.message.id,
		};

		const recentItem: ?ImModelRecentItem = Core.getStore().getters['recent/get'](params.dialogId);
		if (recentItem)
		{
			newRecentItem.isFakeElement = false;
			newRecentItem.isBirthdayPlaceholder = false;
			newRecentItem.liked = false;
		}

		const addMethod = manager.getActionName();
		Core.getStore().dispatch(addMethod, newRecentItem);
	}

	handleMessageDeleteComplete(params: MessageDeleteCompleteParams)
	{
		const lastMessageWasDeleted = Boolean(params.newLastMessage);
		if (lastMessageWasDeleted)
		{
			this.updateRecentForMessageDelete(params.dialogId, params.newLastMessage.id);
		}

		this.#updateUnloadedChatCounter(params);
	}

	/* region Counters handling */
	handleReadMessage(params: ReadMessageParams)
	{
		this.#updateUnloadedChatCounter(params);
	}

	handleReadMessageChat(params: ReadMessageParams)
	{
		if (params.type === ChatType.comment)
		{
			this.#updateCommentCounter({
				channelChatId: params.parentChatId,
				commentChatId: params.chatId,
				commentCounter: params.counter,
			});

			return;
		}

		this.#updateUnloadedChatCounter(params);
	}

	handleUnreadMessage(params: UnreadMessageParams)
	{
		this.#updateUnloadedChatCounter(params);
	}

	handleUnreadMessageChat(params: UnreadMessageParams)
	{
		this.#updateUnloadedChatCounter(params);
	}

	handleChatMuteNotify(params)
	{
		this.#updateUnloadedChatCounter(params);
	}

	handleChatUnread(params)
	{
		Logger.warn('RecentPullHandler: handleChatUnread', params);
		this.#updateUnloadedChatCounter({
			dialogId: params.dialogId,
			chatId: params.chatId,
			counter: params.counter,
			muted: params.muted,
			unread: params.active,
		});

		Core.getStore().dispatch('recent/unread', {
			id: params.dialogId,
			action: params.active,
		});
	}
	/* endregion Counters handling */

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

	#updateUnloadedChatCounter(params: {
		dialogId: string,
		chatId: number,
		counter: number,
		muted: boolean,
		unread: boolean,
		lines: boolean
	})
	{
		const { dialogId, chatId, counter, muted, unread, lines = false } = params;
		if (lines)
		{
			return;
		}

		const recentItem: ?ImModelRecentItem = Core.getStore().getters['recent/get'](dialogId);
		if (recentItem)
		{
			return;
		}
		Logger.warn('RecentPullHandler: updateUnloadedChatCounter:', { dialogId, chatId, counter, muted, unread });

		let newCounter = 0;
		if (muted)
		{
			newCounter = 0;
		}
		else if (unread && counter === 0)
		{
			newCounter = 1;
		}
		else if (unread && counter > 0)
		{
			newCounter = counter;
		}
		else if (!unread)
		{
			newCounter = counter;
		}

		Core.getStore().dispatch('counters/setUnloadedChatCounters', { [chatId]: newCounter });
	}

	#updateCommentCounter(payload: { channelChatId: number, commentChatId: number, commentCounter: number })
	{
		const { channelChatId, commentChatId, commentCounter } = payload;
		if (Type.isUndefined(commentCounter))
		{
			return;
		}

		const counters = {
			[channelChatId]: {
				[commentChatId]: commentCounter,
			},
		};

		Core.getStore().dispatch('counters/setCommentCounters', counters);
	}

	updateRecentForMessageDelete(dialogId: string, newLastMessageId: number): void
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
