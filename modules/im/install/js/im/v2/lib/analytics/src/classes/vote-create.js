import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getUserType } from '../helpers/get-user-type';
import { getCollabId } from '../analytics';
import { AnalyticsTool, AnalyticsEvent } from '../const';

import type { AnalyticsOptions } from 'ui.analytics';
import type { ImModelChat } from 'im.v2.model';

export class Vote
{
	getSerializedParams(dialogId: string): string
	{
		const options = this.getAnalyticsOptions(dialogId);
		const queryParams = Object.entries(options).map(([optionName, optionValue]) => {
			return `st[${optionName}]=${encodeURIComponent(optionValue)}`;
		});

		return queryParams.join('&');
	}

	getAnalyticsOptions(dialogId: string): Partial<AnalyticsOptions>
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);
		const chatType = chat.type;
		const options = {
			tool: AnalyticsTool.im,
			event: AnalyticsEvent.clickCreatePoll,
			category: getCategoryByChatType(chatType),
			p1: `chatType_${chatType}`,
			p2: getUserType(),
			p5: `chatId_${chat.chatId}`,
		};

		if (chatType === ChatType.comment)
		{
			const parentChat = Core.getStore().getters['chats/getByChatId'](chat.parentChatId);
			options.p1 = `chatType_${parentChat.type}`;
			options.p4 = `parentChatId_${chat.parentChatId}`;
		}

		if (chatType === ChatType.collab)
		{
			options.p4 = getCollabId(chat.chatId);
		}

		return options;
	}
}
