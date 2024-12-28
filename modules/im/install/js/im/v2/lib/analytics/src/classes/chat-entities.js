import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import { AnalyticsEvent, AnalyticsTool, AnalyticsSection } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getChatType } from '../helpers/get-chat-type';
import { getCollabId } from '../helpers/get-collab-id';
import { getUserType } from '../helpers/get-user-type';

import type { ImModelChat } from 'im.v2.model';

export class ChatEntities
{
	onCreateTaskFromSidebarClick(dialogId: string)
	{
		this.#onClick({
			dialogId,
			event: AnalyticsEvent.clickCreateTask,
			section: AnalyticsSection.chatSidebar,
		});
	}

	onCreateTaskFromTextareaClick(dialogId: string)
	{
		this.#onClick({
			dialogId,
			event: AnalyticsEvent.clickCreateTask,
			section: AnalyticsSection.chatTextarea,
		});
	}

	onCreateEventFromSidebarClick(dialogId: string)
	{
		this.#onClick({
			dialogId,
			event: AnalyticsEvent.clickCreateEvent,
			section: AnalyticsSection.chatSidebar,
		});
	}

	onCreateEventFromTextareaClick(dialogId: string)
	{
		this.#onClick({
			dialogId,
			event: AnalyticsEvent.clickCreateEvent,
			section: AnalyticsSection.chatTextarea,
		});
	}

	#onClick({ dialogId, event, section })
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event,
			c_section: section,
			p1: `chatType_${getChatType(chat)}`,
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
