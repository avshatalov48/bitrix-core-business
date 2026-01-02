import { Core } from 'im.v2.application.core';
import { sendData } from 'ui.analytics';

import { AnalyticsEvent, AnalyticsTool, PSEUDO_CHAT_TYPE_FOR_NOTES } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { isNotes } from '../helpers/is-notes';

import type { ImModelChat } from 'im.v2.model';

export class ChatPins
{
	onPin(dialogId: string): void
	{
		if (!isNotes(dialogId))
		{
			return;
		}

		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.pinChat,
			p1: `chatType_${PSEUDO_CHAT_TYPE_FOR_NOTES}`,
		};

		sendData(params);
	}
}
