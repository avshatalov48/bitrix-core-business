import { Loc } from 'main.core';

import { ChannelManager } from 'im.v2.lib.channel';
import { SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isChannel = (chatContext: ImModelChat) => ChannelManager.isChannel(chatContext.dialogId);

const channelPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.chat,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
		SidebarMainPanelBlock.taskList,
		SidebarMainPanelBlock.meetingList,
	],
	getHeaderTitle: () => Loc.getMessage('IM_SIDEBAR_CHANNEL_HEADER_TITLE'),
});

export { isChannel, channelPreset };
