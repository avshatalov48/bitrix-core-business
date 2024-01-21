import { ChatType } from 'im.v2.const';
import { RecentPullHandler } from './recent';

import type { JsonObject } from 'main.core';
import type { MessageAddParams } from './types/message';


export class CopilotRecentHandler extends RecentPullHandler
{
	setRecentItem(newRecentItem: JsonObject)
	{
		this.store.dispatch('recent/setCopilot', newRecentItem);
	}

	checkChatType(params: MessageAddParams): boolean
	{
		const newMessageChatType = params.chat[params.chatId]?.type;

		return newMessageChatType === ChatType.copilot;
	}
}
