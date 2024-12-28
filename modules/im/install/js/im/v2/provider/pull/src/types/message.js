import type { reactionType as Reaction } from 'ui.reactions-select';

import { CounterType } from 'im.v2.const';

import type { RawChat, RawFile, RawUser, RawMessage, RawMultidialog, RawLines } from './common';

type CounterTypeItem = $Values<typeof CounterType>;

export type MessageAddParams = {
	chat?: {[chatId: string]: RawChat} | [],
	chatId: number,
	counter: number,
	dialogId: string,
	files: {[fileId: string]: RawFile} | [],
	lines: RawLines | null,
	message: RawMessage,
	notify: boolean,
	userBlockChat: {[chatId: string]: {[userId: string]: boolean}} | [],
	userInChat: {[chatId: string]: number[]} | [],
	users: {[userId: string]: RawUser} | null,
};

export type MessageUpdateParams = {
	chatId: number,
	dialogId: string,
	id: number,
	params: {
		IS_EDITED: 'Y' | 'N'
	},
	senderId: number,
	text: string,
	textLegacy: string,
	type: string
};

export type MessageDeleteParams = {
	chatId: number,
	dialogId: string,
	id: number,
	params: {
		IS_DELETED: 'Y' | 'N'
	},
	senderId: number,
	text: string,
	type: string
};

export type MessageDeleteCompleteParams = {
	chatId: number,
	dialogId: string,
	counter: number,
	unread: boolean,
	muted: boolean,
	id: number,
	lastMessageViews?: {
		countOfViewers: number,
		firstViewers: Array<{
			id: number,
			name: string,
			avatar: string
		}>,
		messageId: number
	},
	newLastMessage?: RawMessage,
	params: Object<string, any>,
	senderId: number,
	text: string,
	type: string,
	counterType: CounterTypeItem
};

export type ReadMessageParams = {
	chatId: number,
	counter: number,
	dialogId: string,
	lastId: number,
	lines: boolean,
	muted: boolean,
	unread: boolean,
	viewedMessages: number[],
	type: string,
	parentChatId: number,
	counterType: CounterTypeItem
};

export type MessageParams = {
	chat?: {[chatId: string]: RawChat} | [],
	chatId: number,
	counter: number,
	dateLastActivity: string,
	dialogId: string,
	files: {[fileId: string]: RawFile} | [],
	lines: boolean,
	message: RawMessage,
	multidialog: RawMultidialog,
	notify: boolean,
	userBlockChat: {[chatId: string]: {[userId: string]: boolean}} | [],
	userInChat: {[chatId: string]: number[]} | [],
	users: {[userId: string]: RawUser} | null,
	counterType: CounterTypeItem
};

export type MessageChatParams = {
	chat?: {[chatId: string]: RawChat} | [],
	chatId: number,
	counter: number,
	dateLastActivity: string,
	dialogId: string,
	files: {[fileId: string]: RawFile} | [],
	lines: boolean,
	message: RawMessage,
	multidialog: RawMultidialog,
	notify: boolean,
	userBlockChat: {[chatId: string]: {[userId: string]: boolean}} | [],
	userInChat: {[chatId: string]: number[]} | [],
	users: {[userId: string]: RawUser} | null,
	counterType: CounterTypeItem
};

export type ReadMessageChatParams = {
	chatId: number,
	counter: number,
	dialogId: string,
	lastId: number,
	lines: boolean,
	muted: boolean,
	unread: boolean,
	viewedMessages: number[],
	type: string,
	parentChatId: number,
	counterType: CounterTypeItem
};

export type UnreadMessageParams = {
	chatId: number,
	counter: number,
	dialogId: string,
	lines: boolean,
	counterType: CounterTypeItem
};

export type ReadMessageOpponentParams = {
	chatId: number,
	chatMessageStatus: string,
	date: string,
	dialogId: string,
	lastId: number,
	userId: number,
	userName: string,
	viewedMessages: number[]
};

export type PinAddParams = {
	files: {[fileId: string]: RawFile} | [],
	pin: {
		authorId: number,
		chatId: number,
		dateCreate: string,
		id: number,
		messageId: number
	},
	additionalMessages: RawMessage[],
	reminders: Object | [],
	users: RawUser[]
};

export type PinDeleteParams = {
	chatId: number,
	linkId: number,
	messageId: number
};

export type AddReactionParams = {
	actualReactions: {
		reaction: RawReaction,
		usersShort: ReactionUser[]
	},
	reaction: ReactionType,
	userId: number,
	dialogId: string
};

export type DeleteReactionParams = {
	actualReactions: {
		reaction: RawReaction,
		usersShort: ReactionUser[]
	},
	reaction: ReactionType,
	userId: number
};

type ReactionType = $Values<typeof Reaction>;

type RawReaction = {
	messageId: number,
	reactionCounters: {[reactionType: string]: number},
	reactionUsers: {[reactionType: string]: number[]},
	ownReactions?: ReactionType[]
};

type ReactionUser = {
	id: number,
	name: string,
	avatar: string
};
