import type { reactionType as Reaction } from 'ui.reactions-select';

import type { JsonObject } from 'main.core';
import type { MessageStatus } from 'im.v2.const';

export type RawTariffRestrictions = {
	isHistoryLimitExceeded: boolean,
};

export type RawMessagesAutoDeleteConfig = {
	delay: number,
	chatId: number,
};

export type RawMessage = {
	author_id: number,
	chat_id: number,
	date: string,
	id: number,
	isSystem: boolean,
	params: Object<string, any>,
	replaces: [],
	text: string,
	unread: boolean,
	uuid: string | null,
	viewed: boolean,
	viewedByOthers: boolean
};

export type RawCommentInfo = {
	chatId: number,
	lastUserIds: number[],
	messageCount: 0,
	messageId: number,
	isUserSubscribed: boolean,
};

export type RawCollabInfo = {
	collabId: number,
	guestCount: number,
	tasks: RawCollabEntityInfo,
	files: RawCollabEntityInfo,
	calendar: RawCollabEntityInfo,
};

type RawCollabEntityInfo = {
	counter: number,
	url: string,
};

export type RawChat = {
	avatar: string,
	color: string,
	counter: number,
	dateCreate: string,
	description: string,
	dialogId: string,
	diskFolderId: number,
	entityData1: string,
	entityData2: string,
	entityData3: string,
	entityId: string,
	entityType: string,
	extranet: boolean,
	id: number,
	lastId: number,
	lastMessageId: number,
	lastMessageViews: {
		countOfViewers: number,
		firstViewers: Array<{
			date: string,
			userId: number,
			userName: string
		}>,
		messageId: number
	},
	managerList: number[],
	markedId: number,
	messageCount: number,
	messageType: string,
	muteList: number[],
	name: string,
	owner: number,
	public: string,
	restrictions: RawRestrictions,
	role: string,
	type: string,
	unreadId: number,
	userCounter: number,
	backgroundId: string | null,
	textFieldEnabled: boolean,
};

export type RawRecentChat = {
	avatar: string,
	color: string,
	date_create: string,
	entity_data_1: string,
	entity_data_2: string,
	entity_data_3: string,
	entity_id: string,
	entity_type: string,
	extranet: boolean,
	id: number,
	manager_list: number[],
	message_type: string,
	mute_list: number[],
	name: string,
	owner: number,
	parent_chat_id: number,
	parent_message_id: number,
	permissions: RawPermissions,
	restrictions: RawRestrictions,
	role: string,
	type: string,
	user_counter: number,
	background_id: string | null,
	text_field_enabled: boolean,
};

type RawRestrictions = {
	avatar: boolean,
	call: boolean,
	extend: boolean,
	leave: boolean,
	leaveOwner: boolean,
	mute: boolean,
	rename: boolean,
	send: boolean,
	userList: boolean,
};
type RawPermissions = {
	can_post: string,
	manage_messages: string,
	manage_settings: string,
	manage_ui: string,
	manage_users_add: string,
	manage_users_delete: string,
};

export type RawFile = {
	authorId: number,
	authorName: string,
	chatId: number,
	date: string,
	extension: string,
	id: number,
	image: boolean,
	isTranscribable: boolean,
	name: string,
	progress: number,
	size: number,
	status: string,
	type: string,
	urlDownload: string,
	urlPreview: string,
	urlShow: string,
	viewerAttrs: {
		actions: string,
		imChatId: number,
		objectId: string,
		src: string,
		title: string,
		viewer: null,
		viewerGroupBy: string,
		viewerType: string
	}
};

export type RawPin = {
	id: number,
	messageId: number,
	chatId: number,
	authorId: number,
	dateCreate: string,
	message: RawMessage
};

export type RawReaction = {
	messageId: number,
	reactionCounters: {[reactionType: string]: number},
	reactionUsers: {[reactionType: string]: number[]},
	ownReactions?: Array<$Values<typeof Reaction>>
};

export type RawUser = {
	absent: false | string,
	active: boolean,
	avatar: string,
	avatarHr: string,
	birthday: string,
	bot: boolean,
	color: string,
	connector: boolean,
	departments: number[],
	desktopLastDate: false | string,
	externalAuthId: string,
	extranet: boolean,
	firstName: string,
	gender: 'M' | 'F',
	id: number,
	idle: false | string,
	lastActivityDate: false | string,
	lastName: string,
	mobileLastDate: false | string,
	name: string,
	network: boolean,
	phones: false | number[],
	status: string,
	workPosition: string
};

export type RawShortUser = {
	id: number,
	name: string,
	avatar: string
};

type RawPrompt = {
	code: string,
	promptType: string,
	text: string,
	title: string
};

export type RawCopilotRole = {
	code: string,
	name: string,
	desc: string,
	default: boolean,
	avatarId: number,
	prompts: RawPrompt[],
};

export type RawCopilot = {
	chats: {[string]: string},
	messages: {[number]: string},
	recommendedRoles?: string[],
	aiProvider?: string,
	roles: {[string]: RawCopilotRole},
}

export type RawLegacyRecentItem = {
	id: string, // dialogId
	chat_id: number,
	chat: RawRecentChat,
	user: RawUser,
	message: {
		attach: boolean,
		author_id: number,
		date: string,
		file: boolean | {},
		id: number,
		temporaryId?: string,
		status: $Keys<typeof MessageStatus>,
		text: string,
		uuid: string,
	},
	type: 'user' | 'chat',
	title: string,
	counter: number,
	last_id: number,
	date_update: string,
	date_last_activity: string,
	avatar: { url: string, color: string },
	pinned: boolean,
	unread: boolean,
	has_reminder: boolean,
	options: {},
};

export type RecentItemType = 'chat' | 'user';

export type RawRecentItem = {
	chatId: number,
	dialogId: string,
	type: RecentItemType,
	counter: number,
	dateLastActivity: string,
	dateUpdate: string,
	invited: Boolean | {
		originatorId: number,
		canResend: boolean,
	},
	lastReadMessageId: number,
	messageId: number,
	options: JsonObject,
	pinned: boolean,
	unread: boolean,
};
