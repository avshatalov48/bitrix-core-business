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
	other: 'other',
	meeting: 'meeting',
	market: 'market',
	messageSearch: 'messageSearch',
	chatsWithUser: 'chatsWithUser',
	multidialog: 'multidialog',
	none: '',
});

export const SidebarFileTypes = Object.freeze({
	media: 'media',
	audio: 'audio',
	document: 'document',
	other: 'other',
	brief: 'brief',
	fileUnsorted: 'fileUnsorted',
});

export const SidebarFileTabTypes = Object.freeze({
	[SidebarFileTypes.media]: SidebarFileTypes.media,
	[SidebarFileTypes.audio]: SidebarFileTypes.audio,
	[SidebarFileTypes.document]: SidebarFileTypes.document,
	[SidebarFileTypes.brief]: SidebarFileTypes.brief,
	[SidebarFileTypes.other]: SidebarFileTypes.other,
});
