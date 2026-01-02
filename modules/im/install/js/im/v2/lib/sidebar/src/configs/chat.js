import { ChatType, SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isChat = (chatContext: ImModelChat) => chatContext.type === ChatType.chat;

const chatPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.chat,
		SidebarMainPanelBlock.tariffLimit,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
		SidebarMainPanelBlock.fileUnsortedList,
		SidebarMainPanelBlock.taskList,
		SidebarMainPanelBlock.meetingList,
		SidebarMainPanelBlock.marketAppList,
	],
});

export { isChat, chatPreset };
