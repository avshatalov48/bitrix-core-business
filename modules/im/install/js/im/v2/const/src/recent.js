export const ChatTypes = {
	user: 'user',
	chat: 'chat',
	open: 'open',
	general: 'general',
	videoconf: 'videoconf',
	announcement: 'announcement',
	call: 'call',
	support24: {
		notifier: 'support24Notifier',
		question: 'support24Question'
	},
	crm: 'crm',
	group: 'sonetGroup',
	calendar: 'calendar',
	task: 'tasks'
};

export const UserStatus = {
	online: 'online',
	mobileOnline: 'mobile-online',
	idle: 'idle',
	dnd: 'dnd',
	away: 'away',
	break: 'break'
};

export const RecentSection = {
	general: 'general',
	pinned: 'pinned'
};

export const MessageStatus = {
	received: 'received',
	delivered: 'delivered',
	error: 'error'
};

export const RecentCallStatus = {
	waiting: 'waiting',
	joined: 'joined'
};

export const RecentSettings = {
	showBirthday: 'showBirthday',
	showInvited: 'showInvited',
	showLastMessage: 'showLastMessage'
};

// old chat names -> new model names
export const RecentSettingsMap = {
	'viewBirthday': 'showBirthday',
	'viewCommonUsers': 'showInvited',
	'viewLastMessage': 'showLastMessage'
};