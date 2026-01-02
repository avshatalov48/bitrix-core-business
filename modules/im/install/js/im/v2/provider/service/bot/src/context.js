import { EventEmitter } from 'main.core.events';

import { runAction } from 'im.v2.lib.rest';
import { EventType, RestMethod } from 'im.v2.const';

import type { JsonObject } from 'main.core';

export class BotContextService
{
	scheduleContextRequest(dialogId: string, context: JsonObject): void
	{
		const eventHandler = (event) => {
			const { dialogId: eventDialogId } = event.getData();
			if (eventDialogId !== dialogId)
			{
				return;
			}

			EventEmitter.unsubscribe(EventType.dialog.onDialogInited, eventHandler);
			void this.#sendBotContext(dialogId, context);
		};

		EventEmitter.subscribe(EventType.dialog.onDialogInited, eventHandler);
	}

	#sendBotContext(dialogId: string, context: JsonObject): Promise
	{
		return runAction(RestMethod.imV2ChatBotSendContext, {
			data: { dialogId, context },
		}).catch(([error]) => {
			console.error('BotContextService: send context error', error);
		});
	}
}
