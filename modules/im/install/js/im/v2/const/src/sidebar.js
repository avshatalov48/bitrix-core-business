export const SidebarBlock = Object.freeze({
	main: 'main',
	info: 'info',
	task: 'task',
	brief: 'brief',
	file: 'file',
	fileUnsorted: 'fileUnsorted',
	sign: 'sign',
	meeting: 'meeting',
	market: 'market',
});

export const SidebarDetailBlock = Object.freeze({
	main: 'main',
	link: 'link',
	favorite: 'favorite',
	task: 'task',
	brief: 'brief',
	media: 'media',
	audio: 'audio',
	document: 'document',
	fileUnsorted: 'fileUnsorted',
	other: 'other',
	sign: 'sign',
	meeting: 'meeting',
	market: 'market',
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
	[SidebarFileTypes.other]: SidebarFileTypes.other,
});
