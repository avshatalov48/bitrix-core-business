import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import { AnalyticsEvent, AnalyticsTool, AnalyticsSection } from '../const';
import { getCollabId } from '../helpers/get-collab-id';
import { getUserType } from '../helpers/get-user-type';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';

import type { ImModelChat } from 'im.v2.model';

export class UserAdd
{
	onChatSidebarClick(dialogId: string)
	{
		this.#onAddUserClick(dialogId, AnalyticsSection.chatSidebar);
	}

	onChatHeaderClick(dialogId: string)
	{
		this.#onAddUserClick(dialogId, AnalyticsSection.chatHeader);
	}

	#onAddUserClick(dialogId: string, element: AnalyticsSection.chatSidebar | AnalyticsSection.chatHeader)
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
