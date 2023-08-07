import { BaseEvent } from 'main.core.events';
import { runAction } from 'im.v2.lib.rest';

import { Core } from 'im.v2.application.core';
import { FileStatus, FileType, RestMethod } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';

import { UploaderWrapper } from './classes/uploading/uploader-wrapper';

import type { ImModelDialog, ImModelUser } from 'im.v2.model';
import type { UploaderFile } from 'ui.uploader.core';

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
	chatId?: number,
	uploaderId: string
}

type FileCommitParams = {
	temporaryFileId: string,
	tempMessageId: string,
	chatId: number,
	realFileId: number,
	fromDisk: boolean,
	sendAsFile: boolean,
	messageText: string
}

type UploadFilesParams = {
	files: File[],
	dialogId: string,
	autoUpload: boolean
}

export class UploadingService
{
	#store;
	#restClient;
	#isRequestingDiskFolderId: boolean = false;
	#diskFolderIdRequestPromise: { [string]: Promise } = {};
	#uploaderWrapper: UploaderWrapper;

	static instance = null;

	static getInstance(): UploadingService
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
		this.#restClient = Core.getRestClient();

		this.#initUploader();
	}

	uploadFiles(params: UploadFilesParams): Promise<{files: UploaderFile[], uploaderId: string}>
	{
		const { files, dialogId, autoUpload } = params;

		const uploaderId = Utils.text.getUuidV4();

		return this.checkDiskFolderId(dialogId).then((diskFolderId: number) => {
			this.#uploaderWrapper.createUploader({
				diskFolderId,
				uploaderId,
				autoUpload,
			});

			const filesForUploader = [];
			files.forEach((file) => {
				const messageWithFile = this.#prepareMessageWithFile(file, dialogId, uploaderId);
				filesForUploader.push(messageWithFile);
			});

			const addedFiles = this.#uploaderWrapper.addFiles(filesForUploader);

			return {
				files: addedFiles,
				uploaderId,
			};
		});
	}

	getFiles(uploaderId): UploaderFile[]
	{
		return this.#uploaderWrapper.getFiles(uploaderId);
	}

	start(uploaderId: string)
	{
		this.#uploaderWrapper.start(uploaderId);
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

	#initUploader()
	{
		this.#uploaderWrapper = new UploaderWrapper();
		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileAddStart, (event: BaseEvent) => {
			const { file } = event.getData();
			this.#addFileToStore(file);
		});

		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileAdd, (event: BaseEvent) => {
			const { file } = event.getData();
			this.#updateFilePreviewInStore(file);
		});
		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadStart, (event: BaseEvent) => {
			const { file } = event.getData();
			this.#updateFileSizeInStore(file);
		});

		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadProgress, (event: BaseEvent) => {
			const { file } = event.getData();
			this.#updateFileProgress(file.getId(), file.getProgress(), FileStatus.upload);
		});

		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadComplete, (event: BaseEvent) => {
			const { file }: {file: UploaderFile} = event.getData();
			this.#updateFileProgress(file.getId(), file.getProgress(), FileStatus.wait);
			this.#uploadPreview(file);

			this.commitFile({
				realFileId: file.getServerFileId(),
				temporaryFileId: file.getId(),
				chatId: file.getCustomData('chatId'),
				tempMessageId: file.getCustomData('tempMessageId'),
				messageText: file.getCustomData('messageText') ?? '',
				sendAsFile: file.getCustomData('sendAsFile'),
				fromDisk: false,
			});
		});
		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadError, (event: BaseEvent) => {
			const { file, error } = event.getData();
			this.#updateFileProgress(file.getId(), 0, FileStatus.error);
			Logger.error('FilesService: upload error', error);
		});
		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadCancel, (event: BaseEvent) => {
			const { tempMessageId, tempFileId } = event.getData();
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
		return new Promise((resolve, reject) => {
			this.#isRequestingDiskFolderId = true;

			const chatId = this.#getChatId(dialogId);
			this.#restClient.callMethod(RestMethod.imDiskFolderGet, { chat_id: chatId }).then((response) => {
				const { ID: diskFolderId } = response.data();
				this.#isRequestingDiskFolderId = false;
				this.#store.commit('dialogues/update', {
					dialogId,
					fields: {
						diskFolderId,
					},
				});
				resolve(diskFolderId);
			}).catch((error) => {
				this.#isRequestingDiskFolderId = false;
				reject(error);
			});
		});
	}

	commitFile(params: FileCommitParams)
	{
		const { temporaryFileId, tempMessageId, chatId, realFileId, fromDisk, messageText = '', sendAsFile = false } = params;

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
			message: messageText,
			template_id: tempMessageId,
			file_template_id: temporaryFileId,
			as_file: sendAsFile ? 'Y' : 'N',
			...fileIdParams,
		}).catch((error) => {
			Logger.error('commitFile error', error);
		});
	}

	#uploadPreview(file: UploaderFile)
	{
		const id = file.getServerFileId().toString().slice(1);
		const previewFile = file.getClientPreview();
		if (!previewFile)
		{
			return;
		}

		const formData = new FormData();
		formData.append('id', id);
		formData.append('previewFile', previewFile, `preview_${file.getName()}.jpg`);

		runAction(RestMethod.imDiskFilePreviewUpload, { data: formData }).catch((error) => {
			Logger.error('imDiskFilePreviewUpload request error', error);
		});
	}

	#prepareMessageWithFile(file: File, dialogId: string, uploaderId): MessageWithFile
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
			uploaderId,
		};
	}

	#updateFileProgress(id: string, progress: number, status: string)
	{
		this.#store.dispatch('files/update', {
			id,
			fields: {
				progress: (progress === 100 ? 99 : progress),
				status,
			},
		});
	}

	#cancelUpload(tempMessageId: string, tempFileId)
	{
		this.#store.dispatch('messages/delete', { id: tempMessageId });
		this.#store.dispatch('files/delete', { id: tempFileId });
	}

	#addFileToStore(file: UploaderFile)
	{
		const taskId = file.getId();
		const fileBinary = file.getBinary();
		const previewData = this.#preparePreview(file);

		this.#store.dispatch('files/add', {
			id: taskId,
			chatId: file.getCustomData('chatId'),
			authorId: Core.getUserId(),
			name: fileBinary.name,
			type: this.#getFileType(fileBinary),
			extension: this.#getFileExtension(fileBinary),
			status: file.isFailed() ? FileStatus.error : FileStatus.progress,
			progress: 0,
			authorName: this.#getCurrentUser().name,
			...previewData,
		});
	}

	#updateFilePreviewInStore(file: UploaderFile)
	{
		const previewData = this.#preparePreview(file);

		this.#store.dispatch('files/update', {
			id: file.getId(),
			fields: {
				...previewData,
			},
		});
	}

	#updateFileSizeInStore(file: UploaderFile)
	{
		this.#store.dispatch('files/update', {
			id: file.getId(),
			fields: {
				size: file.getSize(),
			},
		});
	}

	#preparePreview(file: UploaderFile): { image: { width: number, height: number }, urlPreview: Blob }
	{
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

		return previewData;
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
		this.#uploaderWrapper.destroy();
	}
}
