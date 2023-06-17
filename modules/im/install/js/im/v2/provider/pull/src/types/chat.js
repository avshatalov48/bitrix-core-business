import type {RawUser} from './common';

export type ChatOwnerParams = {
	chatId: number,
	dialogId: string,
	userId: number
};

export type ChatManagersParams = {
	chatId: number,
	dialogId: string,
	list: number[]
};

export type ChatUserAddParams = {
	chatId: number,
	dialogId: string,
	chatTitle: string,
	chatOwner: number,
	chatExtranet: boolean,
	users: {[userId: string]: RawUser},
	newUsers: number[],
	userCount: number
};

export type ChatUserLeaveParams = {
	chatId: number,
	chatTitle: string,
	dialogId: string,
	message: string,
	userCount: number,
	userId: number
};

export type StartWritingParams = {
	dialogId: string,
	userId: number,
	userName: string
};

export type ChatUnreadParams = {
	chatId: number,
	dialogId: string,
	active: boolean,
	muted: boolean,
	counter: number,
	markedId: number | "0",
	lines: boolean
};

export type ChatMuteNotifyParams = {
	chatId: number,
	dialogId: string,
	muted: boolean,
	mute: boolean,
	counter: number,
	lines: boolean,
	unread: boolean
};

export type ChatRenameParams = {
	chatId: number,
	name: string
};

export type ChatAvatarParams = {
	chatId: number,
	avatar: string
};