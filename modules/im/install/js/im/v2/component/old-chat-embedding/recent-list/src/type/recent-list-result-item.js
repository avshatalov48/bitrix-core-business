const ChatType = Object.freeze({
	'chat': 'C',
	'lines': 'L',
	'channel': 'O',
	'thread': 'T'
});

const MessageType = Object.freeze({
	system: 'S',
	private: 'P',
	chat: 'C',
	channel: 'O',
	thread: 'T',
	lines: 'L'
});

const MessageStatus = Object.freeze({
	received: 'received',
	delivered: 'delivered',
	error: 'error'
});

const ExternalAuthId = Object.freeze({
	default: 'default',
	bot: 'bot',
	imconnector: 'imconnector',
	network: 'network'
});

const UserStatus = Object.freeze({
	online: 'online',
	dnd: 'dnd',
	away: 'away',
	break: 'break'
});

const RecentItemType = Object.freeze({
	chat: 'chat',
	user: 'user'
});

export type ImRecentListResultItem = {
	id: string,
	title: string,
	type: RecentItemType.chat | RecentItemType.user,
	chat_id: number,
	counter: number,
	unread: boolean,
	date_update: string,
	pinned: boolean,
	avatar: {
		url: string,
		color: string
	},
	message: {
		id: number,
		author_id: number,
		text: string,
		date: string,
		status: MessageStatus.delivered | MessageStatus.received | MessageStatus.error,
		attach: boolean,
		file: boolean,
		uuid: string | null
	},
	// only for chats
	chat?: {
		id: number,
		name: string,
		type: ChatType.chat | ChatType.lines | ChatType.channel | ChatType.thread,
		avatar: string,
		color: string,
		date_create: string,
		dialogId: string,
		entity_data_1: string,
		entity_data_2: string,
		entity_data_3: string,
		entity_id: string,
		entity_type: string,
		extranet: boolean,
		manager_list: number[],
		message_type: MessageType.system | MessageType.private | MessageType.chat | MessageType.channel | MessageType.thread | MessageType.lines,
		mute_list: number[],
		owner: number
	},
	// only for users
	user?: {
		// general info
		id: number,
		first_name: string,
		last_name: string,
		name: string,
		gender: 'M' | 'F',
		avatar: string,
		color: string,
		work_position: string,
		departments: number[],
		phones: Object | false,
		last_activity_date: string,
		// types
		bot: boolean,
		connector: boolean,
		network: boolean,
		extranet: boolean,
		external_auth_id: ExternalAuthId.default | ExternalAuthId.bot | ExternalAuthId.imconnector | ExternalAuthId.network,
		// statuses
		status: UserStatus.online | UserStatus.offline | UserStatus.dnd | UserStatus.away | UserStatus.break,
		absent: boolean,
		active: boolean,
		idle: boolean,
		birthday: string,
		mobile_last_date: string | false,
		desktop_last_date: string | false
	},
	invited?: {
		originator_id: number,
		can_resend: boolean
	},
	options?: {
		// invitations stuff
		default_user_record?: boolean
	}
}