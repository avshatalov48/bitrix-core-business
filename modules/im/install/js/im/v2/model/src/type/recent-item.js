export type RecentItem = {
	dialogId: string,
	messageId: number | string,
	draft: {
		text: string,
		date: ?Date
	},
	unread: boolean,
	pinned: boolean,
	liked: boolean,
	invitation: {
		isActive: boolean,
		originator: number,
		canResend: boolean
	},
	isFakeElement: boolean, // invitation or fake element for new users
	isBirthdayPlaceholder: boolean,
	lastActivityDate: null | Date,
};
