import { Core } from 'im.v2.application.core';
import { UserType, SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isBot = (chatContext: ImModelChat) => {
	const user = Core.getStore().getters['users/get'](chatContext.dialogId);

	return user?.type === UserType.bot;
};

const botPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.user,
		SidebarMainPanelBlock.tariffLimit,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
		SidebarMainPanelBlock.fileUnsortedList,
		SidebarMainPanelBlock.marketAppList,
	],
});

export { isBot, botPreset };
