import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

import type { ImModelMessage } from 'im.v2.model';

export class EditService
{
	editMessageText(messageId: number, text: string)
	{
		Logger.warn('MessageService: editMessageText', messageId, text);
		const message = this.#getMessage(messageId);
		if (!message)
		{
			return;
		}

		this.#updateMessageModel(messageId, text);

		const payload = {
			data: {
				id: messageId,
				fields: { message: text },
			},
		};

		runAction(RestMethod.imV2ChatMessageUpdate, payload)
			.catch((error) => {
				Logger.error('MessageService: editMessageText error:', error);
			});
	}

	#updateMessageModel(messageId: number, text: string): void
	{
		const message = this.#getMessage(messageId);
		const isEdited = message.viewedByOthers;

		Core.getStore().dispatch('messages/update', {
			id: messageId,
			fields: {
				text,
				isEdited,
			},
		});
	}

	#getMessage(messageId: number): ImModelMessage | null
	{
		return Core.getStore().getters['messages/getById'](messageId);
	}
}
