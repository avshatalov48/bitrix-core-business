import { ChatType, UserRole, ChatActionGroup } from 'im.v2.const';

type ActionGroupItem = $Keys<typeof ChatActionGroup>;
type RoleItem = $Keys<typeof UserRole>;

export type Chat = {
	dialogId: string,
	chatId: number,
	type: $Values<typeof ChatType>,
	name: string,
	description: string,
	avatar: string,
	color: string,
	extranet: boolean,
	containsCollaber: boolean,
	counter: number,
	userCounter: number,
	lastReadId: number,
	markedId: number,
	lastMessageId: number,
	lastMessageViews: {
		countOfViewers: number,
		firstViewer?: {
			userId: number,
			userName: string,
			date: Date
		},
		messageId: number
	},
	savedPositionMessageId: number,
	managerList: number[],
	muteList: number[],
	quoteId: number,
	ownerId: number,
	entityLink: {
		type: string,
		url: string,
	},
	dateCreate: Date | null,
	public: {
		code: string,
		link: string
	},
	inited: boolean,
	loading: boolean,
	hasPrevPage: boolean,
	hasNextPage: boolean,
	isHistoryLimitExceeded: boolean,
	diskFolderId: number,
	role: RoleItem,
	permissions: Object<ActionGroupItem, RoleItem>,
	tariffRestrictions: {
		isHistoryLimitExceeded: boolean,
	},
	parentChatId: number,
	backgroundId: string,
	isTextareaEnabled: boolean,
};

export type CollabInfo = {
	collabId: number,
	guestCount: number,
	entities: {
		tasks: CollabEntityInfo,
		files: CollabEntityInfo,
		calendar: CollabEntityInfo,
	};
};

export type CollabEntityInfo = {
	counter: number,
	url: string,
};
