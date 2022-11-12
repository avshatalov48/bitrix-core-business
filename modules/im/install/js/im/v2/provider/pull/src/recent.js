import {Type, Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Logger} from 'im.v2.lib.logger';
import {ChatTypes, MessageStatus, EventType} from 'im.v2.const';

export class RecentPullHandler
{
	static create(params = {})
	{
		return new this(params);
	}

	getModuleId()
	{
		return 'im';
	}

	constructor(params)
	{
		this.controller = params.controller;
		this.store = params.store;
		this.application = params.application;
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

		Logger.warn('RecentPullHandler: handleMessageAdd', params);
		const newRecentItem = {
			id: params.dialogId,
			message: {
				id: params.message.id,
				text: params.message.textOriginal,
				date: params.message.date,
				senderId: params.message.senderId,
				withFile: !Type.isUndefined(params.message.params['FILE_ID']),
				withAttach: !Type.isUndefined(params.message.params['ATTACH']),
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
				this.store.dispatch('recent/set', newRecentItem);
			}, this.getWaitTimeForHumanBot(text));

			return;
		}

		this.store.dispatch('recent/set', newRecentItem);
	}

	handleMessageUpdate(params, extra, command)
	{
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem || recentItem.message.id !== params.id)
		{
			return false;
		}

		Logger.warn('RecentPullHandler: handleMessageUpdate', params, command);

		let text = params.textOriginal;
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
					senderId: params.senderId
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

	handleMessageLike(params)
	{
		Logger.warn('RecentPullHandler: handleMessageLike', params);
		const recentItem = this.store.getters['recent/get'](params.dialogId);
		if (!recentItem)
		{
			return false;
		}

		const currentDialogId = BX.MessengerProxy.getCurrentDialogId(); // TODO: change to Core variable
		if (currentDialogId === params.dialogId)
		{
			return false;
		}

		const currentUserId = this.store.state.application.common.userId;
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
		this.store.dispatch('recent/set', {
			id: params.user.id,
			invited: params.invited ?? false
		});
		this.store.dispatch('users/set', params.user);
		this.store.dispatch('dialogues/set', {
			dialogId: params.user.id,
			title: params.user.name,
			type: ChatTypes.user,
			avatar: params.user.avatar,
			color: params.user.color
		});
	}

	parseUserMention(text)
	{
		const hasUserMention = /\[user=(\d+)]\[\/user]/gi.exec(text);
		if (!hasUserMention)
		{
			return;
		}

		const userId = hasUserMention[1];
		console.warn('FOUND USER MENTION', userId);
		const user = this.store.getters['users/get'](userId);
		if (!user)
		{
			console.warn('NO SUCH USER, NEED REQUEST FOR -', userId);
			EventEmitter.emit(EventType.recent.requestUser, {userId});
		}
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