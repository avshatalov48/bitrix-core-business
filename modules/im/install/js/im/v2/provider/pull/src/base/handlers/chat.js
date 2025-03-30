import { Store } from 'ui.vue3.vuex';
import { Loc } from 'main.core';

import { LayoutManager } from 'im.v2.lib.layout';
import { Messenger } from 'im.public';
import { ChatType, UserRole } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';
import { CopilotManager } from 'im.v2.lib.copilot';
import { CallManager } from 'im.v2.lib.call';
import { ChannelManager } from 'im.v2.lib.channel';
import { WritingManager } from 'im.v2.lib.writing';
import { Logger } from 'im.v2.lib.logger';
import { getChatRoleForUser } from 'im.v2.lib.role-manager';
import { Analytics } from 'im.v2.lib.analytics';

import type {
	ChatOwnerParams,
	ChatManagersParams,
	ChatUserAddParams,
	ChatUserLeaveParams,
	StartWritingParams,
	ChatUnreadParams,
	ChatMuteNotifyParams,
	ChatRenameParams,
	ChatAvatarParams,
	ChatConvertParams,
	ChatDeleteParams,
} from '../../types/chat';
import type { RawUser, RawChat } from '../../types/common';
import type { ImModelChat } from 'im.v2.model';

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
		this.#store.dispatch('chats/update', {
			dialogId: params.dialogId,
			fields: {
				ownerId: params.userId,
			},
		});
	}

	handleChatManagers(params: ChatManagersParams)
	{
		Logger.warn('ChatPullHandler: handleChatManagers', params);
		this.#store.dispatch('chats/update', {
			dialogId: params.dialogId,
			fields: {
				managerList: params.list,
			},
		});

		const chat: ImModelChat = this.#store.getters['chats/get'](params.dialogId);
		if (!chat)
		{
			return;
		}

		const userInManagerList = params.list.includes(Core.getUserId());
		if (chat.role === UserRole.member && userInManagerList)
		{
			this.#store.dispatch('chats/update', {
				dialogId: params.dialogId,
				fields: {
					role: UserRole.manager,
				},
			});
		}

		if (chat.role === UserRole.manager && !userInManagerList)
		{
			this.#store.dispatch('chats/update', {
				dialogId: params.dialogId,
				fields: {
					role: UserRole.member,
				},
			});
		}
	}

	handleChatUserAdd(params: ChatUserAddParams)
	{
		Logger.warn('ChatPullHandler: handleChatUserAdd', params);
		const currentUserId = Core.getUserId();
		if (params.newUsers.includes(currentUserId))
		{
			this.#store.dispatch('chats/update', {
				dialogId: params.dialogId,
				fields: { role: UserRole.member },
			});
		}
		this.#updateChatUsers(params);
	}

	handleChatUserLeave(params: ChatUserLeaveParams)
	{
		Logger.warn('ChatPullHandler: handleChatUserLeave', params);
		const currentUserIsKicked = params.userId === Core.getUserId();

		if (currentUserIsKicked)
		{
			this.#store.dispatch('chats/update', {
				dialogId: params.dialogId,
				fields: { inited: false },
			});
			this.#store.dispatch('messages/clearChatCollection', { chatId: params.chatId });
		}

		const isChannel = ChannelManager.isChannel(params.dialogId);
		if (isChannel)
		{
			void this.#store.dispatch('counters/deleteForChannel', {
				channelChatId: params.chatId,
			});
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

		if (currentUserIsKicked)
		{
			CallManager.getInstance().deleteRecentCall(params.dialogId);
		}

		this.#updateChatUsers(params);
	}

	handleStartWriting(params: StartWritingParams)
	{
		if (params.userId === Core.getUserId())
		{
			return;
		}
		Logger.warn('ChatPullHandler: handleStartWriting', params);
		const { dialogId, userId, userName } = params;
		WritingManager.getInstance().startWriting({ dialogId, userId, userName });
		this.#store.dispatch('users/update', {
			id: userId,
			fields: {
				lastActivityDate: new Date(),
			},
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
		this.#store.dispatch('chats/update', {
			dialogId: params.dialogId,
			fields: { markedId },
		});
	}

	handleChatMuteNotify(params: ChatMuteNotifyParams)
	{
		if (params.muted)
		{
			this.#store.dispatch('chats/mute', {
				dialogId: params.dialogId,
			});

			return;
		}

		this.#store.dispatch('chats/unmute', {
			dialogId: params.dialogId,
		});
	}

	handleChatRename(params: ChatRenameParams)
	{
		const dialog = this.#store.getters['chats/getByChatId'](params.chatId);
		if (!dialog)
		{
			return;
		}

		this.#store.dispatch('chats/update', {
			dialogId: dialog.dialogId,
			fields: {
				name: params.name,
			},
		});
	}

	handleChatAvatar(params: ChatAvatarParams)
	{
		const dialog = this.#store.getters['chats/getByChatId'](params.chatId);
		if (!dialog)
		{
			return;
		}

		this.#store.dispatch('chats/update', {
			dialogId: dialog.dialogId,
			fields: {
				avatar: params.avatar,
			},
		});
	}

	handleReadAllChats()
	{
		Logger.warn('ChatPullHandler: handleReadAllChats');
		this.#store.dispatch('chats/clearCounters');
		this.#store.dispatch('recent/clearUnread');
	}

	handleChatConvert(params: ChatConvertParams)
	{
		Logger.warn('ChatPullHandler: handleChatConvert', params);
		const { dialogId, newType, newPermissions } = params;
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				type: newType,
				permissions: newPermissions,
			},
		});
	}

	handleChatCopilotRoleUpdate(params)
	{
		if (!params.copilotRole)
		{
			return;
		}

		const copilotManager = new CopilotManager();
		void copilotManager.handleRoleUpdate(params.copilotRole);
	}

	handleChatUpdate(params: {chat: RawChat})
	{
		void this.#store.dispatch('chats/update', {
			dialogId: params.chat.dialogId,
			fields: {
				role: getChatRoleForUser(params.chat),
				...params.chat,
			},
		});
	}

	handleChatDelete(params: ChatDeleteParams)
	{
		Logger.warn('ChatPullHandler: handleChatDelete', params);

		const currentUserId = Core.getUserId();
		if (params.userId === currentUserId)
		{
			return;
		}

		void this.#store.dispatch('chats/update', {
			dialogId: params.dialogId,
			fields: { inited: false },
		});
		void this.#store.dispatch('recent/delete', { id: params.dialogId });

		const isCommentChat = params.type === ChatType.comment;
		if (isCommentChat)
		{
			void this.#store.dispatch('counters/deleteForChannel', {
				channelChatId: params.parentChatId,
				commentChatId: params.chatId,
			});
		}

		const isChannel = ChannelManager.isChannel(params.dialogId);
		if (isChannel)
		{
			void this.#store.dispatch('counters/deleteForChannel', {
				channelChatId: params.chatId,
			});
		}

		void this.#store.dispatch('messages/clearChatCollection', { chatId: params.chatId });

		const chatIsOpened = this.#store.getters['application/isChatOpen'](params.dialogId);
		if (chatIsOpened)
		{
			Analytics.getInstance().chatDelete.onChatDeletedNotification(params.dialogId);
			this.#showNotification(Loc.getMessage('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
			void LayoutManager.getInstance().clearCurrentLayoutEntityId();
			void LayoutManager.getInstance().deleteLastOpenedElementById(params.dialogId);
		}

		const chatHasCall = CallManager.getInstance().getCurrentCallDialogId() === params.dialogId;
		if (chatHasCall)
		{
			CallManager.getInstance().leaveCurrentCall();
		}
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
			userManager.setUsersToModel(Object.values(params.users));
		}

		this.#store.dispatch('chats/update', {
			dialogId: params.dialogId,
			fields: { userCounter: params.userCount },
		});
	}

	#showNotification(text: string): void
	{
		BX.UI.Notification.Center.notify({ content: text });
	}
}
