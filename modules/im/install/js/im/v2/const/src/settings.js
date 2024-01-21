import { DialogAlignment, NotificationSettingsMode } from 'im.v2.const';

export const Settings = Object.freeze({
	appearance: {
		background: 'backgroundImageId',
		alignment: 'chatAlignment',
	},
	notification: {
		enableSound: 'enableSound',
		enableAutoRead: 'notifyAutoRead',
		mode: 'notifyScheme',
		enableWeb: 'notifySchemeSendSite',
		enableMail: 'notifySchemeSendEmail',
		enablePush: 'notifySchemeSendPush',
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
	user: {
		status: 'status',
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

export type RawSettings = {
	backgroundImage: boolean,
	backgroundImageId: string,
	bxdNotify: boolean,
	callAcceptIncomingVideo: string,
	chatAlignment: DialogAlignment.center | DialogAlignment.left,
	correctText: boolean,
	enableBigSmile: boolean,
	enableDarkTheme: string,
	enableRichLink: boolean,
	enableSound: string,
	generalNotify: boolean,
	isCurrentThemeDark: boolean,
	linesNewGroupEnable: boolean,
	linesTabEnable: boolean,
	loadLastMessage: boolean,
	loadLastNotify: boolean,
	nativeNotify: boolean,
	next: boolean,
	notifications: RawNotificationSettingsBlock[],
	notifyAutoRead: boolean,
	notifyScheme: NotificationSettingsMode.simple | NotificationSettingsMode.expert,
	notifySchemeLevel: string,
	notifySchemeSendEmail: boolean,
	notifySchemeSendPush: boolean,
	notifySchemeSendSite: boolean,
	notifySchemeSendXmpp: boolean,
	openDesktopFromPanel: boolean,
	panelPositionHorizontal: string,
	panelPositionVertical: string,
	pinnedChatSort: string,
	privacyCall: string,
	privacyChat: string,
	privacyMessage: string,
	privacyProfile: string,
	privacySearch: string,
	sendByEnter: boolean,
	sshNotify: boolean,
	status: string,
	trackStatus: string,
	viewBirthday: boolean,
	viewCommonUsers: boolean,
	viewGroup: boolean,
	viewLastMessage: boolean,
	viewOffline: boolean,
};

export type RawNotificationSettingsBlock = {
	id: string,
	label: string,
	notices: NotificationSettingsItem[],
};

export type NotificationSettingsBlock = {
	id: string,
	label: string,
	items: {
		[item: string]: NotificationSettingsItem,
	},
};

export type NotificationSettingsItem = {
	id: string,
	label: string,
	mail: boolean,
	push: boolean,
	site: boolean,
	disabled: string[],
};

export const NotificationSettingsType = {
	web: 'site',
	mail: 'mail',
	push: 'push',
};
