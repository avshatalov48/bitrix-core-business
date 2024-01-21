import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

import type { ImModelChat, ImModelMessage } from 'im.v2.model';

export class EditService
{
	#chatId: number;

	constructor(chatId: number)
	{
		this.#chatId = chatId;
	}

	editMessageText(messageId: number, text: string)
	{
		Logger.warn('MessageService: editMessageText', messageId, text);
		const message = this.#getMessage(messageId);
		if (!message)
		{
			return;
		}

		this.#updateMessageModel(messageId, text);
		this.#updateRecentModel(messageId, text);

		runAction(RestMethod.imV2ChatMessageUpdate, {
			data: {
				id: messageId,
				fields: { message: text },
			},
		})
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

	#updateRecentModel(messageId: number, text: string): void
	{
		const dialog = this.#getChat();
		if (messageId !== dialog.lastMessageId)
		{
			return;
		}

		Core.getStore().dispatch('recent/update', {
			id: dialog.dialogId,
			fields: {
				message: {
					text,
				},
				dateUpdate: new Date(),
			},
		});
	}

	#getChat(): ImModelChat | null
	{
		return Core.getStore().getters['chats/getByChatId'](this.#chatId);
	}

	#getMessage(messageId: number): ImModelMessage | null
	{
		return Core.getStore().getters['messages/getById'](messageId);
	}
}
