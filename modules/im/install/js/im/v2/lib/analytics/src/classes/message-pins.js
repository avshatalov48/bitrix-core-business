import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';

import { AnalyticsEvent, AnalyticsTool, MessagePinsTypes } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getChatType } from '../helpers/get-chat-type';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

export class MessagePins
{
	onUnpin({ dialogId, eventParams = {} }: { dialogId: string, eventParams: JsonObject })
	{
		this.onSendData({
			dialogId,
			eventParams: {
				event: AnalyticsEvent.unpinMessage,
				...eventParams,
			},
		});
	}

	onSendData({ dialogId, eventParams }: { dialogId: number, eventParams: JsonObject }): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			p1: `chatType_${getChatType(chat)}`,
			...this.#getEventSpecificParams(eventParams.event, chat.chatId),
			...eventParams,
		};

		sendData(params);
	}

	#getEventSpecificParams(event: string, chatId: number): Record<string, string>
	{
		const pinnedCount: number = Core.getStore().getters['messages/pin/getPinned'](chatId).length;

		if (event === AnalyticsEvent.pinMessage)
		{
			return {
				p3: `pinnedCount_${pinnedCount}`,
				type: pinnedCount > 1 ? MessagePinsTypes.multiple : MessagePinsTypes.single,
			};
		}

		if (event === AnalyticsEvent.unpinMessage)
		{
			return {
				type: pinnedCount > 0 ? MessagePinsTypes.selected : MessagePinsTypes.single,
			};
		}

		return {};
	}
}
