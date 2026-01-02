import { Loc } from 'main.core';

import { MessageService } from 'im.v2.provider.service.message';

import { SidebarMenu } from '../sidebar-base-menu';

import type { MenuItemOptions } from 'ui.system.menu';

export class FavoriteMenu extends SidebarMenu
{
	constructor()
	{
		super();

		this.id = 'im-sidebar-context-menu';
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getDeleteFromFavoriteItem(),
		];
	}

	getDeleteFromFavoriteItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_REMOVE_FROM_SAVED_V2'),
			onClick: function() {
				const messageService = new MessageService({ chatId: this.context.chatId });
				messageService.removeMessageFromFavorite(this.context.messageId);
				this.menuInstance.close();
			}.bind(this),
		};
	}
}
