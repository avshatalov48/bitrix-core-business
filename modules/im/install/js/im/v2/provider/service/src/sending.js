import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { EventType, MessageStatus, RestMethod, DialogScrollThreshold } from 'im.v2.const';
import { UploadingService, MessageService } from './registry';

import type { ImModelDialog } from 'im.v2.model';
import type { FileFromDisk, MessageWithFile } from './uploading';

type Message = {
	temporaryId: string,
	chatId: number,
	dialogId: string,
	authorId: number,
	text: string,
	params: Object,
	withFile: boolean,
	unread: boolean,
	sending: boolean
};

export class SendingService
{
	#store: Store;
	#uploadingService: UploadingService;

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

		this.#uploadingService = UploadingService.getInstance();
	}

	sendMessage(params: {text: string, fileId: string, tempMessageId: string, dialogId: string}): Promise
	{
		const { text = '', fileId = '', tempMessageId, dialogId } = params;
		if (!Type.isStringFilled(text) && !Type.isStringFilled(fileId))
		{
			return Promise.resolve();
		}
		Logger.warn('SendingService: sendMessage', params);

		const message = this.#prepareMessage({ text, fileId, tempMessageId, dialogId });

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

	sendFilesFromInput(files: File[], dialogId: string)
	{
		if (files.length === 0)
		{
			return;
		}

		this.#uploadingService.uploadFiles({ files, dialogId, autoUpload: true }).then(({ files: uploaderFiles }) => {
			uploaderFiles.forEach((file) => {
				this.sendMessage({
					fileId: file.getId(),
					tempMessageId: file.getCustomData('tempMessageId'),
					dialogId: file.getCustomData('dialogId'),
				});
			});
		}).catch((error) => {
			Logger.error('SendingService: sendFilesFromInput error', error);
		});
	}

	sendFilesFromClipboard(files, dialogId): Promise
	{
		return this.#uploadingService.uploadFiles({ files, dialogId, autoUpload: false });
	}

	sendFilesFromDisk(files: {[string]: FileFromDisk}, dialogId: string)
	{
		Object.values(files).forEach((file) => {
			const messageWithFile = this.#prepareFileFromDisk(file, dialogId);

			this.#uploadingService.uploadFileFromDisk(messageWithFile).then(() => {
				return this.sendMessage({
					tempMessageId: messageWithFile.tempMessageId,
					fileId: messageWithFile.tempFileId,
					dialogId: messageWithFile.dialogId,
				});
			}).then(() => {
				this.#uploadingService.commitFile({
					chatId: messageWithFile.chatId,
					temporaryFileId: messageWithFile.tempFileId,
					tempMessageId: messageWithFile.tempMessageId,
					realFileId: messageWithFile.file.id.slice(1),
					fromDisk: true,
				});
			}).catch((error) => {
				console.error('SendingService: sendFilesFromDisk error:', error);
			});
		});
	}

	#prepareFileFromDisk(file: FileFromDisk, dialogId: string): MessageWithFile
	{
		const tempMessageId = Utils.text.getUuidV4();
		const realFileId = file.id.slice(1); // 'n123' => '123'
		const tempFileId = `${tempMessageId}|${realFileId}`;

		return {
			tempMessageId,
			tempFileId,
			dialogId,
			file,
			chatId: this.#getDialog(dialogId).chatId,
		};
	}

	sendMessagesWithFiles(params: {groupFiles: boolean, text: string, uploaderId: string, dialogId: string, sendAsFile: boolean})
	{
		const { groupFiles, text, uploaderId, dialogId, sendAsFile } = params;

		if (groupFiles)
		{
			return;
		}

		const messagesToSend = [];

		const files = this.#uploadingService.getFiles(uploaderId);
		const hasText = text.length > 0;

		// if we have more than one file and text, we need to send text message first
		if (files.length > 1 && hasText)
		{
			messagesToSend.push({ dialogId, text });
		}

		files.forEach((file) => {
			if (file.getError())
			{
				return;
			}

			const messageId = Utils.text.getUuidV4();

			file.setCustomData('messageId', messageId);
			if (files.length === 1 && hasText)
			{
				file.setCustomData('messageText', text);
			}

			if (sendAsFile)
			{
				file.setCustomData('sendAsFile', true);
			}

			messagesToSend.push({
				fileId: file.getId(),
				tempMessageId: file.getCustomData('tempMessageId'),
				dialogId: file.getCustomData('dialogId'),
				text: file.getCustomData('messageText') ?? '',
			});
		});

		messagesToSend.forEach((message) => {
			this.sendMessage(message);
		});

		this.#uploadingService.start(uploaderId);
	}

	#prepareMessage(params: {text: string, fileId: string, tempMessageId: string, dialogId: string}): Message
	{
		const { text, fileId, tempMessageId, dialogId } = params;

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
					status: MessageStatus.received,
					sending: true,
					params: { withFile: false, withAttach: false },
				},
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
		if (element.text)
		{
			query.message = element.text;
		}

		return Core.getRestClient().callMethod(RestMethod.imMessageAdd, query);
	}

	#updateModels(params: {oldId: string, newId: number, dialogId: string})
	{
		const { oldId, newId, dialogId } = params;
		this.#store.dispatch('messages/updateWithId', {
			id: oldId,
			fields: {
				id: newId,
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

	destroy()
	{
		this.#uploadingService.destroy();
	}
}
