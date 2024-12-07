import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';
import { runAction, type RunActionError } from 'im.v2.lib.rest';
import { Core } from 'im.v2.application.core';
import { EventType, RestMethod, DialogScrollThreshold, ChatType } from 'im.v2.const';

import { MessageService } from '../registry';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelMessage } from 'im.v2.model';

type BaseMessageParams = {
	dialogId: string,
	text: string,
	tempMessageId?: string,
};

type PlainMessageParams = BaseMessageParams & {
	replyId?: number,
	forwardIds?: number[],
};

type CopilotMessageParams = BaseMessageParams & {
	copilot: {
		promptCode: string,
	},
};

type FileMessageParams = BaseMessageParams & {
	fileIds: string[],
};

type PreparedMessage = {
	temporaryId: string,
	chatId: number,
	dialogId: string,
	authorId: number,
	replyId: number,
	forward: {userId: number, id: string},
	forwardIds: {[string]: number},
	text: string,
	params: JsonObject,
	copilot: JsonObject,
	unread: boolean,
	sending: boolean,
	viewedByOthers: boolean,
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

	async sendMessage(params: PlainMessageParams): Promise
	{
		const { text = '' } = params;
		if (!Type.isStringFilled(text))
		{
			return Promise.resolve();
		}

		Logger.warn('SendingService: sendMessage', params);
		const message = this.#prepareMessage(params);

		return this.#processMessageSending(message);
	}

	async sendMessageWithFiles(params: FileMessageParams): Promise
	{
		const { text = '', fileIds = [] } = params;
		if (!Type.isStringFilled(text) && !Type.isArrayFilled(fileIds))
		{
			return Promise.resolve();
		}

		Logger.warn('SendingService: sendMessage with files', params);
		const message = this.#prepareMessageWithFiles(params);

		await this.#handleAddingMessageToModels(message);

		return Promise.resolve();
	}

	async forwardMessages(params: PlainMessageParams): Promise
	{
		const { forwardIds, dialogId, text } = params;
		if (!Type.isArrayFilled(forwardIds))
		{
			return Promise.resolve();
		}
		Logger.warn('SendingService: forwardMessages', params);

		await this.#handlePagination(dialogId);

		let commentMessage = null;
		if (Type.isStringFilled(text))
		{
			commentMessage = this.#prepareMessage(params);
			await this.#addMessageToModels(commentMessage);
		}

		const forwardUuidMap = this.#getForwardUuidMap(forwardIds);
		const forwardedMessages = this.#prepareForwardMessages(params, forwardUuidMap);

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
			this.#logSendErrors(errors, 'forwardMessage');
		}

		return Promise.resolve();
	}

	async retrySendMessage(params: { tempMessageId: string, dialogId: string }): Promise
	{
		const { tempMessageId, dialogId } = params;
		const unsentMessage: ImModelMessage = this.#store.getters['messages/getById'](tempMessageId);
		if (!unsentMessage)
		{
			return Promise.resolve();
		}

		this.#removeMessageError(tempMessageId);
		const message = this.#prepareMessage({
			text: unsentMessage.text,
			dialogId,
			tempMessageId: unsentMessage.id,
			replyId: unsentMessage.replyId,
		});

		return this.#sendAndProcessMessage(message);
	}

	async sendCopilotPrompt(params: CopilotMessageParams): Promise
	{
		const { text = '' } = params;
		if (!Type.isStringFilled(text))
		{
			return Promise.resolve();
		}

		Logger.warn('SendingService: sendCopilotPrompt', params);
		const message = this.#preparePrompt(params);

		return this.#processMessageSending(message);
	}

	async #processMessageSending(message: PreparedMessage): Promise
	{
		await this.#handleAddingMessageToModels(message);

		return this.#sendAndProcessMessage(message);
	}

	async #handleAddingMessageToModels(message)
	{
		await this.#handlePagination(message.dialogId);
		await this.#addMessageToModels(message);

		this.#sendScrollEvent({ force: true, dialogId: message.dialogId });
	}

	async #sendAndProcessMessage(message: PreparedMessage): Promise
	{
		const sendResult = await this.#sendMessageToServer(message)
			.catch((errors) => {
				this.#updateMessageError(message.temporaryId);
				this.#logSendErrors(errors, 'sendAndProcessMessage');
			});

		Logger.warn('SendingService: sendAndProcessMessage result -', sendResult);
		const { id } = sendResult;
		if (!id)
		{
			return Promise.resolve();
		}

		this.#updateModels({
			oldId: message.temporaryId,
			newId: id,
			dialogId: message.dialogId,
		});

		return Promise.resolve();
	}

	#prepareMessage(params: PlainMessageParams): PreparedMessage
	{
		const { text, tempMessageId, dialogId, replyId, forwardIds } = params;

		const defaultFields = {
			authorId: Core.getUserId(),
			unread: false,
			sending: true,
		};

		return {
			text,
			dialogId,
			chatId: this.#getDialog(dialogId).chatId,
			temporaryId: tempMessageId ?? Utils.text.getUuidV4(),
			replyId,
			forwardIds,
			viewedByOthers: this.#needToSetAsViewed(dialogId),
			...defaultFields,
		};
	}

	#prepareMessageWithFiles(params: FileMessageParams): PreparedMessage
	{
		const { fileIds } = params;
		if (!Type.isArrayFilled(fileIds))
		{
			throw new Error('SendingService: sendMessageWithFile: no fileId provided');
		}

		return {
			...this.#prepareMessage(params),
			params: { FILE_ID: fileIds },
		};
	}

	#preparePrompt(params: CopilotMessageParams): PreparedMessage
	{
		const { copilot } = params;
		if (!copilot || !copilot.promptCode)
		{
			throw new Error('SendingService: preparePrompt: no code provided');
		}

		return {
			...this.#prepareMessage(params),
			copilot,
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

		void this.#store.dispatch('chats/clearLastMessageViews', { dialogId: message.dialogId });

		return this.#store.dispatch('messages/add', message);
	}

	#addMessageToRecent(message: PreparedMessage)
	{
		const recentItem = this.#store.getters['recent/get'](message.dialogId);
		if (!recentItem || message.text === '')
		{
			return;
		}

		void this.#store.dispatch('recent/update', {
			id: message.dialogId,
			fields: { messageId: message.temporaryId },
		});
	}

	#sendMessageToServer(message: PreparedMessage): Promise
	{
		const fields = {};

		if (message.replyId)
		{
			fields.replyId = message.replyId;
		}

		if (message.forwardIds)
		{
			fields.forwardIds = message.forwardIds;
		}

		if (message.text)
		{
			fields.message = message.text;
			fields.templateId = message.temporaryId;
		}

		if (message.copilot)
		{
			fields.copilot = message.copilot;
		}

		const queryData = {
			dialogId: message.dialogId.toString(),
			fields,
		};

		return runAction(RestMethod.imV2ChatMessageSend, { data: queryData });
	}

	#updateModels(params: { oldId: string, newId: number, dialogId: string })
	{
		const { oldId, newId, dialogId } = params;
		void this.#store.dispatch('messages/updateWithId', {
			id: oldId,
			fields: { id: newId },
		});
		void this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				lastId: newId,
				lastMessageId: newId,
			},
		});
		void this.#store.dispatch('recent/update', {
			id: dialogId,
			fields: { messageId: newId },
		});
	}

	#updateMessageError(messageId: string)
	{
		void this.#store.dispatch('messages/update', {
			id: messageId,
			fields: { error: true },
		});
	}

	#removeMessageError(messageId: string)
	{
		void this.#store.dispatch('messages/update', {
			id: messageId,
			fields: {
				sending: true,
				error: false,
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
		return this.#store.getters['users/bots/isNetwork'](dialogId);
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
			void this.#store.dispatch('messages/update', {
				id: commentMessage.temporaryId,
				fields: { error: true },
			});
		}

		Object.keys(forwardUuidMap).forEach((uuid: string) => {
			void this.#store.dispatch('messages/update', {
				id: uuid,
				fields: { error: true },
			});
		});
	}

	#prepareForwardMessages(params: PlainMessageParams, forwardUuidMap: {[string]: number}): PreparedMessage[]
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

			preparedMessages.push({
				...this.#prepareMessage({ dialogId, text: message.text, tempMessageId: uuid, replyId: message.replyId }),
				forward: this.#prepareForwardParams(messageId),
				attach: message.attach,
				isDeleted: message.isDeleted,
				files: message.files,
			});
		});

		return preparedMessages;
	}

	#prepareForwardParams(messageId: number): { id: string, userId: number, chatType: string, chatTitle: string }
	{
		const message: ImModelMessage = this.#store.getters['messages/getById'](messageId);
		const chat = this.#getDialogByChatId(message.chatId);

		const isForward = this.#store.getters['messages/isForward'](messageId);

		const userId = isForward ? message.forward.userId : message.authorId;
		const chatType = isForward ? message.forward.chatType : chat.type;
		let chatTitle = isForward ? message.forward.chatTitle : chat.name;
		if (chatType === ChatType.channel)
		{
			chatTitle = null;
		}

		return {
			id: this.#buildForwardContextId(message.chatId, messageId),
			userId,
			chatType,
			chatTitle,
		};
	}

	#prepareSendForwardRequest(params: {
		forwardUuidMap: { [string]: number },
		commentMessage: ?PreparedMessage,
		dialogId: string
	}): { dialogId: string, forwardIds: { [string]: number }, text?: string, temporaryId?: string }
	{
		const { dialogId, forwardUuidMap, commentMessage } = params;

		const requestPrams = {
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

	#getForwardUuidMap(forwardIds: number[]): { [string]: number }
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

	#logSendErrors(errors: RunActionError[], methodName: string)
	{
		errors.forEach((error) => {
			// eslint-disable-next-line no-console
			console.error(`SendingService: ${methodName} error: code: ${error.code} message: ${error.message}`);
		});
	}
}
