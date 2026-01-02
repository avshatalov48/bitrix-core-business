import { RecentMenu } from 'im.v2.lib.menu';

import type { MenuItemOptions } from 'ui.system.menu';

export class CollabRecentMenu extends RecentMenu
{
	getMenuItems(): MenuItemOptions[]
	{
		return [
			this.getUnreadMessageItem(),
			this.getPinMessageItem(),
			this.getMuteItem(),
		];
	}
}
