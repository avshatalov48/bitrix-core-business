import {Store} from 'ui.vue3.vuex';

import {Messenger} from 'im.public';
import {Core} from 'im.v2.application.core';
import {UserManager} from 'im.v2.lib.user';
import {CallManager} from 'im.v2.lib.call';
import {Logger} from 'im.v2.lib.logger';

import type {
	ChatOwnerParams,
	ChatManagersParams,
	ChatUserAddParams,
	ChatUserLeaveParams,
	StartWritingParams,
	ChatUnreadParams,
	ChatMuteNotifyParams,
	ChatRenameParams,
	ChatAvatarParams
} from '../types/chat';
import type {RawUser} from '../types/common';

export class ChatPullHandler
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	handleChatOwner(params: ChatOwnerParams)
	{
		Logger.warn('ChatPullHandler: handleChatOwner', params);
		this.#store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				ownerId: params.userId,
			}
		});
	}

	handleChatManagers(params: ChatManagersParams)
	{
		Logger.warn('ChatPullHandler: handleChatManagers', params);
		this.#store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				managerList: params.list,
			}
		});
	}

	handleChatUserAdd(params: ChatUserAddParams)
	{
		Logger.warn('ChatPullHandler: handleChatUserAdd', params);
		this.#updateChatUsers(params);
	}

	handleChatUserLeave(params: ChatUserLeaveParams)
	{
		Logger.warn('ChatPullHandler: handleChatUserLeave', params);
		const currentUserIsKicked = params.userId === Core.getUserId();

		if (currentUserIsKicked)
		{
			this.#store.dispatch('dialogues/update', {
				dialogId: params.dialogId,
				fields: {
					inited: false
				}
			});
			this.#store.dispatch('messages/clearChatCollection', {chatId: params.chatId});
		}

		const chatIsOpened = this.#store.getters['application/isChatOpen'](params.dialogId);
		if (currentUserIsKicked && chatIsOpened)
		{
			Messenger.openChat();
		}

		const chatHasCall = CallManager.getInstance().getCurrentCallDialogId() === params.dialogId;
		if (currentUserIsKicked && chatHasCall)
		{
			CallManager.getInstance().leaveCurrentCall();
		}

		this.#updateChatUsers(params);
	}

	handleStartWriting(params: StartWritingParams)
	{
		Logger.warn('ChatPullHandler: handleStartWriting', params);
		const {dialogId, userId, userName} = params;
		this.#store.dispatch('dialogues/startWriting', {
			dialogId,
			userId,
			userName
		});
	}

	handleChatUnread(params: ChatUnreadParams)
	{
		Logger.warn('ChatPullHandler: handleChatUnread', params);
		let markedId = 0;
		if (params.active === true)
		{
			markedId = params.markedId;
		}
		this.#store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {markedId}
		});
	}

	handleChatMuteNotify(params: ChatMuteNotifyParams)
	{
		if (params.muted)
		{
			this.#store.dispatch('dialogues/mute', {
				dialogId: params.dialogId
			});

			return true;
		}

		this.#store.dispatch('dialogues/unmute', {
			dialogId: params.dialogId
		});
	}

	handleChatRename(params: ChatRenameParams)
	{
		const dialog = this.#store.getters['dialogues/getByChatId'](params.chatId);
		if (!dialog)
		{
			return false;
		}

		this.#store.dispatch('dialogues/update', {
			dialogId: dialog.dialogId,
			fields: {
				name: params.name
			}
		});
	}

	handleChatAvatar(params: ChatAvatarParams)
	{
		const dialog = this.#store.getters['dialogues/getByChatId'](params.chatId);
		if (!dialog)
		{
			return false;
		}

		this.#store.dispatch('dialogues/update', {
			dialogId: dialog.dialogId,
			fields: {
				avatar: params.avatar
			}
		});
	}

	handleReadAllChats()
	{
		Logger.warn('ChatPullHandler: handleReadAllChats');
		this.#store.dispatch('dialogues/clearCounters');
		this.#store.dispatch('recent/clearUnread');
	}

	#updateChatUsers(params: {
		users?: {[userId: string]: RawUser},
		dialogId: string,
		userCount: number
	})
	{
		if (params.users)
		{
			const userManager = new UserManager();
			userManager.setUsersToModel(params.users);
		}

		this.#store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {userCounter: params.userCount}
		});
	}
}