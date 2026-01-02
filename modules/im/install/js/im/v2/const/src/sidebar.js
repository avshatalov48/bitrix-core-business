export const SidebarDetailBlock = Object.freeze({
	main: 'main',
	members: 'members',
	link: 'link',
	favorite: 'favorite',
	task: 'task',
	brief: 'brief',
	media: 'media',
	file: 'file',
	audio: 'audio',
	document: 'document',
	fileUnsorted: 'fileUnsorted',
	meeting: 'meeting',
	market: 'market',
	messageSearch: 'messageSearch',
	chatsWithUser: 'chatsWithUser',
	multidialog: 'multidialog',
	none: '',
});

export const SidebarFileGroups = Object.freeze({
	media: 'media',
	audio: 'audio',
	file: 'file',
	brief: 'brief',
	fileUnsorted: 'fileUnsorted',
});

export const SidebarFileTabGroups = Object.freeze({
	[SidebarFileGroups.media]: SidebarFileGroups.media,
	[SidebarFileGroups.file]: SidebarFileGroups.file,
	[SidebarFileGroups.audio]: SidebarFileGroups.audio,
	[SidebarFileGroups.brief]: SidebarFileGroups.brief,
});

export const SidebarMainPanelBlock = {
	support: 'support',
	chat: 'chat',
	notes: 'notes',
	user: 'user',
	copilot: 'copilot',
	copilotInfo: 'copilotInfo',
	info: 'info',
	post: 'post',
	fileList: 'fileList',
	fileUnsortedList: 'fileUnsortedList',
	task: 'task',
	taskList: 'taskList',
	meetingList: 'meetingList',
	marketAppList: 'marketAppList',
	multidialog: 'multidialog',
	tariffLimit: 'tariffLimit',
	collabHelpdesk: 'collabHelpdesk',
};

export type SidebarMainPanelBlockType = $Keys<typeof SidebarMainPanelBlock>;
