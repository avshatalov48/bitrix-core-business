import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {Utils} from 'im.v2.lib.utils';
import {callBatch} from 'im.v2.lib.rest';
import {EventType, MessageStatus, RestMethod, DialogScrollThreshold} from 'im.v2.const';
import {MessageService} from 'im.v2.provider.service';

import {FileService} from './classes/sending/file';

import type {FileFromDisk} from './classes/sending/file';
import type {ImModelDialog} from 'im.v2.model';

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
	#fileService: FileService;

	static instance = null;

	static getInstance()
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
		this.#fileService = new FileService();
	}

	sendMessage(params: {text: string, fileId: string, temporaryMessageId: string, dialogId: string}): Promise
	{
		const {text = '', fileId = '', temporaryMessageId, dialogId} = params;
		if (!Type.isStringFilled(text) && !Type.isStringFilled(fileId))
		{
			return;
		}
		Logger.warn(`SendingService: sendMessage`, params);

		const message = this.#prepareMessage({text, fileId, temporaryMessageId, dialogId});

		return this.#handlePagination(dialogId).then(() => {
			return this.#addMessageToModels(message);
		}).then(() => {
			this.#sendScrollEvent({force: true, dialogId});
			this.#sendMessageToServer(message);
		});
	}

	sendFilesFromInput(files: File[], dialogId: string)
	{
		if (files.length === 0)
		{
			return;
		}

		this.#fileService.checkDiskFolderId(dialogId).then((diskFolderId: number) => {
			files.forEach((rawFile: File) => {
				const temporaryMessageId = Utils.text.getUuidV4();
				const temporaryFileId = Utils.text.getUuidV4();

				const fileToUpload = {temporaryMessageId, temporaryFileId, rawFile, diskFolderId, dialogId};

				this.#fileService.uploadFile(fileToUpload).then(() => {
					this.sendMessage({
						temporaryMessageId: temporaryMessageId,
						fileId: temporaryFileId,
						dialogId: dialogId
					});
				});
			});
		});
	}

	sendFilesFromDisk(files: {[string]: FileFromDisk}, dialogId)
	{
		Object.values(files).forEach(file => {
			const temporaryMessageId = Utils.text.getUuidV4();
			const realFileId = file.id.slice(1); //'n123' => '123'
			const temporaryFileId = `${temporaryMessageId}|${realFileId}`;

			this.#fileService.uploadFileFromDisk({temporaryMessageId, temporaryFileId, dialogId, rawFile: file}).then(() => {
				return this.sendMessage({temporaryMessageId, fileId: temporaryFileId, dialogId});
			}).then(() => {
				this.#fileService.commitFile({
					temporaryFileId: temporaryFileId,
					realFileId: realFileId,
					fromDisk: true,
				});
			});
		});
	}

	destroy()
	{
		this.#fileService.destroy();
	}

	#prepareMessage(params: {text: string, fileId: string, temporaryMessageId: string, dialogId: string}): Message
	{
		const {text, fileId, temporaryMessageId, dialogId} = params;

		const messageParams = {};
		if (fileId)
		{
			messageParams.FILE_ID = [fileId];
		}

		const temporaryId = temporaryMessageId || Utils.text.getUuidV4();

		return {
			temporaryId,
			chatId: this.#getDialog(dialogId).chatId,
			dialogId: dialogId,
			authorId: Core.getUserId(),
			text,
			params: messageParams,
			withFile: !!fileId,
			unread: false,
			sending: true
		};
	}

	#handlePagination(dialogId: string): Promise
	{
		if (!this.#getDialog(dialogId).hasNextPage)
		{
			return Promise.resolve();
		}

		Logger.warn('SendingService: sendMessage: there are unread pages, move to chat end');
		const messageService = new MessageService({chatId: this.#getDialog(dialogId).chatId});
		return messageService.loadContext(this.#getDialog(dialogId).lastMessageId).then(() => {
			this.#sendScrollEvent({dialogId});
		}).catch(error => {
			console.error('SendingService: loadContext error', error);
		});
	}

	#addMessageToModels(message: Message): Promise
	{
		this.#addMessageToRecent(message);

		this.#store.dispatch('dialogues/clearLastMessageViews', {dialogId: message.dialogId});

		return this.#store.dispatch('messages/add', message);
	}

	#addMessageToRecent(message: Message)
	{
		const recentItem = this.#store.getters['recent/get'](message.dialogId);
		if (!recentItem || message.text === '')
		{
			return false;
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
					params: {withFile: false, withAttach: false}
				}
			}
		});
	}

	#sendMessageToServer(element: Message)
	{
		if (element.withFile)
		{
			return;
		}

		const query = {
			[RestMethod.imMessageAdd]: {
				template_id: element.temporaryId,
				dialog_id: element.dialogId
			},
			[RestMethod.imV2ChatRead]: {
				dialogId: element.dialogId,
				onlyRecent: true
			}
		};
		if (element.text)
		{
			query[RestMethod.imMessageAdd].message = element.text;
		}

		callBatch(query).then(result => {
			Logger.warn('SendingService: sendMessage result -', result[RestMethod.imMessageAdd]);
			this.#updateMessageId({
				oldId: element.temporaryId,
				newId: result[RestMethod.imMessageAdd],
				dialogId: element.dialogId
			});
		}).catch(error => {
			this.#updateMessageError(element.temporaryId);
			console.error('SendingService: sendMessage error -', error);
		});
	}

	#updateMessageId(params: {oldId: string, newId: number, dialogId: string})
	{
		const {oldId, newId, dialogId} = params;
		this.#store.dispatch('messages/updateWithId', {
			id: oldId,
			fields: {
				id: newId
			}
		});
		this.#store.dispatch('dialogues/update', {
			dialogId: dialogId,
			fields: {
				lastId: newId,
				lastMessageId: newId
			}
		});
	}

	#updateMessageError(messageId: string)
	{
		this.#store.dispatch('messages/update', {
			id: messageId,
			fields: {
				error: true
			}
		});
	}

	#sendScrollEvent(params: {force: boolean, dialogId: string} = {})
	{
		const {force = false, dialogId} = params;
		EventEmitter.emit(EventType.dialog.scrollToBottom, {
			chatId: this.#getDialog(dialogId).chatId,
			threshold: force ? DialogScrollThreshold.none : DialogScrollThreshold.halfScreenUp
		});
	}

	#getDialog(dialogId: string): ?ImModelDialog
	{
		return this.#store.getters['dialogues/get'](dialogId);
	}
}