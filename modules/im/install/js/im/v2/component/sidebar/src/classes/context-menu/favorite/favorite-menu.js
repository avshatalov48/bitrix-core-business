import {Loc} from 'main.core';

import {SidebarMenu} from '../sidebar-base-menu';

import type {MenuItem} from 'im.v2.lib.menu';
import {MessageService} from 'im.v2.provider.service';

export class FavoriteMenu extends SidebarMenu
{
	constructor()
	{
		super();

		this.id = 'im-sidebar-context-menu';
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getDeleteFromFavoriteItem(),
		];
	}

	getDeleteFromFavoriteItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_REMOVE_FROM_SAVED'),
			onclick: function() {
				const messageService = new MessageService({chatId: this.context.chatId});
				messageService.removeMessageFromFavorite(this.context.messageId);
				this.menuInstance.close();
			}.bind(this)
		};
	}
}