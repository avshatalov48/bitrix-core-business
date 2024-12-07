import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';
import { SidebarDetailBlock } from 'im.v2.const';
import { ChatUnreadParams } from './types/chat';

import type {
	AddMultidialogParams,
	ChangeMultidialogSessionsLimitParams,
	ChangeMultidialogStatusParams,
} from './types/multidialog';
import { MessageChatParams, MessageParams, ReadMessageChatParams, ReadMessageParams } from './types/message';
import type { ChatUserAddParams, ChatUserLeaveParams } from './types/chat';
import type { ImModelSidebarMultidialogItem } from 'im.v2.model';

export class SidebarPullHandler
{
	constructor()
	{
		this.store = Core.getStore();
		this.userManager = new UserManager();
	}

	getModuleId(): string
	{
		return 'im';
	}

	// region members
	handleChatUserAdd(params: ChatUserAddParams)
	{
		if (this.getMembersCountFromStore(params.chatId) === 0)
		{
			return;
		}

		void this.userManager.setUsersToModel(Object.values(params.users));

		void this.store.dispatch('sidebar/members/set', {
			chatId: params.chatId,
			users: params.newUsers,
		});
	}

	handleChatUserLeave(params: ChatUserLeaveParams)
	{
		if (this.getMembersCountFromStore(params.chatId) === 0)
		{
			return;
		}

		void this.store.dispatch('sidebar/members/delete', {
			chatId: params.chatId,
			userId: params.userId,
		});
	}
	// endregion

	// region task
	handleTaskAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		void this.userManager.setUsersToModel(params.users);

