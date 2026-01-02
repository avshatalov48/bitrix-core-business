import type {
	RawChat,
	RawFile,
	RawMessage,
	RawMessagesAutoDeleteConfig,
	RawRecentItem,
	RawUser,
	RawCopilot,
} from 'im.v2.provider.service.types';

export type BaseRecentQueryParams = {
	limit: number,
	filter: Record,
};

export type RecentRestResult = {
	hasNextPage: boolean,
	chats: RawChat[],
	files: RawFile[],
	recentItems: RawRecentItem[],
	users: RawUser[],
	messages: RawMessage[],
	additionalMessages: RawMessage[],
	messagesAutoDeleteConfigs: RawMessagesAutoDeleteConfig[],
	copilot: null | RawCopilot,
};
