import { Loc } from 'main.core';

import { ChatType, SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isCollab = (chatContext: ImModelChat) => chatContext.type === ChatType.collab;

const collabPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.chat,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
		SidebarMainPanelBlock.fileUnsortedList,
		SidebarMainPanelBlock.collabHelpdesk,
	],
	getHeaderTitle: () => Loc.getMessage('IM_SIDEBAR_COLLAB_HEADER_TITLE'),
});

export { isCollab, collabPreset };
