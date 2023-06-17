import {RecentMenu} from 'im.old-chat-embedding.lib.menu';

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