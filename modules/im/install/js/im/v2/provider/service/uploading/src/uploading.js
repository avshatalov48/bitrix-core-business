import { Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { FileStatus as UploaderFileStatus, isResizableImage } from 'ui.uploader.core';
import { runAction } from 'im.v2.lib.rest';

import { Core } from 'im.v2.application.core';
import { FileStatus, FileType, RestMethod } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';
import { Notifier } from 'im.v2.lib.notifier';
import { SendingService } from 'im.v2.provider.service.sending';

import { UploaderWrapper } from './classes/uploader-wrapper';
import { createDeferredPromise } from './utils/deferred-promise';

import type { ImModelChat, ImModelUser, ImModelMessage } from 'im.v2.model';
import type { UploaderFile, UploaderError } from 'ui.uploader.core';
import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';
import type {
	MessageWithFile,
	FileFromDisk,
	FileCommitParams,
	UploadFilesParams,
} from './types/uploading';
import type { UploaderWrapperFileOptions } from './classes/types/uploader-wrapper';

type CreateUploaderParams = {
	dialogId: number,
	autoUpload: boolean,
	sendAsFile: boolean,
	maxParallelUploads: number,
	maxParallelLoads: number,
};

const EVENT_NAMESPACE = 'BX.Messenger.v2.Service.UploadingService';

export class UploadingService extends EventEmitter
{
	#store: Store;
	#restClient: RestClient;
	#isRequestingDiskFolderId: boolean = false;
	#diskFolderIdRequestPromise: { [string]: Promise } = {};
	#uploaderWrappers: Map<string, UploaderWrapper> = new Map();
	#sendingService: SendingService;

	static event = {
		uploadStart: 'uploadStart',
		uploadComplete: 'uploadComplete',
		uploadError: 'uploadError',
		uploadCancel: 'uploadCancel',
	};

	#queue: Array<string> = [];
	#isUploading: boolean = false;

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
		super();
		this.setEventNamespace(EVENT_NAMESPACE);

		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
		this.#sendingService = SendingService.getInstance();
	}

	// eslint-disable-next-line max-lines-per-function
	async #createUploader(params: CreateUploaderParams): Promise<{
		uploaderWrapper: UploaderWrapper,
		loadAllComplete: Promise<any>,
		uploadAllComplete: Promise<any>,
	}>
	{
		const {
			dialogId,
			autoUpload = false,
			sendAsFile = false,
			maxParallelUploads,
			maxParallelLoads,
		} = params;

		const uploaderId = Utils.text.getUuidV4();
		const chatId = this.#getChatId(dialogId);
		const folderId = await this.checkDiskFolderId(dialogId);
		const tempMessageId = Utils.text.getUuidV4();

		const loadAllComplete = createDeferredPromise();
		const uploadAllComplete = createDeferredPromise();
		const uploadPreviewPromises = [];

		const uploaderWrapper = new UploaderWrapper({
			uploaderId,
			uploaderOptions: {
				autoUpload,
				maxParallelLoads,
				maxParallelUploads,
				controllerOptions: {
					folderId,
					chat: {
						chatId,
						dialogId,
					},
				},
			},
			customData: {
				chatId,
				dialogId,
				tempMessageId,
				sendAsFile,
			},
			events: {
				[UploaderWrapper.Event.onFileAddStart]: (event: BaseEvent) => {
					const { file } = event.getData();
					this.#addFileToStore(file, sendAsFile);
				},
				[UploaderWrapper.Event.onFileStatusChange]: (event: BaseEvent) => {
					const { file }: { file: UploaderFile } = event.getData();

					if (file.getStatus() === UploaderFileStatus.PENDING)
					{
						if (this.#isMediaFile(file))
						{
							this.#updateFilePreviewInStore(file, sendAsFile);
						}
						else
						{
							this.#updateFilePreviewInStore(file, sendAsFile);
							this.#updateFileTypeInStore(file, FileType.file);
							file.setTreatImageAsFile(true);
							file.setCustomData('sendAsFile', true);
						}

						const target: UploaderWrapper = event.getTarget();

						if (target.isAllPending())
						{
							loadAllComplete.resolve();
						}
					}
				},
				[UploaderWrapper.Event.onFileUploadStart]: (event: BaseEvent) => {
					const { file } = event.getData();
					this.#updateFileSizeInStore(file);
					this.emit(UploadingService.event.uploadStart);
				},
				[UploaderWrapper.Event.onFileUploadProgress]: (event: BaseEvent) => {
					const { file } = event.getData();
					this.#updateFileProgress(file.getId(), file.getProgress(), FileStatus.upload);
				},
				[UploaderWrapper.Event.onFileUploadComplete]: async (event: BaseEvent) => {
					const { file } = event.getData();
					const serverFileId: number = file.getServerFileId().toString().slice(1);
					const temporaryFileId: string = file.getId();
					if (this.#isMediaFile(file))
					{
						this.#setFileMapping({ serverFileId, temporaryFileId });
					}

					this.#updateFileProgress(temporaryFileId, file.getProgress(), FileStatus.wait);

					uploadPreviewPromises.push(
						this.#uploadPreview(file),
					);

					this.emit(UploadingService.event.uploadComplete);
				},
				[UploaderWrapper.Event.onFileUploadError]: (event: BaseEvent) => {
					const { error } = event.getData();
					const target = event.getTarget();

					this.#setMessageError(target.getCustomData('tempMessageId'));

					target.getFiles().forEach((uploaderFile: UploaderFile) => {
						this.#updateFileProgress(uploaderFile.getId(), 0, FileStatus.error);
					});

					if (this.#isMaxFileSizeExceeded(error))
					{
						Notifier.file.handleUploadError(error);
					}

					Logger.error('UploadingService: upload error', error);
					this.emit(UploadingService.event.uploadError);

					this.#isUploading = false;
					this.#processQueue();
					this.stop(uploaderId);
				},
				[UploaderWrapper.Event.onFileUploadCancel]: (event: BaseEvent) => {
					const { tempFileId } = event.getData();
					this.#cancelUpload(tempMessageId, tempFileId);
					this.emit(UploadingService.event.uploadCancel);
				},
				[UploaderWrapper.Event.onUploadComplete]: async (event: BaseEvent) => {
					this.#isUploading = false;
					this.#processQueue();

					const target: UploaderWrapper = event.getTarget();
					if (target.isAllCompleted())
					{
						await Promise.all(uploadPreviewPromises);
						uploadAllComplete.resolve();
						await this.commitMessage(uploaderId);
						this.#destroyUploader(uploaderId);
					}
				},
			},
		});

		return {
			uploaderWrapper,
			loadAllComplete: loadAllComplete.promise,
			uploadAllComplete: uploadAllComplete.promise,
		};
	}

	prepareFilesOptions(
		files: Array<File | Blob | UploaderFile>,
		options: { chatId: number, dialogId: number },
		sendAsFile: boolean,
	): Array<UploaderWrapperFileOptions>
	{
		return files.map((file: UploaderWrapperFileOptions | UploaderFile) => {
			const id = Utils.text.getUuidV4();

			if (Type.isFunction(file.getBinary))
			{
				return {
					file: file.getBinary(),
					options: {
						...file.toJSON(),
						clientPreview: file.getClientPreview(),
						id,
						customData: {
							...options,
						},
						treatImageAsFile: sendAsFile,
					},
				};
			}

			return {
				file,
				options: {
					id,
					customData: {
						...options,
					},
					downloadUrl: URL.createObjectURL(file),
				},
			};
		});
	}

	async addFiles(params: UploadFilesParams): Promise<{
		uploaderFiles: UploaderFile[],
		uploaderId: string,
		loadAllComplete: Promise<any>,
		uploadAllComplete: Promise<any>,
	}>
	{
		const {
			files,
			dialogId,
			autoUpload,
			sendAsFile = false,
			maxParallelUploads,
			maxParallelLoads,
		} = params;

		const {
			uploaderWrapper,
			loadAllComplete,
			uploadAllComplete,
		} = await this.#createUploader({
			dialogId,
			autoUpload,
			sendAsFile,
			maxParallelUploads,
			maxParallelLoads,
		});
		const uploaderId: string = uploaderWrapper.getId();
		const chatId = this.#getChatId(dialogId);

		this.#uploaderWrappers.set(uploaderId, uploaderWrapper);

		const filesOptions = this.prepareFilesOptions(
			files,
			{
				chatId,
				dialogId,
			},
			sendAsFile,
		);

		const uploaderFiles = uploaderWrapper.addFiles(filesOptions);

		return {
			uploaderFiles,
			uploaderId,
			loadAllComplete,
			uploadAllComplete,
		};
	}

	getFiles(uploaderId): UploaderFile[]
	{
		return this.#uploaderWrappers.get(uploaderId).getFiles();
	}

	#addToQueue(uploaderId: string)
	{
		this.#queue.push(uploaderId);
		this.#processQueue();
	}

	#processQueue()
	{
		if (this.#isUploading || this.#queue.length === 0)
		{
			return;
		}

		this.#isUploading = true;

		const uploaderId: string = this.#queue.shift();
		this.#uploaderWrappers.get(uploaderId).start();
	}

	start(uploaderId: string)
	{
		this.getFiles(uploaderId).forEach((file: UploaderFile) => {
			this.#updateFileProgress(file.getId(), 0, FileStatus.progress);
		});

		this.#addToQueue(uploaderId);
	}

	stop(uploaderId: string)
	{
		this.#uploaderWrappers.get(uploaderId).stop();
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

	#isMediaFile(file: UploaderFile): boolean
	{
		return (
			isResizableImage(file.getBinary())
			|| (
				file.isVideo()
				&& Type.isStringFilled(file.getPreviewUrl())
			)
		);
	}

	#setFileMapping(options: {serverFileId: number, temporaryFileId: string})
	{
		void this.#store.dispatch('files/setTemporaryFileMapping', options);
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
				void this.#store.dispatch('chats/update', {
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
			console.error('commitFile error', error);
		});
	}

	commitMessage(uploaderId: string): Promise
	{
		const uploader: UploaderWrapper = this.#uploaderWrappers.get(uploaderId);
		const chatId = uploader.getCustomData('chatId');
		const sendAsFile = uploader.getCustomData('sendAsFile');
		const text = uploader.getCustomData('text');
		const tempMessageId = uploader.getCustomData('tempMessageId');

		const fileIds = uploader.getServerFilesIds();

		return this.#restClient.callMethod(RestMethod.imDiskFileCommit, {
			chat_id: chatId,
			message: text,
			template_id: tempMessageId,
			as_file: sendAsFile ? 'Y' : 'N',
			upload_id: fileIds,
		});
	}

	async #uploadPreview(file: UploaderFile): Promise
	{
		const needPreview = this.#getFileType(file) === FileType.video || file.isAnimated();
		if (!needPreview)
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

		return runAction(RestMethod.imDiskFilePreviewUpload, { data: formData })
			.catch(([error]) => {
				console.error('imDiskFilePreviewUpload request error', error);
			});
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
		const message: ImModelMessage = this.#store.getters['messages/getById'](tempMessageId);
		if (message)
		{
			void this.#store.dispatch('messages/delete', { id: tempMessageId });
			void this.#store.dispatch('files/delete', { id: tempFileId });

			const chat: ImModelChat = this.#store.getters['chats/getByChatId'](message.chatId);
			const lastMessageId: string | number | null = this.#store.getters['messages/findLastChatMessageId'](message.chatId);

			if (Type.isString(lastMessageId) || Type.isNumber(lastMessageId))
			{
				void this.#store.dispatch('recent/update', {
					id: chat.dialogId,
					fields: { messageId: lastMessageId },
				});
			}
			else
			{
				void this.#store.dispatch('recent/delete', {
					id: chat.dialogId,
				});
			}
		}
	}

	#getFileType(file: UploaderFile): string
	{
		if (isResizableImage(file.getBinary()))
		{
			return FileType.image;
		}

		if (file.isVideo())
		{
			return FileType.video;
		}

		if (file.getType().startsWith('audio'))
		{
			return FileType.audio;
		}

		return FileType.file;
	}

	#addFileToStore(file: UploaderFile, sendAsFile: boolean)
	{
		const fileType = this.#getFileType(file);
		const currentUser = this.#getCurrentUser();
		const isFile = ![FileType.image, FileType.video].includes(fileType);

		void this.#store.dispatch('files/add', {
			id: file.getId(),
			name: file.getName(),
			size: file.getSize(),
			type: fileType,
			extension: file.getExtension(),
			chatId: file.getCustomData('chatId'),
			authorId: currentUser.id,
			authorName: currentUser.name,
			status: file.isFailed() ? FileStatus.error : FileStatus.wait,
			progress: 0,
			urlDownload: file.getDownloadUrl(),
			...(() => {
				if (isFile || sendAsFile)
				{
					return { image: false };
				}

				return {};
			})(),
		});
	}

	#updateFilePreviewInStore(file: UploaderFile, sendAsFile: boolean)
	{
		void this.#store.dispatch('files/update', {
			id: file.getId(),
			fields: {
				urlPreview: (() => {
					if (file.isImage())
					{
						return file.getPreviewUrl() || file.getDownloadUrl();
					}

					return file.getPreviewUrl();
				})(),
				...(() => {
					if (sendAsFile)
					{
						return { image: false };
					}

					return {
						image: {
							width: file.getPreviewWidth(),
							height: file.getPreviewHeight(),
						},
					};
				})(),
			},
		});
	}

	#updateFileTypeInStore(file: UploaderFile, type: string)
	{
		void this.#store.dispatch('files/update', {
			id: file.getId(),
			fields: {
				type,
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

	#getDiskFolderId(dialogId: string): number
	{
		return this.#getDialog(dialogId).diskFolderId;
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

	#setMessagesText(uploaderId: string, text: string)
	{
		this.#uploaderWrappers.get(uploaderId).setCustomData('text', text);
	}

	sendMessageWithFiles(params: { uploaderId: string, text: string })
	{
		const { uploaderId, text } = params;

		this.#setMessagesText(uploaderId, text);
		this.#tryToSendMessage(uploaderId);
	}

	#createMessageFromFiles(uploaderId): {text: string, dialogId: string, tempMessageId: string, fileIds: []}
	{
		const fileIds = [];
		const files = this.getFiles(uploaderId);
		files.forEach((file) => {
			if (!file.getError())
			{
				fileIds.push(file.getId());
			}
		});

		const text = this.#uploaderWrappers.get(uploaderId).getCustomData('text');
		const dialogId = this.#uploaderWrappers.get(uploaderId).getCustomData('dialogId');
		const tempMessageId = this.#uploaderWrappers.get(uploaderId).getCustomData('tempMessageId');

		return {
			fileIds,
			tempMessageId,
			dialogId,
			text,
		};
	}

	#tryToSendMessage(uploaderId: string)
	{
		const message = this.#createMessageFromFiles(uploaderId);
		void this.#sendingService.sendMessageWithFiles(message);
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

	#isMaxFileSizeExceeded(error: UploaderError): boolean
	{
		return error.getCode() === 'MAX_FILE_SIZE_EXCEEDED';
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

	getUploaderIdByFileId(fileId: string | number): ?string
	{
		const uploaderIds: Array<string> = [...this.#uploaderWrappers.keys()];

		return uploaderIds.find((uploaderId: string) => {
			return this.getFiles(uploaderId).some((file: UploaderFile) => {
				return file.getId() === fileId;
			});
		});
	}

	removeFileFromUploader(options: { uploaderId: string, filesIds: Array<string>, restartUploading: boolean })
	{
		const { uploaderId, filesIds, restartUploading = false } = options;

		const files = this.#uploaderWrappers.get(uploaderId).getFiles().filter((file: UploaderFile) => {
			return filesIds.includes(file.getId());
		});

		files.forEach((file: UploaderFile) => {
			file.remove();
			file.abort();
		});

		if (restartUploading)
		{
			const [firstFile] = this.getFiles(uploaderId);
			if (firstFile)
			{
				firstFile.upload();
			}
			else
			{
				this.#isUploading = false;
				this.#processQueue();
			}
		}
	}

	#destroyUploader(uploaderId: string)
	{
		this.#uploaderWrappers.get(uploaderId).destroy();
		this.#uploaderWrappers.delete(uploaderId);
	}

	async retry(uploaderId: string)
	{
		const uploaderWrapper = this.#uploaderWrappers.get(uploaderId);
		const dialogId = uploaderWrapper.getCustomData('dialogId');
		const text = uploaderWrapper.getCustomData('text');
		const tempMessageId = uploaderWrapper.getCustomData('tempMessageId');

		const binaryFiles: Array<File> = uploaderWrapper.getBinaryFiles();

		const { uploaderId: newUploaderId, loadAllComplete } = await this.addFiles({
			dialogId,
			files: binaryFiles,
		});

		void this.#store.dispatch('messages/deleteLoadingMessageByMessageId', {
			messageId: tempMessageId,
		});

		await loadAllComplete;

		this.sendMessageWithFiles({
			uploaderId: newUploaderId,
			text,
		});

		this.#destroyUploader(uploaderId);
	}
}
