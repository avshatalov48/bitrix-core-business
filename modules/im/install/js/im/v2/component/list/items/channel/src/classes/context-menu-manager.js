import { Loc } from 'main.core';

import { Layout } from 'im.v2.const';
import { LayoutManager } from 'im.v2.lib.layout';
import { RecentMenu } from 'im.v2.lib.menu';

import type { MenuItemOptions } from 'ui.system.menu';

export class ChannelRecentMenu extends RecentMenu
{
	getMenuItems(): MenuItemOptions[]
	{
		return [
			this.getOpenItem(),
		];
	}

	getOpenItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_MENU_OPEN'),
			onClick: () => {
				void LayoutManager.getInstance().setLayout({
					name: Layout.channel,
					entityId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}
}
