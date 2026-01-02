import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { Utils } from 'im.v2.lib.utils';
import { BaseMenu } from 'im.v2.lib.menu';
import { CallManager } from 'im.v2.lib.call';
import { PermissionManager } from 'im.v2.lib.permission';
import { EventType, SidebarDetailBlock, UserType } from 'im.v2.const';

import type { MenuItemOptions } from 'ui.system.menu';
import type { ImModelUser } from 'im.v2.model';

export class SearchContextMenu extends BaseMenu
{
	callManager: CallManager;

	constructor()
	{
		super();

		this.id = 'im-chat-search-context-menu';
		this.callManager = CallManager.getInstance();
		this.permissionManager = PermissionManager.getInstance();
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		return [
			this.getOpenItem(),
			this.getOpenProfileItem(),
			this.getChatsWithUserItem(),
		];
	}

	getOpenItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_MENU_OPEN'),
			onClick: () => {
				void Messenger.openChat(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getOpenProfileItem(): ?MenuItemOptions
	{
		if (!this.isUser() || this.isBot())
		{
			return null;
		}

		const profileUri = Utils.user.getProfileLink(this.context.dialogId);

		return {
			title: Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
			onClick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			},
		};
	}

	getChatsWithUserItem(): ?MenuItemOptions
	{
		if (!this.isUser() || this.isBot() || this.isChatWithCurrentUser())
		{
			return null;
		}

		const isAnyChatOpened = this.store.getters['application/getLayout'].entityId.length > 0;

		return {
			title: Loc.getMessage('IM_LIB_MENU_FIND_SHARED_CHATS'),
			onClick: async () => {
				if (!isAnyChatOpened)
				{
					await Messenger.openChat(this.context.dialogId);
				}

				EventEmitter.emit(EventType.sidebar.open, {
					panel: SidebarDetailBlock.chatsWithUser,
					standalone: true,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}

	isUser(): boolean
	{
		return this.store.getters['chats/isUser'](this.context.dialogId);
	}

	isBot(): boolean
	{
		if (!this.isUser())
		{
			return false;
		}

		const user: ImModelUser = this.store.getters['users/get'](this.context.dialogId);

		return user.type === UserType.bot;
	}

	isChatWithCurrentUser(): boolean
	{
		return this.getCurrentUserId() === Number.parseInt(this.context.dialogId, 10);
	}
}
