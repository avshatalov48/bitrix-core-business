import {PullClient} from 'pull.client';

import {Core} from 'im.old-chat-embedding.application.core';
import {UserManager} from 'im.old-chat-embedding.lib.user';
import {Logger} from 'im.old-chat-embedding.lib.logger';

export class BasePullHandler
{
	constructor()
	{
		this.store = Core.getStore();
		this.userManager = new UserManager();
	}

	getModuleId()
	{
		return 'im';
	}

	getSubscriptionType()
	{
		return PullClient.SubscriptionType.Server;
	}

	handleMessage(params, extra)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageChat(params, extra)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageAdd(params, extra)
	{
		Logger.warn('handleMessageAdd', params);

		if (params.lines)
		{
			return false;
		}

		if (params?.chat[params.chatId])
		{
			const chatToAdd = {...params.chat[params.chatId], dialogId: params.dialogId};
			this.store.dispatch('dialogues/set', chatToAdd);
		}

		//set users
		if (params.users)
		{
			this.userManager.setUsersToModel(Object.values(params.users));
		}

		//stop writing event
		this.store.dispatch('dialogues/stopWriting', {
			dialogId: params.dialogId,
			userId: params.message.senderId
		});

		// counters (TBD for own message)
		if (params.message.senderId !== Core.getUserId())
		{
			this.store.dispatch('dialogues/update', {
				dialogId: params.dialogId,
				fields: {
					counter: params.counter,
				}
			});
		}
	}

	handleMessageUpdate(params, extra, command)
	{
		this.execMessageUpdateOrDelete(params, extra, command);
	}

	handleMessageDelete(params, extra, command)
	{
		this.execMessageUpdateOrDelete(params, extra, command);
	}

	handleMessageDeleteComplete(params, extra)
	{
		this.execMessageUpdateOrDelete(params, extra, command);
	}

	execMessageUpdateOrDelete(params, extra, command)
	{
		this.store.dispatch('dialogues/stopWriting', {
			dialogId: params.dialogId,
			userId: params.senderId
		});
	}

	handleChatOwner(params, extra)
	{
		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				ownerId: params.userId,
			}
		});
	}

	handleChatManagers(params, extra)
	{
		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				managerList: params.list,
			}
		});
	}

	handleChatUpdateParams(params, extra)
	{
		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: params.params
		});
	}

	handleChatUserAdd(params, extra)
	{
		if (params.users)
		{
			this.userManager.setUsersToModel(params.users);
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {userCounter: params.userCount}
		});
	}

	handleChatUserLeave(params, extra)
	{
		this.handleChatUserAdd(params, extra);
	}

	handleStartWriting(params, extra)
	{
		const {dialogId, userId, userName} = params;
		this.store.dispatch('dialogues/startWriting', {
			dialogId,
			userId,
			userName
		});
	}

	handleReadMessage(params, extra)
	{
		Logger.warn('handleReadMessage', params);
		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				counter: params.counter,
			}
		});
	}

	handleReadMessageChat(params, extra)
	{
		this.handleReadMessage(params, extra);
	}

	handleUnreadMessage(params, extra)
	{
		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				counter: params.counter,
			}
		});
	}

	handleUnreadMessageChat(params, extra)
	{
		this.handleUnreadMessage(params, extra);
	}

	handleUnreadMessageOpponent(params, extra)
	{
		this.execUnreadMessageOpponent(params, extra);
	}

	handleUnreadMessageChatOpponent(params, extra)
	{
		this.execUnreadMessageOpponent(params, extra);
	}

	execUnreadMessageOpponent(params, extra)
	{
		this.store.dispatch('dialogues/removeFromReadList', {
			dialogId: params.dialogId,
			userId: params.userId
		});
	}

	handleReadAllChats()
	{
		Logger.warn('BasePullHandler: handleReadAllChats');
		this.store.dispatch('dialogues/clearCounters');
		this.store.dispatch('recent/clearUnread');
	}

	handleChatMuteNotify(params)
	{
		if (params.muted)
		{
			this.store.dispatch('dialogues/mute', {
				dialogId: params.dialogId
			});

			return true;
		}

		this.store.dispatch('dialogues/unmute', {
			dialogId: params.dialogId
		});
	}

	handleUserInvite(params)
	{
		if (!params.invited)
		{
			this.store.dispatch('users/update', {
				id: params.userId,
				fields: params.user
			});
		}
	}

	handleChatRename(params)
	{
		const dialog = this.store.getters['dialogues/getByChatId'](params.chatId);
		if (!dialog)
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: dialog.dialogId,
			fields: {
				name: params.name
			}
		});
	}

	handleChatAvatar(params)
	{
		const dialog = this.store.getters['dialogues/getByChatId'](params.chatId);
		if (!dialog)
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: dialog.dialogId,
			fields: {
				avatar: params.avatar
			}
		});
	}
}