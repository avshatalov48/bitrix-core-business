import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';
import {UuidManager} from 'im.v2.lib.uuid';
import {runAction} from 'im.v2.lib.rest';

import type {ImModelDialog} from 'im.v2.model';

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

	#messagesToRead: {[chatId: string]: Set<messageId>} = {};

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	readAll(): Promise
	{
		Logger.warn('ReadService: readAll');
		this.#store.dispatch('dialogues/clearCounters');
		this.#store.dispatch('recent/clearUnread');

		return this.#restClient.callMethod(RestMethod.imV2ChatReadAll).catch(error => {
			console.error('ReadService: readAll error', error);
		});
	}

	readDialog(dialogId: string)
	{
		Logger.warn('ReadService: readDialog', dialogId);
		this.#store.dispatch('recent/unread', {id: dialogId, action: false});
		this.#store.dispatch('dialogues/update', {
			dialogId,
			fields: {counter: 0}
		});
		this.#restClient.callMethod(RestMethod.imV2ChatRead, {dialogId}).catch(error => {
			console.error('ReadService: error reading chat', error);
		});
	}

	unreadDialog(dialogId: string)
	{
		Logger.warn('ReadService: unreadDialog', dialogId);
		this.#store.dispatch('recent/unread', {id: dialogId, action: true});
		this.#restClient.callMethod(RestMethod.imV2ChatUnread, {dialogId}).catch(error => {
			console.error('ReadService: error setting chat as unread', error);
			this.#store.dispatch('recent/unread', {id: dialogId, action: false});
		});
	}

	readMessage(chatId: number, messageId: number)
	{
		if (!this.#messagesToRead[chatId])
		{
			this.#messagesToRead[chatId] = new Set();
		}
		this.#messagesToRead[chatId].add(messageId);

		clearTimeout(this.readTimeout);
		this.readTimeout = setTimeout(() => {
			Object.entries(this.#messagesToRead).forEach(([queueChatId, messageIds]) => {
				queueChatId = +queueChatId;
				Logger.warn('ReadService: readMessages', messageIds);
				if (messageIds.size === 0)
				{
					return;
				}

				const copiedMessageIds = [...messageIds];
				delete this.#messagesToRead[queueChatId];

				this.#readMessageOnClient(queueChatId, copiedMessageIds).then((readMessagesCount: number) => {
					Logger.warn('ReadService: readMessage, need to reduce counter by', readMessagesCount);
					return this.#decreaseChatCounter(queueChatId, readMessagesCount);
				}).then(() => {
					return this.#readMessageOnServer(queueChatId, copiedMessageIds);
				}).then((readResult: ReadResult) => {
					this.#checkChatCounter(readResult);
				}).catch(error => {
					console.error('ReadService: error reading message', error);
				});
			});
		}, READ_TIMEOUT);
	}

	clearDialogMark(dialogId: string)
	{
		Logger.warn('ReadService: clear dialog mark', dialogId);
		this.#store.dispatch('recent/unread', {
			id: dialogId,
			action: false
		});
		this.#store.dispatch('dialogues/update', {
			dialogId,
			fields: {
				markedId: 0
			}
		});
		this.#restClient.callMethod(RestMethod.imV2ChatRead, {
			dialogId,
			onlyRecent: true
		}).catch(error => {
			console.error('ReadService: error clearing dialog mark', error);
		});
	}

	#readMessageOnClient(chatId: number, messageIds: number[]): Promise<number>
	{
		const maxMessageId = Math.max(...messageIds);
		const dialog = this.#getDialogByChatId(chatId);
		if (maxMessageId > dialog.lastReadId)
		{
			this.#store.dispatch('dialogues/update', {
				dialogId: this.#getDialogIdByChatId(chatId),
				fields: {
					lastId: maxMessageId
				}
			});
		}

		return this.#store.dispatch('messages/readMessages', {
			chatId,
			messageIds
		});
	}

	#decreaseChatCounter(chatId: number, readMessagesCount: number): Promise
	{
		return this.#store.dispatch('dialogues/decreaseCounter', {
			dialogId: this.#getDialogIdByChatId(chatId),
			count: readMessagesCount
		});
	}

	#readMessageOnServer(chatId: number, messageIds: number[]): Promise
	{
		Logger.warn('ReadService: readMessages on server', messageIds);
		return runAction(RestMethod.imV2ChatMessageRead, {
			data: {
				chatId,
				ids: messageIds,
				actionUuid: UuidManager.getInstance().getActionUuid()
			}
		});
	}

	#checkChatCounter(readResult: ReadResult)
	{
		const {chatId, counter} = readResult;

		const dialog = this.#getDialogByChatId(chatId);
		if (dialog.counter > counter)
		{
			Logger.warn('ReadService: counter from server is lower than local one', dialog.counter, counter);
			this.#store.dispatch('dialogues/update', {
				dialogId: dialog.dialogId,
				fields: {counter}
			});
		}
	}

	#getDialogIdByChatId(chatId: number): number
	{
		const dialog = this.#store.getters['dialogues/getByChatId'](chatId);
		if (!dialog)
		{
			return 0;
		}

		return dialog.dialogId;
	}

	#getDialogByChatId(chatId: number): ?ImModelDialog
	{
		return this.#store.getters['dialogues/getByChatId'](chatId);
	}
}