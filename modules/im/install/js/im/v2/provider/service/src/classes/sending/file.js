import {BaseEvent, EventEmitter} from 'main.core.events';

import {Core} from 'im.v2.application.core';
import {FileStatus, FileType, RestMethod} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

import {UploadManager} from './upload-manager';

import type {ImModelDialog, ImModelUser} from 'im.v2.model';
import type {UploaderFile} from 'ui.uploader.core';

export type FileFromDisk = {
	id: string;
	name: string;
	type: string;
	size: string;
	sizeInt: string;
	modifyBy: string;
	modifyDate: string;
	modifyDateInt: number;
	ext: string;
	fileType: string;
};

export type MessageWithFile = {
	tempMessageId?: string,
	tempFileId: string,
	file: FileFromDisk | File,
	dialogId: string,
	chatId?: number
}

export class FileService extends EventEmitter
{
	#store;
	#restClient;
	#isRequestingDiskFolderId: boolean = false;
	#diskFolderIdRequestPromise: {[string]: Promise} = {};
	#uploadManager: UploadManager;

	static eventNamespace = 'BX.Messenger.v2.Service.Sending.FileService';

	static events = {
		sendMessageWithFile: 'sendMessageWithFile',
	};

	constructor()
	{
		super();
		this.setEventNamespace(FileService.eventNamespace);

		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();

		this.#initUploadManager();
	}

