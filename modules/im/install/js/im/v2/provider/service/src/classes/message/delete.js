import { EventEmitter } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';

import { Utils } from 'im.v2.lib.utils';
import { RestMethod, EventType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { Core } from 'im.v2.application.core';

import type { ImModelChat, ImModelMessage, ImModelRecentItem } from 'im.v2.model';

export class DeleteService
{
	#store: Store;
	#chatId: number;

	constructor(chatId: number)
	{
		this.#chatId = chatId;
		this.#store = Core.getStore();
	}

	async deleteMessage(messageId: number | string)
	{
		Logger.warn('MessageService: deleteMessage', messageId);

		if (Utils.text.isUuidV4(messageId))
		{
			this.#deleteTemporaryMessage(messageId);

			return;
		}

		this.#sendDeleteEvent(messageId);

		const message: ImModelMessage = this.#store.getters['messages/getById'](messageId);
		if (message.viewedByOthers)
		{
			await this.#shallowMessageDelete(message);
		}
		else
		{
			await this.#completeMessageDelete(message);
		}
	}

	#shallowMessageDelete(message: ImModelMessage): Promise
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

		const dialog: ImModelChat = this.#store.getters['chats/getByChatId'](this.#chatId);
		if (message.id === dialog.lastMessageId)
		{
			this.#store.dispatch('recent/update', {
				id: dialog.dialogId,
				fields: {
					message: { text: '' },
				},
			});
		}

		return this.#deleteMessageOnServer(message.id);
	}

	#completeMessageDelete(message: ImModelMessage): Promise
	{
		const dialog: ImModelChat = this.#store.getters['chats/getByChatId'](this.#chatId);
		const previousMessage: ImModelMessage = this.#store.getters['messages/getPreviousMessage']({
			messageId: message.id,
			chatId: dialog.chatId,
		});
		if (message.id === dialog.lastMessageId)
		{
			this.#updateLastMessageInRecent(message.id, dialog.dialogId);

			const newLastId = previousMessage ? previousMessage.id : 0;
			this.#store.dispatch('chats/update', {
				dialogId: dialog.dialogId,
				fields: {
					lastMessageId: newLastId,
					lastId: newLastId,
				},
			});
			this.#store.dispatch('chats/clearLastMessageViews', {
				dialogId: dialog.dialogId,
			});
		}

		this.#store.dispatch('messages/delete', {
			id: message.id,
		});

		return this.#deleteMessageOnServer(message.id);
	}

	#deleteMessageOnServer(messageId: number): Promise
	{
		return runAction(RestMethod.imV2ChatMessageDelete, {
			data: { id: messageId },
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('MessageService: deleteMessage error:', error);
		});
	}

	#deleteTemporaryMessage(messageId: string)
	{
		const chat: ImModelChat = this.#store.getters['chats/getByChatId'](this.#chatId);
		const recentItem: ImModelRecentItem = this.#store.getters['recent/get'](chat.dialogId);
		if (recentItem.message.id === messageId)
		{
			this.#updateLastMessageInRecent(messageId, chat.dialogId);
		}

		this.#store.dispatch('messages/delete', {
			id: messageId,
		});
	}

	#updateLastMessageInRecent(messageId: number | string, dialogId: string)
	{
		const previousMessage: ImModelMessage = this.#store.getters['messages/getPreviousMessage']({
			messageId,
			chatId: this.#chatId,
		});
		let updatedMessage = { text: '' };
		if (previousMessage)
		{
			updatedMessage = previousMessage;
		}

		this.#store.dispatch('recent/update', {
			id: dialogId,
			fields: {
				message: updatedMessage,
				dateUpdate: new Date(),
			},
		});
	}

	#sendDeleteEvent(messageId: number)
	{
		EventEmitter.emit(EventType.dialog.onMessageDeleted, { messageId });
	}
}
