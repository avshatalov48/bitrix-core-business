import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';

import type { MessageAddParams, ReadMessageParams, UnreadMessageParams } from './types/message';
import type { ChatHideParams } from './types/chat';

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

	handleChatHide(params: ChatHideParams)
	{
		this.updateUnloadedLinesCounter({
			dialogId: params.dialogId,
			chatId: params.chatId,
			lines: params.lines,
			counter: 0,
		});
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
		this.store.dispatch('counters/setUnloadedLinesCounters', { [chatId]: counter });
	}
}
