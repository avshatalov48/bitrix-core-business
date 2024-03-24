import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { Core } from 'im.v2.application.core';
import { EventType, MessageStatus, RestMethod, DialogScrollThreshold, BotType } from 'im.v2.const';

import { MessageService } from './registry';

import type { Store } from 'ui.vue3.vuex';
import type { ImModelChat, ImModelBot, ImModelMessage } from 'im.v2.model';

type SendingMessageParams = {
	tempMessageId?: string,
	dialogId: string,
	replyId?: number,
	text: string,
	fileId?: string,
	forwardIds?: number[],
};

type PreparedMessage = {
	temporaryId: string,
	chatId: number,
	dialogId: string,
	authorId: number,
	replyId: number,
	forwardIds: {[string]: number},
	text: string,
	params: Object,
	withFile: boolean,
	unread: boolean,
	sending: boolean,
	status: $Keys<typeof MessageStatus>,
};

export class SendingService
{
	#store: Store;

	static instance: SendingService = null;

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

	sendMessage(params: SendingMessageParams): Promise
	{
		const { text = '', fileId = '', dialogId } = params;
		if (!Type.isStringFilled(text) && !Type.isStringFilled(fileId))
		{
			return Promise.resolve();
		}

		Logger.warn('SendingService: sendMessage', params);
		const message = this.#prepareMessage(params);

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
				Logger.warn('SendingService: sendMessage result -', result);
				const { id } = result;
				if (!id)
				{
					return;
				}

				this.#updateModels({
					oldId: message.temporaryId,
					newId: id,
					dialogId: message.dialogId,
				});
			})
			.catch((errors) => {
				this.#updateMessageError(message.temporaryId);
				errors.forEach((error) => {
					// eslint-disable-next-line no-console
					console.error(`SendingService: sendMessage error: code: ${error.code} message: ${error.message}`);
				});
			});
	}

	async forwardMessages(params: SendingMessageParams): Promise
	{
		Logger.warn('SendingService: forwardMessages', params);
		const { forwardIds, dialogId } = params;
		if (!Type.isArrayFilled(forwardIds))
		{
			return Promise.resolve();
		}

		await this.#handlePagination(dialogId);

		const commentMessage = this.#prepareForwardCommentForModel(params);
		if (commentMessage)
		{
			await this.#addMessageToModels(commentMessage);
		}

		const forwardUuidMap = this.#getForwardUuidMap(forwardIds);
		const forwardedMessages = this.#prepareForwardForModel(params, forwardUuidMap);

		await this.#addForwardsToModels(forwardedMessages);

		this.#sendScrollEvent({ force: true, dialogId });

		try
		{
			const requestParams = this.#prepareSendForwardRequest({ forwardUuidMap, commentMessage, dialogId });
			const response = await this.#sendMessageToServer(requestParams);
			Logger.warn('SendingService: forwardMessage result -', response);
			this.#handleForwardMessageResponse({ response, dialogId, commentMessage });
		}
		catch (errors)
		{
			this.#handleForwardMessageError({ commentMessage, forwardUuidMap });
			errors.forEach((error) => {
				// eslint-disable-next-line no-console
				console.error(`SendingService: forwardMessage error: code: ${error.code} message: ${error.message}`);
			});
		}

		return Promise.resolve();
	}

	retrySendMessage(params: { tempMessageId: string, dialogId: string }): Promise
	{
		const { tempMessageId, dialogId } = params;

		const unsentMessage: ImModelMessage = this.#store.getters['messages/getById'](tempMessageId);
		if (!unsentMessage)
		{
			return Promise.resolve();
		}

		this.#store.dispatch('messages/update', {
			id: tempMessageId,
			fields: {
				sending: true,
				error: false,
			},
		});

		const message = this.#prepareMessage({
			text: unsentMessage.text,
			tempMessageId: unsentMessage.id,
			dialogId,
			replyId: unsentMessage.replyId,
		});

		return this.#sendMessageToServer(message).then((result) => {
			if (message.withFile)
			{
				return;
			}
			Logger.warn('SendingService: retrySendMessage result -', result.data());
			const { id } = result.data();
			if (!id)
			{
				return;
			}
			this.#updateModels({
				oldId: message.temporaryId,
				newId: id,
				dialogId: message.dialogId,
			});
		}).catch((errors) => {
			this.#updateMessageError(message.temporaryId);
			errors.forEach((error) => {
				// eslint-disable-next-line no-console
				console.error(`SendingService: retrySendMessage error: code: ${error.code} message: ${error.message}`);
			});
		});
	}

	#prepareMessage(params: SendingMessageParams): PreparedMessage
	{
		const { text, fileId, tempMessageId, dialogId, replyId, forwardIds } = params;
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
			forwardIds,
			authorId: Core.getUserId(),
			text,
			params: messageParams,
			withFile: Boolean(fileId),
			unread: false,
			sending: true,
			viewedByOthers: this.#needToSetAsViewed(dialogId),
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
			// eslint-disable-next-line no-console
			console.error('SendingService: loadContext error', error);
		});
	}

	#addMessageToModels(message: PreparedMessage): Promise
	{
		this.#addMessageToRecent(message);

		this.#store.dispatch('chats/clearLastMessageViews', { dialogId: message.dialogId });

		return this.#store.dispatch('messages/add', message);
	}

	#addMessageToRecent(message: PreparedMessage)
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
					date: new Date(),
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

	#sendMessageToServer(element: PreparedMessage): Promise
	{
		if (element.withFile)
		{
			return Promise.resolve();
		}

		const fields = {};

		if (element.replyId)
		{
			fields.replyId = element.replyId;
		}

		if (element.forwardIds)
		{
			fields.forwardIds = element.forwardIds;
		}

		if (element.text)
		{
			fields.message = element.text;
			fields.templateId = element.temporaryId;
		}

		const queryData = {
			dialogId: element.dialogId.toString(),
			fields,
		};

		return runAction(RestMethod.imV2ChatMessageSend, { data: queryData });
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
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				lastId: newId,
				lastMessageId: newId,
			},
		});
		this.#store.dispatch('recent/update', {
			id: dialogId,
			fields: {
				message: { sending: false, date: new Date() },
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

	#getDialog(dialogId: string): ImModelChat
	{
		return this.#store.getters['chats/get'](dialogId, true);
	}

	#getDialogByChatId(chatId: number): ImModelChat
	{
		return this.#store.getters['chats/getByChatId'](chatId, true);
	}

	#needToSetAsViewed(dialogId: string): boolean
	{
		const bot: ImModelBot = this.#store.getters['users/bots/getByUserId'](dialogId);

		return bot?.type === BotType.network;
	}

	#handleForwardMessageResponse(params: { response: Object, dialogId: string, commentMessage: PreparedMessage })
	{
		const { response, dialogId, commentMessage } = params;
		const { id, uuidMap } = response;

		if (id)
		{
			this.#updateModels({
				oldId: commentMessage.temporaryId,
				newId: id,
				dialogId,
			});
		}
		Object.entries(uuidMap).forEach(([uuid: string, messageId: number]) => {
			this.#updateModels({
				oldId: uuid,
				newId: messageId,
				dialogId,
			});
		});
	}

	#handleForwardMessageError({ commentMessage, forwardUuidMap })
	{
		if (commentMessage)
		{
			this.#store.dispatch('messages/update', {
				id: commentMessage.temporaryId,
				fields: {
					error: true,
				},
			});
		}

		Object.keys(forwardUuidMap).forEach((uuid: string) => {
			this.#store.dispatch('messages/update', {
				id: uuid,
				fields: {
					error: true,
				},
			});
		});
	}

	#prepareForwardForModel(params: SendingMessageParams, forwardUuidMap: {[string]: number}): PreparedMessage[]
	{
		const { forwardIds, dialogId } = params;
		if (forwardIds.length === 0)
		{
			return [];
		}

		const preparedMessages = [];
		Object.entries(forwardUuidMap).forEach(([uuid: string, messageId: number]) => {
			const message: ImModelMessage = this.#store.getters['messages/getById'](messageId);
			if (!message)
			{
				return;
			}

			const isForward = this.#store.getters['messages/isForward'](messageId);

			preparedMessages.push({
				attach: message.attach,
				temporaryId: uuid,
				chatId: this.#getDialog(dialogId).chatId,
				authorId: Core.getUserId(),
				replyId: message.replyId,
				text: message.text,
				isDeleted: message.isDeleted,
				forward: {
					id: this.#buildForwardContextId(message.chatId, messageId),
					userId: isForward ? message.forward.userId : message.authorId,
				},
				files: message.files,
				unread: false,
				sending: true,
			});
		});

		return preparedMessages;
	}

	#prepareSendForwardRequest(params: {
		forwardUuidMap: { [string]: number },
		commentMessage: ?PreparedMessage,
		dialogId: string
	}): { withFile: boolean, dialogId: string, forwardIds: { [string]: number }, text?: string, temporaryId?: string }
	{
		const { dialogId, forwardUuidMap, commentMessage } = params;

		const requestPrams = {
			withFile: false,
			dialogId,
			forwardIds: forwardUuidMap,
		};

		if (commentMessage)
		{
			requestPrams.text = commentMessage.text;
			requestPrams.temporaryId = commentMessage.temporaryId;
		}

		return requestPrams;
	}

	#addForwardsToModels(forwardedMessages: PreparedMessage[]): Promise
	{
		const addPromises = [];
		forwardedMessages.forEach((message) => {
			addPromises.push(this.#addMessageToModels(message));
		});

		return Promise.all(addPromises);
	}

	#prepareForwardCommentForModel(params: SendingMessageParams): ?PreparedMessage
	{
		if (!Type.isStringFilled(params.text))
		{
			return null;
		}

		return {
			temporaryId: Utils.text.getUuidV4(),
			chatId: this.#getDialog(params.dialogId).chatId,
			dialogId: params.dialogId,
			authorId: Core.getUserId(),
			text: params.text,
			withFile: false,
			unread: false,
			sending: true,
			status: this.#needToSetAsViewed(params.dialogId),
		};
	}

	#getForwardUuidMap(forwardIds: number[]): {[string]: number}
	{
		const uuidMap = {};
		forwardIds.forEach((id) => {
			uuidMap[Utils.text.getUuidV4()] = id;
		});

		return uuidMap;
	}

	#buildForwardContextId(chatId: number, messageId: number): string
	{
		const dialogId = this.#getDialogByChatId(chatId).dialogId;
		if (dialogId.startsWith('chat'))
		{
			return `${dialogId}/${messageId}`;
		}

		const currentUser = Core.getUserId();

		return `${dialogId}:${currentUser}/${messageId}`;
	}
}
