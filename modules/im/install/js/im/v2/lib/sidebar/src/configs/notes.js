import { Core } from 'im.v2.application.core';
import { ChatType, SidebarMainPanelBlock } from 'im.v2.const';

import { SidebarPreset } from '../classes/preset';

import type { ImModelChat } from 'im.v2.model';

const isNotes = (chatContext: ImModelChat) => {
	return Core.getStore().getters['chats/isNotes'](chatContext.dialogId);
};

const notesPreset = new SidebarPreset({
	blocks: [
		SidebarMainPanelBlock.notes,
		SidebarMainPanelBlock.tariffLimit,
		SidebarMainPanelBlock.info,
		SidebarMainPanelBlock.fileList,
		SidebarMainPanelBlock.fileUnsortedList,
	],
});

export { isNotes, notesPreset };
