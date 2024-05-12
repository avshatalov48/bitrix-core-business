import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { ChatType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { ImModelMessage } from 'im.v2.model';

import type { JsonObject } from 'main.core';
import type { MessageAddParams, AddReactionParams, MessageDeleteCompleteParams, ReadMessageParams, UnreadMessageParams } from './types/message';
import type { UserInviteParams } from './types/user';
import type { ImModelRecentItem } from 'im.v2.model';

const AddMethodByChatType = {
	[ChatType.copilot]: 'recent/setCopilot',
	default: 'recent/setRecent',
};

// noinspection JSUnusedGlobalSymbols
export class RecentPullHandler
{
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
		if (params.lines)
		{
			return;
		}

		const chatUsers = params.userInChat[params.chatId];
		if (chatUsers && !chatUsers.includes(Core.getUserId()))
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

		this.setRecentItem(params, newRecentItem);
	}

	handleMessageDeleteComplete(params: MessageDeleteCompleteParams)
	{
		const lastMessageWasDeleted = Boolean(params.newLastMessage);
		if (lastMessageWasDeleted)
		{
			this.updateRecentForMessageDelete(params.dialogId, params.newLastMessage.id);
		}

		this.updateUnloadedChatCounter(params);
	}

	/* region Counters handling */
	handleReadMessage(params: ReadMessageParams)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleReadMessageChat(params: ReadMessageParams)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleUnreadMessage(params: UnreadMessageParams)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleUnreadMessageChat(params: UnreadMessageParams)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleChatMuteNotify(params)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleChatUnread(params)
	{
		Logger.warn('RecentPullHandler: handleChatUnread', params);
		this.updateUnloadedChatCounter({
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

	updateUnloadedChatCounter(params: {
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

	setRecentItem(params: MessageAddParams, newRecentItem: JsonObject): void
	{
		const newMessageChatType = params.chat[params.chatId]?.type;
		const addMethod = AddMethodByChatType[newMessageChatType] ?? AddMethodByChatType.default;

		Core.getStore().dispatch(addMethod, newRecentItem);
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
