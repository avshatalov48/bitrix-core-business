import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { EventType, MessageStatus, RestMethod, DialogScrollThreshold } from 'im.v2.const';
import { MessageService } from './registry';

import type { ImModelDialog } from 'im.v2.model';

type Message = {
	temporaryId: string,
	chatId: number,
	dialogId: string,
	authorId: number,
	replyId: number,
	text: string,
	params: Object,
	withFile: boolean,
	unread: boolean,
	sending: boolean
};

export class SendingService
{
	#store: Store;

	static instance = null;

	static getInstance(): SendingService
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	constructor()
	{
		this.#store = Core.getStore();
	}

	sendMessage(params: {text: string, fileId: string, tempMessageId: string, dialogId: string, replyId: number}): Promise
	{
		const { text = '', fileId = '', tempMessageId, dialogId, replyId } = params;
		if (!Type.isStringFilled(text) && !Type.isStringFilled(fileId))
		{
			return Promise.resolve();
		}
		Logger.warn('SendingService: sendMessage', params);

		const message = this.#prepareMessage({ text, fileId, tempMessageId, dialogId, replyId });

		return this.#handlePagination(dialogId)
			.then(() => {
				return this.#addMessageToModels(message);
			})
			.then(() => {
				this.#sendScrollEvent({ force: true, dialogId });

				return this.#sendMessageToServer(message);
			})
			.then((result) => {
				if (message.withFile)
				{
					return;
				}
				Logger.warn('SendingService: sendMessage result -', result.data());
				this.#updateModels({
					oldId: message.temporaryId,
					newId: result.data(),
					dialogId: message.dialogId,
				});
			})
			.catch((error) => {
				this.#updateMessageError(message.temporaryId);
				console.error('SendingService: sendMessage error -', error);
			});
	}

	#prepareMessage(params: {text: string, fileId: string, tempMessageId: string, dialogId: string}): Message
	{
		const { text, fileId, tempMessageId, dialogId, replyId } = params;
		const messageParams = {};
		if (fileId)
		{
			messageParams.FILE_ID = [fileId];
		}

		const temporaryId = tempMessageId || Utils.text.getUuidV4();

		return {
			temporaryId,
			chatId: this.#getDialog(dialogId).chatId,
			dialogId,
			replyId,
			authorId: Core.getUserId(),
			text,
			params: messageParams,
			withFile: Boolean(fileId),
			unread: false,
			sending: true,
		};
	}

	#handlePagination(dialogId: string): Promise
	{
		if (!this.#getDialog(dialogId).hasNextPage)
		{
			return Promise.resolve();
		}

		Logger.warn('SendingService: sendMessage: there are unread pages, move to chat end');
		const messageService = new MessageService({ chatId: this.#getDialog(dialogId).chatId });

		return messageService.loadContext(this.#getDialog(dialogId).lastMessageId).then(() => {
			this.#sendScrollEvent({ dialogId });
		}).catch((error) => {
			console.error('SendingService: loadContext error', error);
		});
	}

	#addMessageToModels(message: Message): Promise
	{
		this.#addMessageToRecent(message);

		this.#store.dispatch('dialogues/clearLastMessageViews', { dialogId: message.dialogId });

		return this.#store.dispatch('messages/add', message);
	}

	#addMessageToRecent(message: Message)
	{
		const recentItem = this.#store.getters['recent/get'](message.dialogId);
		if (!recentItem || message.text === '')
		{
			return;
		}

		this.#store.dispatch('recent/update', {
			id: message.dialogId,
			fields: {
				message: {
					id: message.temporaryId,
					text: message.text,
					authorId: message.authorId,
					replyId: message.replyId,
					status: MessageStatus.received,
					sending: true,
					params: { withFile: false, withAttach: false },
				},
				dateUpdate: new Date(),
			},
		});
	}

	#sendMessageToServer(element: Message): Promise
	{
		if (element.withFile)
		{
			return Promise.resolve();
		}

		const query = {
			template_id: element.temporaryId,
			dialog_id: element.dialogId,
		};

		if (element.replyId !== 0)
		{
			query.reply_id = element.replyId;
		}

		if (element.text)
		{
			query.message = element.text;
		}

		return Core.getRestClient().callMethod(RestMethod.imMessageAdd, query);
	}

	#updateModels(params: {oldId: string, newId: number, dialogId: string, replyId: number})
	{
		const { oldId, newId, dialogId, replyId } = params;
		this.#store.dispatch('messages/updateWithId', {
			id: oldId,
			fields: {
				id: newId,
			},
		});
		this.#store.dispatch('messages/update', {
			id: newId,
			fields: {
				replyId,
			},
		});
		this.#store.dispatch('dialogues/update', {
			dialogId,
			fields: {
				lastId: newId,
				lastMessageId: newId,
			},
		});
		this.#store.dispatch('recent/update', {
			id: dialogId,
			fields: {
				message: { sending: false },
			},
		});
	}

	#updateMessageError(messageId: string)
	{
		this.#store.dispatch('messages/update', {
			id: messageId,
			fields: {
				error: true,
			},
		});
	}

	#sendScrollEvent(params: {force: boolean, dialogId: string} = {})
	{
		const { force = false, dialogId } = params;
		EventEmitter.emit(EventType.dialog.scrollToBottom, {
			chatId: this.#getDialog(dialogId).chatId,
			threshold: force ? DialogScrollThreshold.none : DialogScrollThreshold.halfScreenUp,
		});
	}

	#getDialog(dialogId: string): ImModelDialog
	{
		return this.#store.getters['dialogues/get'](dialogId, true);
	}
}
