import {Type, Loc} from 'main.core';

import {Core} from 'im.old-chat-embedding.application.core';
import {Logger} from 'im.old-chat-embedding.lib.logger';
import {UserManager} from 'im.old-chat-embedding.lib.user';
import {MessageStatus} from 'im.old-chat-embedding.const';

export class RecentPullHandler
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

	handleMessage(params)
	{
		this.handleMessageAdd(params);
	}

	handleMessageChat(params)
	{
		this.handleMessageAdd(params);
	}

	handleMessageAdd(params)
	{
		if (params.lines)
		{
			return false;
		}

		const currentUserId = this.store.state.application.common.userId;
		if (currentUserId && params.userInChat[params.chatId] && !params.userInChat[params.chatId].includes(currentUserId))
		{
			return false;
		}

		let attach = false;
		if (Type.isArray(params.message.params['ATTACH']))
		{
			attach = params.message.params['ATTACH'];
		}

		let file = false;
		if (Type.isArray(params.message.params['FILE_ID']))
		{
			file = params.files[params.message.params['FILE_ID'][0]];
		}

		Logger.warn('RecentPullHandler: handleMessageAdd', params);

		const newRecentItem = {
			id: params.dialogId,
			message: {
				id: params.message.id,
				text: params.message.text,
				date: params.message.date,
				senderId: params.message.senderId,
				attach,
				file,
			}
		};

		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (recentItem)
		{
			newRecentItem.options = {
				birthdayPlaceholder: false
			};

			this.store.dispatch('recent/like', {
				id: params.dialogId,
				liked: false
			});
		}

		const {senderId} = params.message;
		const usersModel = this.store.state.users;
		if (usersModel?.botList[senderId] && usersModel.botList[senderId].type === 'human')
		{
			const {text} = params.message;
			setTimeout(() => {
				this.store.dispatch('recent/setRecent', newRecentItem);
			}, this.getWaitTimeForHumanBot(text));

			return;
		}

		this.store.dispatch('recent/setRecent', newRecentItem);
	}

	handleMessageUpdate(params, extra, command)
	{
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem || recentItem.message.id !== params.id)
		{
			return false;
		}

		Logger.warn('RecentPullHandler: handleMessageUpdate', params, command);

		let text = params.text;
		if (command === 'messageDelete')
		{
			text = Loc.getMessage('IM_EMBED_PULL_RECENT_MESSAGE_DELETED');
		}

		this.store.dispatch('recent/update', {
			id: params.dialogId,
			fields: {
				message: {
					id: params.id,
					text: text,
					date: recentItem.message.date,
					status: recentItem.message.status,
					senderId: params.senderId,
					params: {
						withFile: false,
						withAttach: false,
					}
				}
			}
		});
	}

	handleMessageDelete(params, extra, command)
	{
		this.handleMessageUpdate(params, extra, command);
	}

	handleReadMessageOpponent(params)
	{
		Logger.warn('RecentPullHandler: handleReadMessageOpponent', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		const lastReadMessage = Number.parseInt(params.lastId, 10);
		if (!recentItem || recentItem.message.id !== lastReadMessage)
		{
			return false;
		}

		this.store.dispatch('recent/update', {
			id: params.dialogId,
			fields: {
				message: {...recentItem.message, status: MessageStatus.delivered}
			}
		});
	}

	handleReadMessageChatOpponent(params)
	{
		this.handleReadMessageOpponent(params);
	}

	handleUnreadMessageOpponent(params)
	{
		Logger.warn('RecentPullHandler: handleUnreadMessageOpponent', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/update', {
			id: params.dialogId,
			fields: {
				message: {...recentItem.message, status: MessageStatus.received}
			}
		});
	}

	handleUnreadMessageChatOpponent(params)
	{
		Logger.warn('RecentPullHandler: handleUnreadMessageChatOpponent', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/update', {
			id: params.dialogId,
			fields: {
				message: {...recentItem.message, status: params.chatMessageStatus}
			}
		});
	}

	handleMessageLike(params)
	{
		Logger.warn('RecentPullHandler: handleMessageLike', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		if (Type.isUndefined(BX.MessengerProxy))
		{
			return;
		}

		const currentDialogId = BX.MessengerProxy.getCurrentDialogId();
		if (currentDialogId === params.dialogId)
		{
			return false;
		}

		const currentUserId = Core.getUserId();
		const isOwnLike = currentUserId === params.senderId;
		const isOwnLastMessage = recentItem.message.senderId === currentUserId;
		if (isOwnLike || !isOwnLastMessage)
		{
			return false;
		}

		this.store.dispatch('recent/like', {
			id: params.dialogId,
			messageId: params.id,
			liked: params.set
		});
	}

	handleChatPin(params)
	{
		Logger.warn('RecentPullHandler: handleChatPin', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/pin', {
			id: params.dialogId,
			action: params.active
		});
	}

	handleChatUnread(params)
	{
		Logger.warn('RecentPullHandler: handleChatUnread', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/unread', {
			id: params.dialogId,
			action: params.active
		});
	}

	handleChatHide(params)
	{
		Logger.warn('RecentPullHandler: handleChatHide', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/delete', {
			id: params.dialogId
		});
	}

	handleChatUserLeave(params)
	{
		Logger.warn('RecentPullHandler: handleChatUserLeave', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		const currentUserId = this.store.state.application.common.userId;
		if (currentUserId !== params.userId)
		{
			return false;
		}

		this.store.dispatch('recent/delete', {
			id: params.dialogId
		});
	}

	handleUserInvite(params)
	{
		Logger.warn('RecentPullHandler: handleUserInvite', params);
		this.store.dispatch('recent/setRecent', {
			id: params.user.id,
			invited: params.invited ?? false
		});
		this.userManager.setUsersToModel([params.user]);
	}

	getWaitTimeForHumanBot(text)
	{
		const INITIAL_WAIT = 1000;
		const WAIT_PER_WORD = 300;
		const WAIT_LIMIT = 5000;

		let waitTime = (text.split(' ').length * WAIT_PER_WORD) + INITIAL_WAIT;
		if (waitTime > WAIT_LIMIT)
		{
			waitTime = WAIT_LIMIT;
		}

		return waitTime;
	}
}