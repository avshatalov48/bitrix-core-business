import type { RawCopilot, RawMessagesAutoDeleteConfig, RawLegacyRecentItem, RawUser } from 'im.v2.provider.service.types';

export type RecentRestResult = {
	birthdayList: RawUser[],
	hasMore: boolean,
	hasMorePages: boolean,
	items: RawLegacyRecentItem[],
	copilot?: RawCopilot,
	messagesAutoDeleteConfigs: RawMessagesAutoDeleteConfig[],
};
