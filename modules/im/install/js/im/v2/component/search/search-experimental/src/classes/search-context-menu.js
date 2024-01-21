import { RecentMenu, type MenuItem } from 'im.v2.lib.menu';

export class SearchContextMenu extends RecentMenu
{
	getMenuItems(): MenuItem[]
	{
		return [
			this.getOpenItem(),
			this.getCallItem(),
			this.getOpenProfileItem(),
			this.getChatsWithUserItem(),
		];
	}
}
