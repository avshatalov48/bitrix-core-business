import { Loc } from 'main.core';

import { ChatType, SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isComment = (chatContext: ImModelChat) => chatContext.type === ChatType.comment;

const commentPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.post,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
		SidebarMainPanelBlock.taskList,
		SidebarMainPanelBlock.meetingList,
	],
	getHeaderTitle: () => Loc.getMessage('IM_SIDEBAR_COMMENTS_HEADER_TITLE'),
	isHeaderMenuEnabled: () => false,
});

export { isComment, commentPreset };
