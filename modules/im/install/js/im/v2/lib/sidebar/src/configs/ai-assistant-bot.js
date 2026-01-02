import { Core } from 'im.v2.application.core';
import { SidebarMainPanelBlock } from 'im.v2.const';
import { Loc } from 'main.core';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isAiAssistantBot = (chatContext: ImModelChat) => Core.getStore().getters['users/bots/isAiAssistant'](chatContext.dialogId);

const aiAssistantBotPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.user,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.taskList,
		SidebarMainPanelBlock.meetingList,
	],
	areSharedChatsEnabled: () => false,
	getCustomDescription: () => {
		return Loc.getMessage('IM_SIDEBAR_AI_ASSISTANT_DESCRIPTION');
	},
});

export { isAiAssistantBot, aiAssistantBotPreset };
