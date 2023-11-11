export const Settings = Object.freeze({
	appearance: {
		background: 'backgroundImageId',
		alignment: 'chatAlignment',
	},
	notification: {
		enableSound: 'enableSound',
	},
	hotkey: {
		sendByEnter: 'sendByEnter',
	},
	message: {
		bigSmiles: 'enableBigSmile',
	},
	recent: {
		showBirthday: 'viewBirthday',
		showInvited: 'viewCommonUsers',
		showLastMessage: 'viewLastMessage',
	},
	desktop: {
		enableRedirect: 'openDesktopFromPanel',
	},
});

export const SettingsSection = Object.freeze({
	appearance: 'appearance',
	notification: 'notification',
	hotkey: 'hotkey',
	message: 'message',
	recent: 'recent',
	desktop: 'desktop',
});
