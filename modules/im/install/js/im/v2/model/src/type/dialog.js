import { DialogType, UserRole, ChatActionGroup } from 'im.v2.const';

type WritingListItem = {
	userId: number,
	userName: string
};

type ActionGroupItem = $Keys<typeof ChatActionGroup>;
type RoleItem = $Keys<typeof UserRole>;

export type Dialog = {
	dialogId: string,
	chatId: number,
	type: $Values<typeof DialogType>,
	name: string,
	description: string,
	avatar: string,
	color: string,
	extranet: boolean,
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
	writingList: WritingListItem[],
	muteList: number[],
	textareaMessage: string,
	quoteId: number,
	owner: number,
	entityType: string,
	entityId: string,
	dateCreate: Date | null,
	public: {
		code: string,
		link: string
	},
	inited: boolean,
	loading: boolean,
	hasPrevPage: boolean,
	hasNextPage: boolean,
	diskFolderId: number,
	role: RoleItem,
	permissions: Object<ActionGroupItem, RoleItem>
};
