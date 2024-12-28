import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import { AnalyticsEvent, AnalyticsTool, CreateChatContext } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getUserType } from '../helpers/get-user-type';

export class ChatCreate
{
	onStartClick(type: $Values<typeof ChatType>)
	{
		const currentLayout = Core.getStore().getters['application/getLayout'].name;

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(type),
			event: AnalyticsEvent.clickCreateNew,
			type,
			c_section: `${currentLayout}_tab`,
			p2: getUserType(),
		});
	}

	onCollabEmptyStateCreateClick()
	{
		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(ChatType.collab),
			event: AnalyticsEvent.clickCreateNew,
			type: ChatType.collab,
			c_section: CreateChatContext.collabEmptyState,
			p2: getUserType(),
		});
	}
}
