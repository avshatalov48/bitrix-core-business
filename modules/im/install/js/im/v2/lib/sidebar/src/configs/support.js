import { Core } from 'im.v2.application.core';
import { SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isSupport = (chatContext: ImModelChat) => Core.getStore().getters['sidebar/multidialog/isSupport'](chatContext.dialogId);

const supportPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.support,
		SidebarMainPanelBlock.tariffLimit,
		SidebarMainPanelBlock.multidialog,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
	],
});

export { isSupport, supportPreset };
