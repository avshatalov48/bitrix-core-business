import { Type } from 'main.core';

import type { SearchConfig, EntitySelectorRequestConfig } from './types/types';

export const EntityId = 'im-recent-v2';
const ContextId = 'IM_CHAT_SEARCH';
const SearchDialogId = 'im-chat-search';

export const getSearchConfig = (searchConfig: SearchConfig): EntitySelectorRequestConfig => {
	const entity = {
		id: EntityId,
		dynamicLoad: true,
		dynamicSearch: true,
		options: prepareConfigOptions(searchConfig),
	};

	return {
		dialog: {
			entities: [
				entity,
			],
			preselectedItems: [],
			clearUnavailableItems: false,
			context: ContextId,
			id: SearchDialogId,
		},
	};
};

const prepareConfigOptions = (searchConfig: SearchConfig) => {
	const options = {
		withChatByUsers: false,
	};

	if (!searchConfig)
	{
		return { ...options, exclude: [] };
	}

	const exclude = [];

	if (Type.isBoolean(searchConfig.chats) && !searchConfig.chats)
	{
		exclude.push('chats');
	}

	if (Type.isBoolean(searchConfig.users) && !searchConfig.users)
	{
		exclude.push('users');
	}

	return { ...options, exclude };
};
