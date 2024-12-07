import { EventEmitter } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';

import { Utils } from 'im.v2.lib.utils';
import { RestMethod, EventType, ChatType } from 'im.v2.const';
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
		if (this.#canDeleteCompletely(message))
		{
			void this.#completeMessageDelete(message);

			return;
		}

		void this.#shallowMessageDelete(message);
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

		return this.#deleteMessageOnServer(message.id);
	}

	#canDeleteCompletely(message: ImModelMessage): boolean
	{
		const alwaysCompleteDeleteChats = [ChatType.channel, ChatType.openChannel, ChatType.generalChannel];
		const neverCompleteDeleteChats = [ChatType.comment];

		const chat = this.#getChat();
		if (alwaysCompleteDeleteChats.includes(chat.type))
		{
			return true;
		}

		if (neverCompleteDeleteChats.includes(chat.type))
		{
			return false;
		}

		return !message.viewedByOthers;
	}

	#completeMessageDelete(message: ImModelMessage): Promise
	{
		const chat = this.#getChat();
		if (message.id === chat.lastMessageId)
		{
			const newLastId = this.#getPreviousMessageId(message.id);
			this.#updateRecentForCompleteDelete(newLastId);
			this.#updateChatForCompleteDelete(newLastId);
		}

		this.#store.dispatch('messages/delete', {
			id: message.id,
		});

		return this.#deleteMessageOnServer(message.id);
	}

	#updateRecentForCompleteDelete(newLastId: number)
	{
		const chat = this.#getChat();
		if (!newLastId)
		{
			this.#store.dispatch('recent/delete', { id: chat.dialogId });

			return;
		}

		this.#store.dispatch('recent/update', {
			id: chat.dialogId,
			fields: { messageId: newLastId },
		});
	}

	#updateChatForCompleteDelete(newLastId)
	{
		const chat = this.#getChat();

		this.#store.dispatch('chats/update', {
			dialogId: chat.dialogId,
			fields: {
				lastMessageId: newLastId,
				lastId: newLastId,
			},
		});
		this.#store.dispatch('chats/clearLastMessageViews', {
			dialogId: chat.dialogId,
		});
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
		const chat = this.#getChat();
		const recentItem: ImModelRecentItem = this.#store.getters['recent/get'](chat.dialogId);
		if (recentItem.messageId === messageId)
		{
			const newLastId = this.#getPreviousMessageId(messageId);
			this.#store.dispatch('recent/update', {
				id: chat.dialogId,
				fields: { messageId: newLastId },
			});
		}

		this.#store.dispatch('messages/delete', {
			id: messageId,
		});
	}

	#getPreviousMessageId(messageId: number): number
	{
		const previousMessage: ImModelMessage = this.#store.getters['messages/getPreviousMessage']({
			messageId,
			chatId: this.#chatId,
		});

		return previousMessage?.id ?? 0;
	}

	#sendDeleteEvent(messageId: number)
	{
		EventEmitter.emit(EventType.dialog.onMessageDeleted, { messageId });
	}

	#getChat(): ImModelChat
	{
		return this.#store.getters['chats/getByChatId'](this.#chatId);
	}
}
