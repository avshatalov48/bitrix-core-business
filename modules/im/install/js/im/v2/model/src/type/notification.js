export type Notification = {
	id: number,
	authorId: number,
	date: Date,
	title: string,
	text: string,
	params: {
		canAnswer: 'Y' | 'N',
		attach: Object[],
		users: number[],
	},
	replaces: Object[],
	notifyButtons: NotificationButton[],
	sectionCode: string,
	read: boolean,
	settingName: string
};

export type NotificationButton = {
	BG_COLOR: string,
	COMMAND: string,
	COMMAND_PARAMS: string,
	DISPLAY: string,
	TEXT: string,
	TEXT_COLOR: string,
	TYPE: string
};