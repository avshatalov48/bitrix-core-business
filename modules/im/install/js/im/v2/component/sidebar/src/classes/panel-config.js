import { ChatType } from 'im.v2.const';

const MainPanelSpecialType = {
	support24: 'support24',
};

export const MainPanelType = {
	user: [ChatType.user],
	chat: [ChatType.chat],
	copilot: [ChatType.copilot],
	support24: [MainPanelSpecialType.support24],
};

export const MainPanelBlock = Object.freeze({
	chat: 'chat',
	user: 'user',
	info: 'info',
	file: 'file',
	fileUnsorted: 'fileUnsorted',
	task: 'task',
	meeting: 'meeting',
	market: 'market',
});

export const MainPanels = {
	[MainPanelType.user]: {
		[MainPanelBlock.user]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
		[MainPanelBlock.fileUnsorted]: 30,
		[MainPanelBlock.task]: 40,
		[MainPanelBlock.meeting]: 50,
		[MainPanelBlock.market]: 60,
	},
	[MainPanelType.chat]: {
		[MainPanelBlock.chat]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
		[MainPanelBlock.fileUnsorted]: 30,
		[MainPanelBlock.task]: 40,
		[MainPanelBlock.meeting]: 50,
		[MainPanelBlock.market]: 60,
	},
	[MainPanelType.copilot]: {
		[MainPanelBlock.user]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.task]: 40,
		[MainPanelBlock.meeting]: 50,
	},
};
