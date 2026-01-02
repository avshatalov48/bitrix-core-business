import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { RestMethod, ChatType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { UuidManager } from 'im.v2.lib.uuid';
import { runAction } from 'im.v2.lib.rest';

import type { ImModelChat, ImModelRecentItem } from 'im.v2.model';

type ReadResult = {
	chatId: number,
	counter: number,
	lastId: number,
	viewedMessages: number[]
};

const READ_TIMEOUT = 300;

export class ReadService
{
	#store: Store;
	#restClient: RestClient;

	#messagesToRead: {[chatId: string]: Set<number>} = {};

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	readAll(): void
	{
		Logger.warn('ReadService: readAll');
		void this.#store.dispatch('chats/clearCounters');
		void this.#store.dispatch('recent/clearUnread');

		return this.#restClient.callMethod(RestMethod.imV2ChatReadAll)
			.catch((result: RestResult) => {
				console.error('ReadService: readAll error', result.error());
			});
	}

	readDialog(dialogId: string): void
	{
		Logger.warn('ReadService: readDialog', dialogId);
		void this.#store.dispatch('recent/unread', {
			id: dialogId,
			action: false,
		});
		void this.#store.dispatch('chats/update', {
			dialogId,
			fields: { counter: 0 },
		});
		this.#restClient.callMethod(RestMethod.imV2ChatRead, { dialogId })
			.catch((result: RestResult) => {
				console.error('ReadService: error reading chat', result.error());
			});
	}

	unreadDialog(dialogId: string): void
	{
		Logger.warn('ReadService: unreadDialog', dialogId);
		void this.#store.dispatch('recent/unread', {
			id: dialogId,
			action: true,
		});
		this.#restClient.callMethod(RestMethod.imV2ChatUnread, { dialogId })
			.catch((result: RestResult) => {
				console.error('ReadService: error setting chat as unread', result.error());
				void this.#store.dispatch('recent/unread', { id: dialogId, action: false });
			});
	}

	readMessage(chatId: number, messageId: number): void
	{
		if (!this.#messagesToRead[chatId])
		{
			this.#messagesToRead[chatId] = new Set();
		}
		this.#messagesToRead[chatId].add(messageId);

		clearTimeout(this.readTimeout);
		this.readTimeout = setTimeout(() => {
			Object.entries(this.#messagesToRead).forEach(([rawChatId, messageIds]) => {
				void this.#readMessagesForChat(rawChatId, messageIds);
			});
		}, READ_TIMEOUT);
	}

	async readChatQueuedMessages(chatId: number): void
	{
		if (!this.#messagesToRead[chatId])
		{
			return;
		}

		clearTimeout(this.readTimeout);

		void this.#readMessagesForChat(chatId, this.#messagesToRead[chatId]);
	}

	clearDialogMark(dialogId: string): void
	{
		Logger.warn('ReadService: clear dialog mark', dialogId);
		const dialog: ImModelChat = this.#store.getters['chats/get'](dialogId);
		const recentItem: ImModelRecentItem = this.#store.getters['recent/get'](dialogId);
		if (dialog.markedId === 0 && !recentItem?.unread)
		{
			return;
		}
		void this.#store.dispatch('recent/unread', {
			id: dialogId,
			action: false,
		});
		void this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				markedId: 0,
			},
		});
		this.#restClient.callMethod(RestMethod.imV2ChatRead, {
			dialogId,
			onlyRecent: 'Y',
		}).catch((result: RestResult) => {
			console.error('ReadService: error clearing dialog mark', result.error());
		});
	}

	async #readMessagesForChat(rawChatId: string, messageIds: Set<number>): Promise<boolean>
	{
		const queueChatId = Number.parseInt(rawChatId, 10);
		Logger.warn('ReadService: readMessages', messageIds);
		if (messageIds.size === 0)
		{
			return true;
		}

		const copiedMessageIds = [...messageIds];
		delete this.#messagesToRead[queueChatId];

		const readMessagesCount = await this.#readMessageOnClient(queueChatId, copiedMessageIds);

		Logger.warn('ReadService: readMessage, need to reduce counter by', readMessagesCount);
		await this.#decreaseChatCounter(queueChatId, readMessagesCount);

		const readResult = await this.#readMessageOnServer(queueChatId, copiedMessageIds)
			.catch(([error]) => {
				console.error('ReadService: error reading message', error);
			});

		this.#checkChatCounter(readResult);

		return true;
	}

	#readMessageOnClient(chatId: number, messageIds: number[]): Promise<number>
	{
		const maxMessageId = Math.max(...messageIds);
		const dialog = this.#getDialogByChatId(chatId);
		if (maxMessageId > dialog.lastReadId)
		{
			void this.#store.dispatch('chats/update', {
				dialogId: this.#getDialogIdByChatId(chatId),
				fields: { lastId: maxMessageId },
			});
		}

		return this.#store.dispatch('messages/readMessages', {
			chatId,
			messageIds,
		});
	}

	#decreaseCommentCounter(chatId: number, readMessagesCount: number): Promise
	{
		const chat = this.#getDialogByChatId(chatId);
		let newCounter = chat.counter - readMessagesCount;
		if (newCounter < 0)
		{
			newCounter = 0;
		}

		const counters = {
			[chat.parentChatId]: {
				[chatId]: newCounter,
			},
		};

		return Core.getStore().dispatch('counters/setCommentCounters', counters);
	}

	#decreaseChatCounter(chatId: number, readMessagesCount: number): Promise
	{
		const chat = this.#getDialogByChatId(chatId);
		if (chat.type === ChatType.comment)
		{
			return this.#decreaseCommentCounter(chatId, readMessagesCount);
		}

		let newCounter = chat.counter - readMessagesCount;
		if (newCounter < 0)
		{
			newCounter = 0;
		}

		return this.#store.dispatch('chats/update', {
			dialogId: this.#getDialogIdByChatId(chatId),
			fields: { counter: newCounter },
		});
	}

	#readMessageOnServer(chatId: number, messageIds: number[]): Promise
	{
		Logger.warn('ReadService: readMessages on server', messageIds);

		return runAction(RestMethod.imV2ChatMessageRead, {
			data: {
				chatId,
				ids: messageIds,
				actionUuid: UuidManager.getInstance().getActionUuid(),
			},
		});
	}

	#checkChatCounter(readResult: ReadResult)
	{
		if (!readResult)
		{
			return;
		}

		const { chatId, counter } = readResult;

		const dialog = this.#getDialogByChatId(chatId);
		if (dialog.counter > counter)
		{
			Logger.warn('ReadService: counter from server is lower than local one', dialog.counter, counter);
			void this.#store.dispatch('chats/update', {
				dialogId: dialog.dialogId,
				fields: { counter },
			});
		}
	}

	#getDialogIdByChatId(chatId: number): number
	{
		const dialog = this.#store.getters['chats/getByChatId'](chatId);
		if (!dialog)
		{
			return 0;
		}

		return dialog.dialogId;
	}

	#getDialogByChatId(chatId: number): ?ImModelChat
	{
		return this.#store.getters['chats/getByChatId'](chatId);
	}
}