		void this.store.dispatch('sidebar/tasks/set', {
			chatId: params.link.chatId,
			tasks: [params.link],
		});
	}

	handleTaskUpdate(params, extra)
	{
		this.handleTaskAdd(params, extra);
	}

	handleTaskDelete(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		void this.store.dispatch('sidebar/tasks/delete', {
			chatId: params.chatId,
			id: params.linkId,
		});
	}
	// endregion

	// region meetings
	handleCalendarAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		void this.userManager.setUsersToModel(params.users);

		void this.store.dispatch('sidebar/meetings/set', {
			chatId: params.link.chatId,
			meetings: [params.link],
		});
	}

	handleCalendarUpdate(params, extra)
	{
		this.handleCalendarAdd(params, extra);
	}

	handleCalendarDelete(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		void this.store.dispatch('sidebar/meetings/delete', {
			chatId: params.chatId,
			id: params.linkId,
		});
	}
	// endregion

	// region links
	handleUrlAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		void this.userManager.setUsersToModel(params.users);

		void this.store.dispatch('sidebar/links/set', {
			chatId: params.link.chatId,
			links: [params.link],
		});

		const counter = this.store.getters['sidebar/links/getCounter'](params.link.chatId);
		void this.store.dispatch('sidebar/links/setCounter', {
			chatId: params.link.chatId,
			counter: counter + 1,
		});
	}

	handleUrlDelete(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		void this.store.dispatch('sidebar/links/delete', {
			chatId: params.chatId,
			id: params.linkId,
		});
	}
	// endregion

	// region favorite
	handleMessageFavoriteAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		void this.userManager.setUsersToModel(params.users);
		void this.store.dispatch('files/set', params.files);
		void this.store.dispatch('messages/store', [params.link.message]);

		void this.store.dispatch('sidebar/favorites/set', {
			chatId: params.link.chatId,
			favorites: [params.link],
		});

		const counter = this.store.getters['sidebar/favorites/getCounter'](params.link.chatId);
		void this.store.dispatch('sidebar/favorites/setCounter', {
			chatId: params.link.chatId,
			counter: counter + 1,
		});
	}

	handleMessageFavoriteDelete(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		void this.store.dispatch('sidebar/favorites/delete', {
			chatId: params.chatId,
			id: params.linkId,
		});
	}
	// endregion

	// region files
	handleFileAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		void this.userManager.setUsersToModel(params.users);
		void this.store.dispatch('files/set', params.files);

		const subType = params.link.subType ?? SidebarDetailBlock.fileUnsorted;

		void this.store.dispatch('sidebar/files/set', {
			chatId: params.link.chatId,
			files: [params.link],
			subType,
		});
	}

	handleFileDelete(params)
	{
		const chatId = Type.isNumber(params.chatId) ? params.chatId : Number.parseInt(params.chatId, 10);
		if (!this.isSidebarInited(chatId))
		{
			return;
		}

		const sidebarFileId = params.linkId ?? params.fileId;
		void this.store.dispatch('sidebar/files/delete', {
			chatId,
			id: sidebarFileId,
		});
	}
	// endregion

	// region support24

	handleChangeMultidialogSessionsLimit(params: ChangeMultidialogSessionsLimitParams)
	{
		void this.store.dispatch('sidebar/multidialog/setOpenSessionsLimit', params.limit);
	}

	handleAddMultidialog(params: AddMultidialogParams)
	{
		const { multidialog, count } = params;
		const isSupport = multidialog.isSupport;
		if (!isSupport)
		{
			return;
		}

		void this.store.dispatch('sidebar/multidialog/setChatsCount', count);
		void this.store.dispatch('sidebar/multidialog/addMultidialogs', [multidialog]);
	}

	handleReadMessageChat(params: ReadMessageChatParams)
	{
		this.deleteUnreadSupportChats(params);
	}

	handleReadMessage(params: ReadMessageParams)
	{
		this.deleteUnreadSupportChats(params);
	}

	handleChangeMultidialogStatus(params: ChangeMultidialogStatusParams)
	{
		const { bot, chat, multidialog } = params;

		const isSupport = multidialog.isSupport;
		if (!isSupport)
		{
			return;
		}

		if (chat)
		{
			void this.store.dispatch('chats/set', chat);
		}

		if (bot)
		{
			void this.userManager.setUsersToModel(bot);
		}

		void this.store.dispatch('sidebar/multidialog/addMultidialogs', [multidialog]);
	}

	handleMessage(params: MessageParams)
	{
		this.setUnreadSupportTickets(params.multidialog);
	}

	handleChatUnread(params: ChatUnreadParams)
	{
		const { chatId, dialogId } = params;

		const isSupport = this.store.getters['sidebar/multidialog/isSupport'](dialogId);
		const isInited = this.store.getters['sidebar/multidialog/isInited'];

		if (isSupport && isInited)
		{
			void this.store.dispatch('sidebar/multidialog/setUnreadChats', [chatId]);
		}
	}
	// endregion

	// region files unsorted and support24
	handleMessageChat(params: MessageChatParams)
	{
		// handle new files while migration is not finished.
		this.setFiles(params);

		// handle new unread chats.
		this.setUnreadSupportTickets(params.multidialog);
	}
	// endregion

	deleteUnreadSupportChats(params: ReadMessageChatParams | ReadMessageParams)
	{
		const notCounter = params.counter === 0;

		if (notCounter)
		{
			void this.store.dispatch('sidebar/multidialog/deleteUnreadChats', params.chatId);
		}
	}

	setUnreadSupportTickets(multidialog: ImModelSidebarMultidialogItem)
	{
		if (!multidialog)
		{
			return;
		}

		const oldMultidialog = this.store.getters['sidebar/multidialog/get'](multidialog.chatId);
		const status = oldMultidialog?.status || multidialog.status;

		void this.store.dispatch('sidebar/multidialog/addMultidialogs', [{ ...multidialog, status }]);
		void this.store.dispatch('sidebar/multidialog/setUnreadChats', [multidialog.chatId]);
	}

	setFiles(params: MessageChatParams)
	{
		const { chatId, users, files } = params;

		if (!this.isSidebarInited(chatId) || this.areFilesMigrated())
		{
			return;
		}

		void this.userManager.setUsersToModel(Object.values(users));
		void this.store.dispatch('files/set', Object.values(files));

		Object.values(files).forEach((file) => {
			void this.store.dispatch('sidebar/files/set', {
				chatId: file.chatId,
				files: [file],
				subType: SidebarDetailBlock.fileUnsorted,
			});
		});
	}

	isSidebarInited(chatId: number): boolean
	{
		return this.store.getters['sidebar/isInited'](chatId);
	}

	areFilesMigrated(): boolean
	{
		return this.store.state.sidebar.isFilesMigrated;
	}

	getMembersCountFromStore(chatId: number): number
	{
		return this.store.getters['sidebar/members/getSize'](chatId);
	}
}
