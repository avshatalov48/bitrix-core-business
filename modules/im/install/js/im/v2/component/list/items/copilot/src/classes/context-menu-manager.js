import { Loc } from 'main.core';

import { RecentMenu } from 'im.v2.lib.menu';

import { CopilotRecentService } from './copilot-service';
import type { MenuItemOptions } from 'ui.system.menu';

export class CopilotRecentMenu extends RecentMenu
{
	getMenuItems(): MenuItemOptions[]
	{
		return [
			this.getUnreadMessageItem(),
			this.getPinMessageItem(),
			this.getMuteItem(),
			this.getHideItem(),
			this.getLeaveItem(),
		];
	}

	getHideItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_MENU_HIDE_MSGVER_1'),
			onClick: () => {
				this.getRecentService().hideChat(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getRecentService(): CopilotRecentService
	{
		if (!this.service)
		{
			this.service = new CopilotRecentService();
		}

		return this.service;
	}
}
