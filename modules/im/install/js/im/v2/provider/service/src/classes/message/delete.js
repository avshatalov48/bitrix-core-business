import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

import type { ImModelDialog, ImModelMessage } from 'im.v2.model';

export class DeleteService
{
	#store: Store;
	#chatId: number;

	constructor(chatId: number)
	{
		this.#chatId = chatId;
		this.#store = Core.getStore();
	}

	deleteMessage(messageId: number)
	{
		Logger.warn('MessageService: deleteMessage', messageId);
		const message: ImModelMessage = this.#store.getters['messages/getById'](messageId);
		if (message.viewedByOthers)
		{
			this.#shallowMessageDelete(message);
		}
		else
		{
			this.#completeMessageDelete(message);
		}
	}

	#shallowMessageDelete(message: ImModelMessage)
	{
		this.#store.dispatch('messages/update', {
			id: message.id,
			fields: {
				text: '',
				isDeleted: true,
				files: [],
				attach: [],
				replyId: 0,
			},
		});

		const dialog: ImModelDialog = this.#store.getters['dialogues/getByChatId'](this.#chatId);
		if (message.id === dialog.lastMessageId)
		{
			this.#store.dispatch('recent/update', {
				id: dialog.dialogId,
				fields: {
					message: { text: '' },
				},
			});
		}

		this.#deleteMessageOnServer(message.id);
	}

	#completeMessageDelete(message: ImModelMessage)
	{
		const dialog: ImModelDialog = this.#store.getters['dialogues/getByChatId'](this.#chatId);
		const previousMessage: ImModelMessage = this.#store.getters['messages/getPreviousMessage']({
			messageId: message.id,
			chatId: dialog.chatId,
		});
		if (message.id === dialog.lastMessageId)
		{
			let updatedMessage = { text: '' };
			if (previousMessage)
			{
				updatedMessage = previousMessage;
			}
			this.#store.dispatch('recent/update', {
				id: dialog.dialogId,
				fields: {
					message: updatedMessage,
					dateUpdate: new Date(),
				},
			});

			const newLastId = previousMessage ? previousMessage.id : 0;
			this.#store.dispatch('dialogues/update', {
				dialogId: dialog.dialogId,
				fields: {
					lastMessageId: newLastId,
					lastId: newLastId,
				},
			});
			this.#store.dispatch('dialogues/clearLastMessageViews', {
				dialogId: dialog.dialogId,
			});
		}

		this.#store.dispatch('messages/delete', {
			id: message.id,
		});

		this.#deleteMessageOnServer(message.id);
	}

	#deleteMessageOnServer(messageId: number)
	{
		runAction(RestMethod.imV2ChatMessageDelete, {
			data: { id: messageId },
		}).catch((error) => {
			console.error('MessageService: deleteMessage error:', error);
		});
	}
}
