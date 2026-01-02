import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import { AnalyticsEvent, AnalyticsTool, AnalyticsSection } from '../const';
import { getCollabId } from '../helpers/get-collab-id';
import { getUserType } from '../helpers/get-user-type';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';

import type { ImModelChat } from 'im.v2.model';

type SelectUserParams = {
	dialogId: string,
	position: number,
};

type SelectUserWithSourceParams = SelectUserParams & {
	source: $Values<typeof SelectUserSource>,
};

const SelectUserSource = Object.freeze({
	recent: 'recent',
	searchResult: 'search_result',
});

export class UserAdd
{
	#hasSearchedBefore: boolean = false;

	onChatSidebarClick(dialogId: string): void
	{
		this.#onAddUserClick(dialogId, AnalyticsSection.chatSidebar);
	}

	onChatHeaderClick(dialogId: string): void
	{
		this.#onAddUserClick(dialogId, AnalyticsSection.chatHeader);
	}

	onStartSearch({ dialogId }: { dialogId: string }): void
	{
		if (this.#hasSearchedBefore)
		{
			return;
		}
		this.#hasSearchedBefore = true;

		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.startSearch,
			c_section: AnalyticsSection.userAdd,
			p1: `chatType_${chat.type}`,
			p2: getUserType(),
		});
	}

	onClosePopup(): void
	{
		this.#hasSearchedBefore = false;
	}

	onSelectUserFromRecent({ dialogId, position }: SelectUserParams): void
	{
		this.#onSelectUser({
			dialogId,
			position,
			source: SelectUserSource.recent,
		});
	}

	onSelectUserFromSearchResult({ dialogId, position }: SelectUserParams): void
	{
		this.#onSelectUser({
			dialogId,
			position,
			source: SelectUserSource.searchResult,
		});
	}

	#onSelectUser({ dialogId, position, source }: SelectUserWithSourceParams): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.selectUser,
			type: source,
			c_section: AnalyticsSection.userAdd,
			p1: `chatType_${chat.type}`,
			p2: getUserType(),
			p3: `position_${position}`,
		});
	}

	#onAddUserClick(dialogId: string, element: AnalyticsSection.chatSidebar | AnalyticsSection.chatHeader): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickAddUser,
			c_section: element,
			p2: getUserType(),
			p5: `chatId_${chat.chatId}`,
		};

		if (chat.type === ChatType.collab)
		{
			params.p4 = getCollabId(chat.chatId);
		}

		sendData(params);
	}
}
