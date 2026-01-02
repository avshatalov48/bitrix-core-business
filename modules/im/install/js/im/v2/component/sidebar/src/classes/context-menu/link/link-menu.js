import 'ui.notification';
import { Loc } from 'main.core';

import { SidebarMenu } from '../sidebar-base-menu';
import { LinkManager } from './link-manager';

import type { MenuItemOptions } from 'ui.system.menu';

export class LinkMenu extends SidebarMenu
{
	constructor()
	{
		super();
		this.linkManager = new LinkManager();
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getCopyLinkItem(Loc.getMessage('IM_SIDEBAR_MENU_COPY_LINK')),
			this.getDeleteLinkItem(),
		];
	}

	getDeleteLinkItem(): ?MenuItemOptions
	{
		if (this.context.authorId !== this.getCurrentUserId())
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_FROM_LINKS'),
			onClick: function() {
				this.linkManager.delete(this.context);
				this.menuInstance.close();
			}.bind(this),
		};
	}
}
