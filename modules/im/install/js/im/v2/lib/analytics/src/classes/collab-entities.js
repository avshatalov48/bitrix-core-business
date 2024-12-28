import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { ChatType, CollabEntityType } from 'im.v2.const';

import { AnalyticsEvent, AnalyticsTool, AnalyticsSection, AnalyticsCategory } from '../const';
import { getCollabId } from '../helpers/get-collab-id';
import { getUserType } from '../helpers/get-user-type';

import type { ImModelChat } from 'im.v2.model';

const EntityToEventMap = {
	[CollabEntityType.tasks]: AnalyticsEvent.openTasks,
	[CollabEntityType.calendar]: AnalyticsEvent.openCalendar,
	[CollabEntityType.files]: AnalyticsEvent.openFiles,
};

export class CollabEntities
{
	onClick(dialogId: string, type: $Values<typeof CollabEntityType>)
	{
		const event = EntityToEventMap[type];
		if (!event)
		{
			return;
		}

		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		const params = {
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.collab,
			event,
			c_section: AnalyticsSection.chatHeader,
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
