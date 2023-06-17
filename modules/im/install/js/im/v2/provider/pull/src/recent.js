import {Type, Loc} from 'main.core';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {UserManager} from 'im.v2.lib.user';
import {MessageStatus} from 'im.v2.const';

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

		const currentUserId = Core.getUserId();
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
				sending: false,
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
			text = Loc.getMessage('IM_PULL_RECENT_MESSAGE_DELETED');
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

	/* region Counters handling */
	handleReadMessage(params)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleReadMessageChat(params)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleUnreadMessage(params)
	{
		this.updateUnloadedChatCounter(params);
	}

	handleUnreadMessageChat(params)
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
			unread: params.active
		});

		this.store.dispatch('recent/unread', {
			id: params.dialogId,
			action: params.active
		});
	}
	/* endregion Counters handling */

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

	handleAddReaction(params)
	{
		Logger.warn('RecentPullHandler: handleAddReaction', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		const chatIsOpened = this.store.getters['application/isChatOpen'](params.dialogId);
		if (chatIsOpened)
		{
			return false;
		}

		const isOwnLike = Core.getUserId() === params.senderId;
		const isOwnLastMessage = Core.getUserId() === recentItem.message.senderId;
		if (isOwnLike || !isOwnLastMessage)
		{
			return false;
		}

		this.store.dispatch('recent/like', {
			id: params.dialogId,
			messageId: params.id,
			liked: true
		});
	}

	handleDeleteReaction(params)
	{
		// Logger.warn('RecentPullHandler: handleDeleteReaction', params);
		// const recentItem = this.store.getters['recent/get'](params.dialogId);
		// if (!recentItem)
		// {
		// 	return false;
		// }
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

		if (params.userId !== Core.getUserId())
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

	updateUnloadedChatCounter(params: {
		dialogId: string,
		chatId: number,
		counter: number,
		muted: boolean,
		unread: boolean
	})
	{
		const {dialogId, chatId, counter, muted, unread} = params;
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (recentItem)
		{
			return;
		}
		Logger.warn('RecentPullHandler: updateUnloadedChatCounter:', {dialogId, chatId, counter, muted, unread});

		let newCounter;
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
		this.store.dispatch('recent/setUnloadedChatCounters', {[chatId]: newCounter});
	}
}