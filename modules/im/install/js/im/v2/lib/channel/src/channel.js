import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';

export const ChannelManager = {
	channelTypes: new Set([ChatType.generalChannel, ChatType.channel, ChatType.openChannel]),

	isChannel(dialogId: string): boolean
	{
		const { type }: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		return ChannelManager.channelTypes.has(type);
	},
};
