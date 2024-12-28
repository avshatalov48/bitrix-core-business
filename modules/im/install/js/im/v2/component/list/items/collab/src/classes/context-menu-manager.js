import { Loc } from 'main.core';

import { Layout } from 'im.v2.const';
import { LayoutManager } from 'im.v2.lib.layout';
import { RecentMenu, type MenuItem } from 'im.v2.lib.menu';

export class CollabRecentMenu extends RecentMenu
{
	getMenuItems(): MenuItem[]
	{
		return [
			this.getUnreadMessageItem(),
			this.getPinMessageItem(),
			this.getMuteItem(),
			// this.getLeaveItem(),
		];
	}

	getOpenItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_OPEN'),
			onclick: () => {
				LayoutManager.getInstance().setLayout({
					name: Layout.collab.name,
					entityId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}
}
