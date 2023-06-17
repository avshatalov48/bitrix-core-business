import {Type} from 'main.core';

import {Core} from 'im.v2.application.core';
import {UserManager} from 'im.v2.lib.user';
import {SidebarDetailBlock} from 'im.v2.const';

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

	getSubscriptionType(): string
	{
		return 'server';
	}

	//region members
	handleChatUserAdd(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		this.userManager.setUsersToModel(Object.values(params.users));

		this.store.dispatch('sidebar/members/set', {
			chatId: params.chatId,
			users: params.newUsers
		});
	}

	handleChatUserLeave(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		this.store.dispatch('sidebar/members/delete', {
			chatId: params.chatId,
			userId: params.userId
		});
	}
	//endregion

	//region task
	handleTaskAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		this.userManager.setUsersToModel(params.users);

		this.store.dispatch('sidebar/tasks/set', {
			chatId: params.link.chatId,
			tasks: [params.link]
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

		this.store.dispatch('sidebar/tasks/delete', {
			chatId: params.chatId,
			id: params.linkId
		});
	}
	//endregion

	//region meetings
	handleCalendarAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		this.userManager.setUsersToModel(params.users);

		this.store.dispatch('sidebar/meetings/set', {
			chatId: params.link.chatId,
			meetings: [params.link]
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

		this.store.dispatch('sidebar/meetings/delete', {
			chatId: params.chatId,
			id: params.linkId
		});
	}
	//endregion

	//region links
	handleUrlAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		this.userManager.setUsersToModel(params.users);

		this.store.dispatch('sidebar/links/set', {
			chatId: params.link.chatId,
			links: [params.link]
		});

		const counter = this.store.getters['sidebar/links/getCounter'](params.link.chatId);
		this.store.dispatch('sidebar/links/setCounter', {
			chatId: params.link.chatId,
			counter: counter + 1
		});
	}

	handleUrlDelete(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		this.store.dispatch('sidebar/links/delete', {
			chatId: params.chatId,
			id: params.linkId
		});
	}
	//endregion

	//region favorite
	handleMessageFavoriteAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		this.userManager.setUsersToModel(params.users);
		this.store.dispatch('files/set', params.files);
		this.store.dispatch('messages/store', [params.link.message]);

		this.store.dispatch('sidebar/favorites/set', {
			chatId: params.link.chatId,
			favorites: [params.link]
		});

		const counter = this.store.getters['sidebar/favorites/getCounter'](params.link.chatId);
		this.store.dispatch('sidebar/favorites/setCounter', {
			chatId: params.link.chatId,
			counter: counter + 1
		});
	}

	handleMessageFavoriteDelete(params)
	{
		if (!this.isSidebarInited(params.chatId))
		{
			return;
		}

		this.store.dispatch('sidebar/favorites/delete', {
			chatId: params.chatId,
			id: params.linkId
		});
	}
	//endregion

	//region files
	handleFileAdd(params)
	{
		if (!this.isSidebarInited(params.link.chatId))
		{
			return;
		}

		this.userManager.setUsersToModel(params.users);
		this.store.dispatch('files/set', params.files);

		if (!params.link.subType)
		{
			params.link.subType = SidebarDetailBlock.fileUnsorted;
		}

		this.store.dispatch('sidebar/files/set', {
			chatId: params.link.chatId,
			files: [params.link]
		});
	}

	handleFileDelete(params)
	{
		const chatId = Type.isNumber(params.chatId) ? params.chatId : Number.parseInt(params.chatId, 10);
		if (!this.isSidebarInited(chatId))
		{
			return;
		}

		const sidebarFileId = params.linkId ? params.linkId : params.fileId;
		this.store.dispatch('sidebar/files/delete', {
			chatId: chatId,
			id: sidebarFileId
		});
	}
	//endregion

	//region files unsorted
	handleMessage(params)
	{
		// handle new files while migration is not finished.
		if (!this.isSidebarInited(params.chatId) || this.isFilesMigrated())
		{
			return;
		}

		this.userManager.setUsersToModel(Object.values(params.users));
		this.store.dispatch('files/set', Object.values(params.files));

		Object.values(params.files).forEach(file => {
			file.subType = SidebarDetailBlock.fileUnsorted;

			this.store.dispatch('sidebar/files/set', {
				chatId: file.chatId,
				files: [file]
			});
		});
	}
	//endregion

	isSidebarInited(chatId: number): boolean
	{
		return this.store.getters['sidebar/isInited'](chatId);
	}

	isFilesMigrated(): boolean
	{
		return this.store.state.sidebar.isFilesMigrated;
	}
}