	uploadFiles(files: File[], dialogId: string)
	{
		this.checkDiskFolderId(dialogId).then((diskFolderId: number) => {
			const tasks = [];
			files.forEach((file: File) => {
				const messageWithFile = this.#prepareMessageWithFile(file, dialogId);
				this.#uploadManager.createUploader({
					tempMessageId: messageWithFile.tempMessageId,
					diskFolderId: diskFolderId
				});

				tasks.push(messageWithFile);
			});

			this.#uploadManager.addUploadTasks(tasks);
		});
	}

	uploadFileFromDisk(messageWithFile: MessageWithFile): Promise
	{
		return this.#addFileFromDiskToModel(messageWithFile);
	}

	#addFileFromDiskToModel(messageWithFile: MessageWithFile): Promise
	{
		return this.#store.dispatch('files/add', {
			id: messageWithFile.tempFileId,
			chatId: messageWithFile.chatId,
			authorId: Core.getUserId(),
			name: messageWithFile.file.name,
			type: Utils.file.getFileTypeByExtension(messageWithFile.file.ext),
			extension: messageWithFile.file.ext,
			size: messageWithFile.file.sizeInt,
			status: FileStatus.wait,
			progress: 0,
			authorName: this.#getCurrentUser().name,
		});
	}

	#initUploadManager()
	{
		this.#uploadManager = new UploadManager();
		this.#uploadManager.subscribe(UploadManager.events.onFileAdd, (event: BaseEvent) => {
			const {file} = event.getData();

			this.#addFileToModel(file).then(() => {
				this.emit(FileService.events.sendMessageWithFile, event);
			});
		});

		this.#uploadManager.subscribe(UploadManager.events.onFileUploadProgress, (event: BaseEvent) => {
			const {file} = event.getData();
			this.#updateFileProgress(file.getId(), file.getProgress(), FileStatus.upload);
		});

		this.#uploadManager.subscribe(UploadManager.events.onFileUploadComplete, (event: BaseEvent) => {
			const {file}: {file: UploaderFile} = event.getData();

			this.#updateFileProgress(file.getId(), file.getProgress(), FileStatus.wait);

			this.commitFile({
				realFileId: file.getServerFileId(),
				temporaryFileId: file.getId(),
				chatId: file.getCustomData('chatId'),
				tempMessageId: file.getCustomData('tempMessageId'),
				fromDisk: false,
			});
		});
		this.#uploadManager.subscribe(UploadManager.events.onFileUploadError, (event: BaseEvent) => {
			const {file, error} = event.getData();
			this.#updateFileProgress(file.getId(), 0, FileStatus.error);
			console.error('FilesService: upload error', error);
		});
		this.#uploadManager.subscribe(UploadManager.events.onFileUploadCancel, (event: BaseEvent) => {
			const {tempMessageId, tempFileId} = event.getData();
			this.#cancelUpload(tempMessageId, tempFileId);
		});
	}

	checkDiskFolderId(dialogId: string): Promise<number>
	{
		if (this.#getDiskFolderId(dialogId) > 0)
		{
			return Promise.resolve(this.#getDiskFolderId(dialogId));
		}

		if (this.#isRequestingDiskFolderId)
		{
			return this.#diskFolderIdRequestPromise[dialogId];
		}

		this.#diskFolderIdRequestPromise[dialogId] = this.#requestDiskFolderId(dialogId);

		return this.#diskFolderIdRequestPromise[dialogId];
	}

	#requestDiskFolderId(dialogId: string): Promise
	{
		return new Promise((resolve, reject) =>
		{
			this.#isRequestingDiskFolderId = true;

			this.#restClient.callMethod(RestMethod.imDiskFolderGet, {chat_id: this.#getChatId(dialogId)}).then(response => {
				const {ID: diskFolderId} = response.data();
				this.#isRequestingDiskFolderId = false;
				this.#store.commit('dialogues/update', {
					dialogId: dialogId,
					fields: {
						diskFolderId: diskFolderId,
					}
				});
				resolve(diskFolderId);
			}).catch(error => {
				this.#isRequestingDiskFolderId = false;
				reject(error);
			});
		});
	}

	commitFile(params: {temporaryFileId: string, tempMessageId: string, chatId: number, realFileId: number, fromDisk: boolean})
	{
		const {temporaryFileId, tempMessageId, chatId, realFileId, fromDisk} = params;

		const fileIdParams = {};
		if (fromDisk)
		{
			fileIdParams.disk_id = realFileId;
		}
		else
		{
			fileIdParams.upload_id = realFileId.toString().slice(1);
		}

		this.#restClient.callMethod(RestMethod.imDiskFileCommit, {
			chat_id: chatId,
			message: '', // we don't have feature to send files with text right now
			template_id: tempMessageId,
			file_template_id: temporaryFileId,
			...fileIdParams
		}).catch(error => {
			console.error('fileCommit error', error);
		});
	}

	#prepareMessageWithFile(file: File, dialogId: string): MessageWithFile
	{
		const tempMessageId = Utils.text.getUuidV4();
		const tempFileId = Utils.text.getUuidV4();
		const chatId = this.#getChatId(dialogId);

		return {
			tempMessageId,
			tempFileId,
			file,
			dialogId,
			chatId,
		};
	}

	#updateFileProgress(id: string, progress: number, status: string)
	{
		this.#store.dispatch('files/update', {
			id: id,
			fields: {
				progress: (progress === 100 ? 99 : progress),
				status: status,
			}
		});
	}

	#cancelUpload(tempMessageId: string, tempFileId)
	{
		this.#store.dispatch('messages/delete', {id: tempMessageId});
		this.#store.dispatch('files/delete', {id: tempFileId});
	}

	#addFileToModel(file): Promise
	{
		const taskId = file.getId();
		const fileBinary = file.getBinary();
		const preview = {
			blob: file.getPreviewUrl(),
			width: file.getPreviewWidth(),
			height: file.getPreviewHeight(),
		};

		const previewData = {};
		if (preview.blob)
		{
			previewData.image = {
				width: preview.width,
				height: preview.height,
			};

			previewData.urlPreview = preview.blob;
		}

		return this.#store.dispatch('files/add', {
			id: taskId,
			chatId: file.getCustomData('chatId'),
			authorId: Core.getUserId(),
			name: fileBinary.name,
			type: this.#getFileType(fileBinary),
			extension: this.#getFileExtension(fileBinary),
			size: fileBinary.size,
			status: file.isFailed() ? FileStatus.error : FileStatus.progress,
			progress: 0,
			authorName: this.#getCurrentUser().name,
			...previewData
		});
	}

	#getDiskFolderId(dialogId: string): number
	{
		return this.#getDialog(dialogId).diskFolderId;
	}

	#getFileType(file: File): string
	{
		let fileType = FileType.file;
		if (file.type.startsWith('image'))
		{
			fileType = FileType.image;
		}
		else if (file.type.startsWith('video'))
		{
			fileType = FileType.video;
		}

		return fileType;
	}

	#getFileExtension(file: File): string
	{
		return file.name.split('.').splice(-1)[0];
	}

	#getDialog(dialogId: string): ImModelDialog
	{
		return this.#store.getters['dialogues/get'](dialogId);
	}

	#getCurrentUser(): ImModelUser
	{
		const userId = Core.getUserId();

		return this.#store.getters['users/get'](userId);
	}

	#getChatId(dialogId: string): ?number
	{
		return this.#getDialog(dialogId)?.chatId;
	}

	destroy()
	{
		this.#uploadManager.destroy();
	}
}