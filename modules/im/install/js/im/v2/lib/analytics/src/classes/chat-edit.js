import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import { AnalyticsEvent, AnalyticsTool, AnalyticsSubSection, AnalyticsSection } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getCollabId } from '../helpers/get-collab-id';
import { getUserType } from '../helpers/get-user-type';

import type { ImModelChat } from 'im.v2.model';

export class ChatEdit
{
	onOpenForm(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickEdit,
			c_section: AnalyticsSection.sidebar,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		};

		if (chat.type === ChatType.collab)
		{
			params.p4 = getCollabId(chat.chatId);
		}

		sendData(params);
	}

	onSubmitForm(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			c_section: AnalyticsSection.editor,
			event: AnalyticsEvent.submitEdit,
			p1: `chatType_${chat.type}`,
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
