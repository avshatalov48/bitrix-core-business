export const DialogType = Object.freeze({
	user: 'user',
	chat: 'chat',
	open: 'open',
	general: 'general',
	videoconf: 'videoconf',
	announcement: 'announcement',
	call: 'call',
	support24Notifier: 'support24Notifier',
	support24Question: 'support24Question',
	crm: 'crm',
	sonetGroup: 'sonetGroup',
	calendar: 'calendar',
	tasks: 'tasks',
	thread: 'thread',
	mail: 'mail',
	lines: 'lines',
});

export const DialogScrollThreshold = Object.freeze({
	none: 'none',
	nearTheBottom: 'nearTheBottom',
	halfScreenUp: 'halfScreenUp'
});

export const DialogCrmType = Object.freeze({
	lead: 'lead',
	company: 'company',
	contact: 'contact',
	deal: 'deal',
	none: 'none',
});

export const DialogReferenceClassName = Object.freeze({
	listBody: 'bx-im-dialog-list',
	listItem: 'bx-im-dialog-list-item-reference',
	listItemName: 'bx-im-dialog-list-item-name-reference',
	listItemBody: 'bx-im-dialog-list-item-content-reference',
	listUnreadLoader: 'bx-im-dialog-list-unread-loader-reference',
});

export const DialogTemplateType = Object.freeze({
	message: 'message',
	delimiter: 'delimiter',
	group: 'group',
	historyLoader: 'historyLoader',
	unreadLoader: 'unreadLoader',
	button: 'button',
	placeholder: 'placeholder'
});

export const DialogState = Object.freeze({
	loading: 'loading',
	empty: 'empty',
	show: 'show'
});

export const DialogBlockType = Object.freeze({
	dateGroup: 'dateGroup',
	authorGroup: 'authorGroup',
	newMessages: 'newMessages',
	markedMessages: 'markedMessages'
});