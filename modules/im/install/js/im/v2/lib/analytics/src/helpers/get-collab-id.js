import { Core } from 'im.v2.application.core';

import type { ImModelCollabInfo } from 'im.v2.model';

export function getCollabId(chatId: number): ?string
{
	const collabInfo: ImModelCollabInfo = Core.getStore().getters['chats/collabs/getByChatId'](chatId);
	if (!collabInfo)
	{
		return null;
	}

	return `collabId_${collabInfo.collabId}`;
}
