import { Core } from 'im.v2.application.core';
import { ChatType, UserType } from 'im.v2.const';

import { getRecentListItems } from './get-recent-items';

import type { SearchResultItem } from '../types/types';
import type { ImModelChat, ImModelUser } from 'im.v2.model';

const MAX_USERS_IN_RECENT_SEARCH_LIST = 50;

export function getUsersFromRecentItems({ withFakeUsers }: {withFakeUsers: boolean}): SearchResultItem[]
{
	return getRecentListItems({ withFakeUsers }).filter(({ dialogId }) => {
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);
		const user: ImModelUser = Core.getStore().getters['users/get'](dialogId, true);

		return chat.type === ChatType.user && user.type !== UserType.bot && user.id !== Core.getUserId();
	}).slice(0, MAX_USERS_IN_RECENT_SEARCH_LIST);
}
