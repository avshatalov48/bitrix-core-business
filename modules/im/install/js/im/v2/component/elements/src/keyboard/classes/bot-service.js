import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

import type { CustomCommandEvent } from '../types/events';

export class BotService
{
	#messageId: string | number;
	#dialogId: string;

	constructor(params: { messageId: string | number, dialogId: string })
	{
		const { messageId, dialogId } = params;
		this.#messageId = messageId;
		this.#dialogId = dialogId;
	}

	sendCommand(event: CustomCommandEvent): void
	{
		const { botId, command, payload } = event;
		Core.getRestClient().callMethod(RestMethod.imMessageCommand, {
			MESSAGE_ID: this.#messageId,
			DIALOG_ID: this.#dialogId,
			BOT_ID: botId,
			COMMAND: command,
			COMMAND_PARAMS: payload,
		})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('BotService: error sending command:', error);
			});
	}
}
