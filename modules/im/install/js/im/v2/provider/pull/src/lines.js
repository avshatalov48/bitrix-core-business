import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';

import type { MessageAddParams, ReadMessageParams, UnreadMessageParams } from './types/message';

export class LinesPullHandler
{
	constructor()
	{
		this.store = Core.getStore();
	}

	getModuleId(): string
	{
		return 'im';
	}

	handleMessageChat(params: MessageAddParams)
	{
		this.updateUnloadedLinesCounter(params);
	}

	handleReadMessageChat(params: ReadMessageParams)
	{
		this.updateUnloadedLinesCounter(params);
	}

	handleUnreadMessageChat(params: UnreadMessageParams)
	{
		this.updateUnloadedLinesCounter(params);
	}

	updateUnloadedLinesCounter(params: {
		dialogId: string,
		chatId: number,
		counter: number,
		lines: ?Object<string, any>,
	})
	{
		const { dialogId, chatId, counter, lines } = params;
		if (!lines || Type.isUndefined(counter))
		{
			return;
		}

		Logger.warn('LinesPullHandler: updateUnloadedLinesCounter:', { dialogId, chatId, counter });
		this.store.dispatch('recent/setUnloadedLinesCounters', { [chatId]: counter });
	}
}
