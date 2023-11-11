export const ChatActionType = Object.freeze({
	avatar: 'avatar',
	call: 'call',
	extend: 'extend',
	leave: 'leave',
	leaveOwner: 'leaveOwner',
	kick: 'kick',
	mute: 'mute',
	rename: 'rename',
	send: 'send',
	userList: 'userList',
});

export const ChatActionGroup = Object.freeze({
	manageSettings: 'manageSettings',
	manageUi: 'manageUi',
	manageUsers: 'manageUsers',
	canPost: 'canPost',
});
