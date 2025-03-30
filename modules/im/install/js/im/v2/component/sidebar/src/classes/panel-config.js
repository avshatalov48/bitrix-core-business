import { ChatType } from 'im.v2.const';

export const MainPanelType = {
	user: [ChatType.user],
	chat: [ChatType.chat],
	copilot: [ChatType.copilot],
	support24Question: [ChatType.support24Question],
	channel: [ChatType.channel],
	openChannel: [ChatType.openChannel],
	comment: [ChatType.comment],
	generalChannel: [ChatType.generalChannel],
	collab: [ChatType.collab],
	lines: [ChatType.lines],
};

export const MainPanelBlock = Object.freeze({
	support: 'support',
	chat: 'chat',
	user: 'user',
	copilot: 'copilot',
	copilotInfo: 'copilotInfo',
	info: 'info',
	post: 'post',
	file: 'file',
	fileUnsorted: 'fileUnsorted',
	task: 'task',
	meeting: 'meeting',
	market: 'market',
	multidialog: 'multidialog',
	tariffLimit: 'tariffLimit',
	collabHelpdesk: 'collabHelpdesk',
});

export const MainPanels = {
	[MainPanelType.user]: {
		[MainPanelBlock.user]: 10,
		[MainPanelBlock.tariffLimit]: 15,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
		[MainPanelBlock.fileUnsorted]: 30,
		[MainPanelBlock.task]: 40,
		[MainPanelBlock.meeting]: 50,
		[MainPanelBlock.market]: 60,
	},
	[MainPanelType.chat]: {
		[MainPanelBlock.chat]: 10,
		[MainPanelBlock.tariffLimit]: 15,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
		[MainPanelBlock.fileUnsorted]: 30,
		[MainPanelBlock.task]: 40,
		[MainPanelBlock.meeting]: 50,
		[MainPanelBlock.market]: 60,
	},
	[MainPanelType.copilot]: {
		[MainPanelBlock.copilot]: 10,
		[MainPanelBlock.tariffLimit]: 15,
		[MainPanelBlock.copilotInfo]: 20,
		[MainPanelBlock.task]: 40,
		[MainPanelBlock.meeting]: 50,
	},
	[MainPanelType.channel]: {
		[MainPanelBlock.chat]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
	},
	[MainPanelType.openChannel]: {
		[MainPanelBlock.chat]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
	},
	[MainPanelType.generalChannel]: {
		[MainPanelBlock.chat]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
	},
	[MainPanelType.comment]: {
		[MainPanelBlock.post]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
		[MainPanelBlock.task]: 40,
		[MainPanelBlock.meeting]: 50,
	},
	[MainPanelType.support24Question]: {
		[MainPanelBlock.support]: 10,
		[MainPanelBlock.tariffLimit]: 15,
		[MainPanelBlock.multidialog]: 20,
		[MainPanelBlock.info]: 30,
		[MainPanelBlock.file]: 40,
	},
	[MainPanelType.collab]: {
		[MainPanelBlock.chat]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
		[MainPanelBlock.fileUnsorted]: 30,
		[MainPanelBlock.collabHelpdesk]: 40,
	},
	[MainPanelType.lines]: {
		[MainPanelBlock.chat]: 10,
		[MainPanelBlock.info]: 20,
		[MainPanelBlock.file]: 30,
	},
};
