import { Core } from 'im.v2.application.core';

import type { ImModelMessage } from 'im.v2.model';

export function getRecentItemDate(dialogId: string): string
{
	const message: ImModelMessage = Core.getStore().getters['recent/getMessage'](dialogId);
	if (!message)
	{
		return '';
	}

	return message.date.toISOString();
}
