import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';

import { UpdateCollabEntityCounterParams, UpdateCollabGuestCountParams } from '../../types/collab';

export class CollabPullHandler
{
	handleUpdateCollabEntityCounter(params: UpdateCollabEntityCounterParams)
	{
		Logger.warn('CollabPullHandler: handleUpdateCollabEntityCounter', params);
		const { chatId, counter, entity } = params;
		void Core.getStore().dispatch('chats/collabs/setCounter', { chatId, entity, counter });
	}

	handleUpdateCollabGuestCount(params: UpdateCollabGuestCountParams)
	{
		Logger.warn('CollabPullHandler: handleUpdateCollabGuestCount', params);
		const { chatId, guestCount } = params;
		void Core.getStore().dispatch('chats/collabs/setGuestCount', { chatId, guestCount });
	}
}
