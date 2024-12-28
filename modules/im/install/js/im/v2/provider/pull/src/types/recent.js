import type { RawChat, RawFile, RawUser, RawMessage } from './common';

export type RecentUpdateParams = {
	additionalMessages: RawMessage[],
	chat: RawChat,
	counter: number,
	lastActivityDate: string,
	messages: RawMessage[],
	files: RawFile[],
	users: RawUser[],
};

export type UserShowInRecentParams = {
	items: UserShowInRecentItem[],
};

type UserShowInRecentItem = {
	user: RawUser,
	date: string,
};
