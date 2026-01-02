import { ChatType, SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isLines = (chatContext: ImModelChat) => chatContext.type === ChatType.lines;

const linesPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.chat,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
	],
	isHeaderMenuEnabled: () => false,
});

export { isLines, linesPreset };
