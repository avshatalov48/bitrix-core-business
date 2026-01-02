import { ChatType, SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isCopilot = (chatContext: ImModelChat) => chatContext.type === ChatType.copilot;

const copilotPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.copilot,
		SidebarMainPanelBlock.tariffLimit,
		SidebarMainPanelBlock.copilotInfo,
		SidebarMainPanelBlock.taskList,
		SidebarMainPanelBlock.meetingList,
	],
});

export { isCopilot, copilotPreset };
