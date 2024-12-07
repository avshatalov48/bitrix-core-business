import { MessageMenu } from 'im.v2.component.message-list';

import type { MenuItem } from 'im.v2.lib.menu';

export class ChannelMessageMenu extends MessageMenu
{
	getMenuItems(): MenuItem[]
	{
		return [
			// this.getReplyItem(),
			this.getCopyItem(),
			this.getCopyLinkItem(),
			this.getCopyFileItem(),
			this.getPinItem(),
			this.getForwardItem(),
			this.getDelimiter(),
			this.getMarkItem(),
			this.getFavoriteItem(),
			this.getDelimiter(),
			this.getDownloadFileItem(),
			this.getSaveToDisk(),
			this.getDelimiter(),
			this.getEditItem(),
			this.getDeleteItem(),
		];
	}
}
