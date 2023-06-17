export type RecentItem = {
	dialogId: string,
	message: {
		id: number,
		senderId: number,
		date: Date,
		status: string,
		sending: boolean,
		text: string,
		params: {
			withFile: boolean | Object,
			withAttach: boolean | Object
		}
	},
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
	options: {
		birthdayPlaceholder: boolean,
		defaultUserRecord: boolean
	}
};