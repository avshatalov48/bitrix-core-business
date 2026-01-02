import { SidebarMainPanelBlock, ChatType } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isTaskComments = (chatContext: ImModelChat) => chatContext.type === ChatType.taskComments;

const taskCommentsPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.task,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
		SidebarMainPanelBlock.meetingList,
	],
	isHeaderMenuEnabled: () => false,
});

export { isTaskComments, taskCommentsPreset };
