import { BaseEvent } from 'main.core.events';
import { getFilesFromDataTransfer, isFilePasted } from 'ui.uploader.core';
import { runAction } from 'im.v2.lib.rest';

import { Core } from 'im.v2.application.core';
import { FileStatus, FileType, RestMethod } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';

import { UploaderWrapper } from './classes/uploader-wrapper';
import { SendingService } from '../registry';

import type { ImModelChat, ImModelUser } from 'im.v2.model';
import type { UploaderFile, UploaderError } from 'ui.uploader.core';
import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';
import type {
	MessageWithFile,
	FileFromDisk,
	FileCommitParams,
	UploadFilesParams,
	UploadFromClipboardParams,
	UploadFromInputParams,
	UploadFromDragAndDrop,
} from './types/uploading';

export class UploadingService
{
	#store: Store;
	#restClient: RestClient;
	#isRequestingDiskFolderId: boolean = false;
	#diskFolderIdRequestPromise: { [string]: Promise } = {};
	#uploaderWrapper: UploaderWrapper;
	#sendingService: SendingService;
	#uploaderFilesRegistry: {
		[uploaderId: string]: {
			autoUpload: boolean,
			wasSent: boolean,
			text: string,
			dialogId: string,
			tempMessageId: string,
			previewCreatedStatus: { [string]: boolean },
			previewSentStatus: { [string]: boolean }
		}
	} = {};

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
		this.#sendingService = SendingService.getInstance();
		this.#initUploader();
	}

	async uploadFromClipboard(params: UploadFromClipboardParams): Promise<{files: UploaderFile[], uploaderId: string}>
	{
		const { clipboardEvent, dialogId, autoUpload, imagesOnly } = params;

		const { clipboardData } = clipboardEvent;
		if (!clipboardData || !isFilePasted(clipboardData))
		{
			return '';
		}

		clipboardEvent.preventDefault();

		let files: File[] = await getFilesFromDataTransfer(clipboardData);
		if (imagesOnly)
		{
			files = files.filter((file) => Utils.file.isImage(file.name));
			if (imagesOnly.length === 0)
			{
				return '';
			}
		}

		const { uploaderFiles, uploaderId } = await this.#addFiles({
			files,
			dialogId,
			autoUpload,
		});

		if (uploaderFiles.length === 0)
		{
			return '';
		}

		return uploaderId;
	}

	async uploadFromInput(params: UploadFromInputParams): Promise<string>
	{
		const { event, sendAsFile, autoUpload, dialogId } = params;
		const rawFiles = Object.values(event.target.files);
		if (rawFiles.length === 0)
		{
			return '';
		}

		const { uploaderId } = await this.#addFiles({
			files: rawFiles,
			dialogId,
			autoUpload,
			sendAsFile,
		});

		return uploaderId;
	}

	async uploadFromDragAndDrop(params: UploadFromDragAndDrop): Promise<string>
	{
		const { event, dialogId, autoUpload, sendAsFile } = params;
		event.preventDefault();

		const rawFiles = await getFilesFromDataTransfer(event.dataTransfer);
		if (rawFiles.length === 0)
		{
			return '';
		}

		const { uploaderId } = await this.#addFiles({
			files: rawFiles,
			dialogId,
			autoUpload,
			sendAsFile,
		});

		return uploaderId;
	}

	#createUploader(params: { dialogId: string, autoUpload: boolean }): Promise<string>
	{
		const { dialogId, autoUpload } = params;

		const uploaderId = Utils.text.getUuidV4();

		return this.checkDiskFolderId(dialogId).then((diskFolderId: number) => {
			this.#uploaderWrapper.createUploader({
				diskFolderId,
				uploaderId,
				autoUpload,
			});

			return uploaderId;
		});
	}

	#addFiles(params: UploadFilesParams): Promise<{uploaderFiles: UploaderFile[], uploaderId: string}>
	{
		const { files, dialogId, autoUpload, sendAsFile = false } = params;

		return this.#createUploader({ dialogId, autoUpload }).then((uploaderId: string) => {
			const filesForUploader = [];
			files.forEach((file) => {
				const preparedFile = this.#prepareFileForUploader(file, dialogId, uploaderId, sendAsFile);
				filesForUploader.push(preparedFile);
			});

			const addedFiles = this.#uploaderWrapper.addFiles(filesForUploader);
			this.#registerFiles(uploaderId, addedFiles, dialogId, autoUpload);

			return {
				uploaderFiles: addedFiles,
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
		this.#uploaderFilesRegistry[uploaderId].autoUpload = true;
		this.#uploaderWrapper.start(uploaderId);
	}

	uploadFileFromDisk(files, dialogId)
	{
		Object.values(files).forEach((file) => {
			const messageWithFile = this.#prepareFileFromDisk(file, dialogId);

			this.#addFileFromDiskToModel(messageWithFile).then(() => {
				const message = {
					tempMessageId: messageWithFile.tempMessageId,
					fileIds: [messageWithFile.tempFileId],
					dialogId: messageWithFile.dialogId,
				};

				return this.#sendingService.sendMessageWithFiles(message);
			}).then(() => {
				this.commitFile({
					chatId: messageWithFile.chatId,
					temporaryFileId: messageWithFile.tempFileId,
					tempMessageId: messageWithFile.tempMessageId,
					realFileId: messageWithFile.file.id.slice(1),
					fromDisk: true,
				});
			}).catch((error) => {
				// eslint-disable-next-line no-console
				console.error('SendingService: sendFilesFromDisk error:', error);
			});
		});
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
			const { file, uploaderId } = event.getData();
			this.#updateFilePreviewInStore(file);
			this.#setPreviewCreatedStatus(uploaderId, file.getId());
			this.#tryToSendMessage(uploaderId);
		});

		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadStart, (event: BaseEvent) => {
			const { file } = event.getData();
			this.#updateFileSizeInStore(file);
		});

		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadProgress, (event: BaseEvent) => {
			const { file } = event.getData();
			this.#updateFileProgress(file.getId(), file.getProgress(), FileStatus.upload);
		});

		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadComplete, async (event: BaseEvent) => {
			const { file, uploaderId }: {file: UploaderFile, uploaderId: string} = event.getData();
			this.#updateFileProgress(file.getId(), file.getProgress(), FileStatus.wait);

			await this.#uploadPreview(file);
			this.#setPreviewSentStatus(uploaderId, file.getId());

			void this.#tryCommit(uploaderId);
		});

		this.#uploaderWrapper.subscribe(UploaderWrapper.events.onFileUploadError, (event: BaseEvent) => {
			const { file, error } = event.getData();
			this.#updateFileProgress(file.getId(), 0, FileStatus.error);
			this.#setMessageError(file.getCustomData('tempMessageId'));
			this.#showError(error);
			Logger.error('UploadingService: upload error', error);
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
				this.#store.commit('chats/update', {
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
			this.#setMessageError(tempMessageId);
			this.#updateFileProgress(temporaryFileId, 0, FileStatus.error);
			Logger.error('commitFile error', error);
		});
	}

	commitMessage(uploaderId: string): Promise
	{
		const dialogId = this.#uploaderFilesRegistry[uploaderId].dialogId;
		const chatId = this.#getChatId(dialogId);

		const fileIds = this.#uploaderWrapper.getFiles(uploaderId).map((file) => {
			return file.getServerFileId().toString().slice(1);
		});

		const sendAsFile = this.#uploaderWrapper.getFiles(uploaderId).every((file) => {
			return file.getCustomData('sendAsFile');
		});

		return this.#restClient.callMethod(RestMethod.imDiskFileCommit, {
			chat_id: chatId,
			message: this.#uploaderFilesRegistry[uploaderId].text,
			template_id: this.#uploaderFilesRegistry[uploaderId].tempMessageId,
			// file_template_id: temporaryFileId,
			as_file: sendAsFile ? 'Y' : 'N',
			upload_id: fileIds,
		});
	}

	async #tryCommit(uploaderId: string)
	{
		if (!this.#readyToCommit(uploaderId))
		{
			return;
		}

		await this.commitMessage(uploaderId);
		this.#uploaderWrapper.destroyUploader(uploaderId);
	}

	async #uploadPreview(file: UploaderFile): Promise
	{
		if (
			this.#getFileType(file.getBinary()) === FileType.file
			|| file.getExtension() === 'gif'
		)
		{
			return Promise.resolve();
		}

		const id = file.getServerFileId().toString().slice(1);
		const previewFile = file.getClientPreview();
		if (!previewFile)
		{
			file.setCustomData('sendAsFile', true);

			return Promise.resolve();
		}

		const formData = new FormData();
		formData.append('id', id);
		formData.append('previewFile', previewFile, `preview_${file.getName()}.jpg`);

		return runAction(RestMethod.imDiskFilePreviewUpload, { data: formData }).catch((error) => {
			Logger.error('imDiskFilePreviewUpload request error', error);
		});
	}

	#prepareFileForUploader(file: File, dialogId: string, uploaderId, sendAsFile: boolean): MessageWithFile
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
			sendAsFile,
		};
	}

	#updateFileProgress(id: string, progress: number, status: string)
	{
		void this.#store.dispatch('files/update', {
			id,
			fields: {
				progress: (progress === 100 ? 99 : progress),
				status,
			},
		});
	}

	#cancelUpload(tempMessageId: string, tempFileId)
	{
		void this.#store.dispatch('messages/delete', { id: tempMessageId });
		void this.#store.dispatch('files/delete', { id: tempFileId });
	}

	#addFileToStore(file: UploaderFile)
	{
		const taskId = file.getId();
		const fileBinary = file.getBinary();
		const previewData = this.#preparePreview(file);
		const asFile = file.getCustomData('sendAsFile');

		void this.#store.dispatch('files/add', {
			id: taskId,
			chatId: file.getCustomData('chatId'),
			authorId: Core.getUserId(),
			name: fileBinary.name,
			size: file.getSize(),
			type: asFile ? FileType.file : this.#getFileType(fileBinary),
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

		void this.#store.dispatch('files/update', {
			id: file.getId(),
			fields: {
				...previewData,
			},
		});
	}

	#updateFileSizeInStore(file: UploaderFile)
	{
		void this.#store.dispatch('files/update', {
			id: file.getId(),
			fields: {
				size: file.getSize(),
			},
		});
	}

	#preparePreview(file: UploaderFile): { image: { width: number, height: number }, urlPreview: Blob }
	{
		if (file.getCustomData('sendAsFile'))
		{
			return {};
		}

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

		if (file.getClientPreview())
		{
			previewData.urlShow = URL.createObjectURL(file.getBinary());
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

	#getDialog(dialogId: string): ImModelChat
	{
		return this.#store.getters['chats/get'](dialogId);
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

	#registerFiles(uploaderId: string, files: UploaderFile[], dialogId: string, autoUpload: boolean)
	{
		if (!this.#uploaderFilesRegistry[uploaderId])
		{
			this.#uploaderFilesRegistry[uploaderId] = {
				previewCreatedStatus: {},
				previewSentStatus: {},
				dialogId,
				text: '',
				autoUpload,
			};
		}

		files.forEach((file) => {
			const fileId = file.getId();
			if (!this.#uploaderFilesRegistry[uploaderId].previewCreatedStatus)
			{
				this.#uploaderFilesRegistry[uploaderId].previewCreatedStatus = {};
			}

			this.#uploaderFilesRegistry[uploaderId].previewCreatedStatus[fileId] = false;
			this.#uploaderFilesRegistry[uploaderId].previewSentStatus[fileId] = false;
		});
	}

	#setPreviewCreatedStatus(uploaderId: string, fileId: string)
	{
		this.#uploaderFilesRegistry[uploaderId].previewCreatedStatus[fileId] = true;
	}

	#setPreviewSentStatus(uploaderId: string, fileId: string)
	{
		this.#uploaderFilesRegistry[uploaderId].previewSentStatus[fileId] = true;
	}

	#setMessagesText(uploaderId: string, text: string)
	{
		this.#uploaderFilesRegistry[uploaderId].text = text;
	}

	#setAutoUpload(uploaderId: string, autoUploadFlag: boolean)
	{
		this.#uploaderFilesRegistry[uploaderId].autoUpload = autoUploadFlag;
	}

	// we don't use it now, because we always send several files in ONE message
	// noinspection JSUnusedGlobalSymbols
	sendSeparateMessagesWithFiles(params: { uploaderId: string, text: string})
	{
		const { uploaderId, text } = params;

		this.#setMessagesText(uploaderId, text);
		this.#setAutoUpload(uploaderId, true);
		this.#tryToSendMessages(uploaderId);
	}

	sendMessageWithFiles(params: { uploaderId: string, text: string})
	{
		const { uploaderId, text } = params;

		this.#setMessagesText(uploaderId, text);
		this.#setAutoUpload(uploaderId, true);
		this.#tryToSendMessage(uploaderId);
	}

	#createMessagesFromFiles(uploaderId): {comment: {text: string, dialogId: string}, files: []}
	{
		const messagesToSend = {
			comment: {},
			files: [],
		};

		const files = this.getFiles(uploaderId);
		const text = this.#uploaderFilesRegistry[uploaderId].text;
		const dialogId = this.#uploaderFilesRegistry[uploaderId].dialogId;
		const hasText = text.length > 0;

		// if we have more than one file and text, we need to send text message first
		if (files.length > 1 && hasText)
		{
			messagesToSend.comment = { dialogId, text };
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

			messagesToSend.files.push({
				fileIds: [file.getId()],
				tempMessageId: file.getCustomData('tempMessageId'),
				dialogId,
				text: file.getCustomData('messageText') ?? '',
			});
		});

		return messagesToSend;
	}

	#createMessageFromFiles(uploaderId): {text: string, dialogId: string, tempMessageId: string, fileIds: []}
	{
		const tempMessageId = Utils.text.getUuidV4();
		this.#uploaderFilesRegistry[uploaderId].tempMessageId = tempMessageId;

		const fileIds = [];
		const files = this.getFiles(uploaderId);
		files.forEach((file) => {
			if (!file.getError())
			{
				fileIds.push(file.getId());
			}
		});

		const text = this.#uploaderFilesRegistry[uploaderId].text;
		const dialogId = this.#uploaderFilesRegistry[uploaderId].dialogId;

		return {
			fileIds,
			tempMessageId,
			dialogId,
			text,
		};
	}

	#readyToAddMessages(uploaderId): boolean
	{
		if (
			!this.#uploaderFilesRegistry[uploaderId]
			|| !this.#uploaderFilesRegistry[uploaderId].autoUpload
			|| this.#uploaderFilesRegistry[uploaderId].wasSent
		)
		{
			return false;
		}

		const { previewCreatedStatus } = this.#uploaderFilesRegistry[uploaderId];

		return Object.values(previewCreatedStatus).every((flag) => flag === true);
	}

	#readyToCommit(uploaderId: string): boolean
	{
		if (!this.#uploaderFilesRegistry[uploaderId])
		{
			return false;
		}

		const { previewSentStatus } = this.#uploaderFilesRegistry[uploaderId];

		return Object.values(previewSentStatus).every((flag) => flag === true);
	}

	#tryToSendMessage(uploaderId: string)
	{
		if (!this.#readyToAddMessages(uploaderId))
		{
			return;
		}

		this.#uploaderFilesRegistry[uploaderId].wasSent = true;

		const message = this.#createMessageFromFiles(uploaderId);
		void this.#sendingService.sendMessageWithFiles(message);
		this.start(uploaderId);
	}

	// we don't use it now, because we always send several files in ONE message
	// noinspection JSUnusedGlobalSymbols
	#tryToSendMessages(uploaderId: string)
	{
		if (!this.#readyToAddMessages(uploaderId))
		{
			return;
		}

		this.#uploaderFilesRegistry[uploaderId].wasSent = true;
		const { comment, files } = this.#createMessagesFromFiles(uploaderId);
		if (comment.text)
		{
			void this.#sendingService.sendMessage(comment);
		}

		files.forEach((message) => {
			void this.#sendingService.sendMessageWithFiles(message);
		});
		this.start(uploaderId);
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

	#showError(error: UploaderError)
	{
		if (error.getCode() === 'MAX_FILE_SIZE_EXCEEDED')
		{
			BX.UI.Notification.Center.notify({
				content: `${error.getMessage()}<br>${error.getDescription()}`,
			});
		}
	}

	#setMessageError(tempMessageId: string)
	{
		void this.#store.dispatch('messages/update', {
			id: tempMessageId,
			fields: {
				error: true,
			},
		});
	}

	destroy()
	{
		this.#uploaderWrapper.destroy();
	}
}
