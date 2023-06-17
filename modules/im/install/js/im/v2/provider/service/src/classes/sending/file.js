import {BaseEvent, EventEmitter} from 'main.core.events';
import {Core} from 'im.v2.application.core';
import {FileStatus, FileType, RestMethod} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';
import {UploadManager} from './upload-manager';
import type {ImModelDialog, ImModelUser} from 'im.v2.model';

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
	temporaryMessageId?: string,
	temporaryFileId: string,
	rawFile: FileFromDisk | File,
	diskFolderId?: number,
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
	#uploadRegistry: {[string]: {dialogId: string, chatId: string}} = {};

	static eventNamespace = 'BX.Messenger.v2.Textarea.UploadingService';

	static events = {
		sendMessageWithFile: 'sendMessageWithFile',
	};

	constructor()
	{
		super();
		this.setEventNamespace(FileService.eventNamespace);

		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
		this.#uploadManager = new UploadManager();

		this.#initUploadManager();
	}

	uploadFile(messageWithFile: MessageWithFile): Promise
	{
		const {temporaryFileId, rawFile, diskFolderId} = messageWithFile;

		this.#addFileToUploadRegistry(temporaryFileId, messageWithFile);

		return this.#uploadManager.addUploadTask(temporaryFileId, rawFile, diskFolderId).then(uploadTask => {
			return this.#addFileToModel(uploadTask);
		});
	}

	uploadFileFromDisk(messageWithFile: MessageWithFile): Promise
	{
		const {temporaryFileId, rawFile} = messageWithFile;

		this.#addFileToUploadRegistry(temporaryFileId, messageWithFile);

		return this.#addFileFromDiskToModel(temporaryFileId, rawFile);
	}

	#addFileFromDiskToModel(id: string, file: FileFromDisk): Promise
	{
		return this.#store.dispatch('files/add', {
			id: id,
			chatId: this.getMessageWithFile(id).chatId,
			authorId: Core.getUserId(),
			name: file.name,
			type: Utils.file.getFileTypeByExtension(file.ext),
			extension: file.ext,
			size: file.sizeInt,
			status: FileStatus.wait,
			progress: 0,
			authorName: this.#getCurrentUser().name,
		});
	}

	#initUploadManager()
	{
		this.#uploadManager = new UploadManager();
		this.#uploadManager.subscribe(UploadManager.events.onFileUploadProgress, (event: BaseEvent) => {
			const {task} = event.getData();
			this.#updateFileProgress(task.taskId, task.progress, FileStatus.upload);
		});
		this.#uploadManager.subscribe(UploadManager.events.onFileUploadComplete, (event: BaseEvent) => {
			const {task, result} = event.getData();
			this.#updateFileProgress(task.taskId, task.progress, FileStatus.wait);

			this.commitFile({
				temporaryFileId: task.taskId,
				realFileId: result.data.file.id,
				fromDisk: false,
			});
		});
		this.#uploadManager.subscribe(UploadManager.events.onFileUploadError, (event: BaseEvent) => {
			const {task} = event.getData();
			this.#updateFileProgress(task.taskId, 0, FileStatus.error);
		});
		this.#uploadManager.subscribe(UploadManager.events.onFileUploadCancel, (event: BaseEvent) => {
			const {taskId} = event.getData();
			this.#cancelUpload(taskId);
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

	commitFile(params: {temporaryFileId: string, realFileId: number, fromDisk: boolean})
	{
		const {temporaryFileId, realFileId, fromDisk} = params;

		const messageWithFile = this.getMessageWithFile(temporaryFileId);

		const fileIdParams = {};
		if (fromDisk)
		{
			fileIdParams.disk_id = realFileId;
		}
		else
		{
			fileIdParams.upload_id = realFileId;
		}

		this.#restClient.callMethod(RestMethod.imDiskFileCommit, {
			chat_id: messageWithFile.chatId,
			message: '', // we don't have feature to send files with text right now
			template_id: messageWithFile.temporaryMessageId,
			file_template_id: temporaryFileId,
			...fileIdParams
		}).catch(error => {
			console.error('fileCommit error', error);
		});
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

	#cancelUpload(taskId: string)
	{
		const messageId = this.getMessageWithFile(taskId).temporaryMessageId;

		this.#store.dispatch('messages/delete', {id: messageId});
		this.#uploadManager.cancel(taskId);
	}

	#addFileToModel(fileToUpload: {taskId: string, file: File, preview: {height: string, width: string, blob: Blob}}): Promise
	{
		const {taskId, file, preview} = fileToUpload;

		const previewData = {};
		if (preview.blob)
		{
			previewData.image = {
				width: preview.width,
				height: preview.height,
			};

			previewData.urlPreview = URL.createObjectURL(preview.blob);
		}

		return this.#store.dispatch('files/add', {
			id: taskId,
			chatId: this.getMessageWithFile(taskId).chatId,
			authorId: Core.getUserId(),
			name: file.name,
			type: this.#getFileType(file),
			extension: this.#getFileExtension(file),
			size: file.size,
			status: FileStatus.progress,
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

	#addFileToUploadRegistry(fileId: string, fileToUpload: MessageWithFile)
	{
		this.#uploadRegistry[fileId] = {
			chatId: this.#getChatId(fileToUpload.dialogId),
			...fileToUpload
		};
	}

	getMessageWithFile(taskId: string): MessageWithFile
	{
		return this.#uploadRegistry[taskId];
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