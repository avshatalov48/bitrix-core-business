import { sendData } from 'ui.analytics';

import { ChatType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

import { AnalyticsEvent, AnalyticsSection, AnalyticsTool } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getChatType } from '../helpers/get-chat-type';
import { getCollabId } from '../helpers/get-collab-id';
import { getUserType } from '../helpers/get-user-type';

import type { ImModelChat } from 'im.v2.model';

export class AttachMenu
{
	onOpenUploadMenu(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);
		const chatType: $Values<ChatType> = getChatType(chat);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickAttach,
			c_section: AnalyticsSection.chatTextarea,
			p1: `chatType_${chatType}`,
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
