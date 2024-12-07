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
