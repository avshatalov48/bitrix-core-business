import { Loc } from 'main.core';

import { Messenger } from 'im.public';
import { RecentMenu, type MenuItem } from 'im.v2.lib.menu';

import { CopilotRecentService } from './copilot-service';

export class CopilotRecentMenu extends RecentMenu
{
	getMenuItems(): MenuItem[]
	{
		return [
			this.getOpenItem(),
			this.getPinMessageItem(),
			this.getHideItem(),
		];
	}

	getOpenItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_OPEN'),
			onclick: () => {
				Messenger.openCopilot(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getHideItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIST_COPILOT_MENU_HIDE'),
			onclick: () => {
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
