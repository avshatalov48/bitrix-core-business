import 'ui.notification';
import { Loc } from 'main.core';

import { SidebarMenu } from '../sidebar-base-menu';
import { LinkManager } from './link-manager';

import type { MenuItem } from 'im.v2.lib.menu';

export class LinkMenu extends SidebarMenu
{
	constructor()
	{
		super();
		this.linkManager = new LinkManager();
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getCopyLinkItem(Loc.getMessage('IM_SIDEBAR_MENU_COPY_LINK')),
			this.getDeleteLinkItem(),
		];
	}

	getDeleteLinkItem(): ?MenuItem
	{
		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_FROM_LINKS'),
			onclick: function() {
				this.linkManager.delete(this.context);
				this.menuInstance.close();
			}.bind(this),
		};
	}
}
