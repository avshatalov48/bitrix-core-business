import { ChatType } from 'im.v2.const';

import { AnalyticsCategory } from '../const';

export function getCategoryByChatType(type: $Values<typeof ChatType>): $Values<typeof AnalyticsCategory>
{
	switch (type)
	{
		case ChatType.channel:
		case ChatType.openChannel:
		case ChatType.comment:
		case ChatType.generalChannel:
			return AnalyticsCategory.channel;
		case ChatType.copilot:
			return AnalyticsCategory.copilot;
		case ChatType.videoconf:
			return AnalyticsCategory.videoconf;
		case ChatType.collab:
			return AnalyticsCategory.collab;
		default:
			return AnalyticsCategory.chat;
	}
}
