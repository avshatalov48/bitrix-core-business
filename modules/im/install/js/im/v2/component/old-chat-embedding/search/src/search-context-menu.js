import {RecentMenu} from 'im.v2.lib.menu';

export class SearchContextMenu extends RecentMenu
{
	getMenuItems(): Array
	{
		return [
			this.getSendMessageItem(),
			this.getCallItem(),
			this.getHistoryItem(),
			this.getOpenProfileItem(),
		];
	}
}