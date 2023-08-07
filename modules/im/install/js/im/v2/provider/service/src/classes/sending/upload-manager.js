import { BaseEvent, EventEmitter } from 'main.core.events';
import { Uploader, UploaderEvent, UploaderFile } from 'ui.uploader.core';

import { EventType } from 'im.v2.const';

import type { MessageWithFile } from './file';

type UploadManagerOptions = {
	tempMessageId: string,
	diskFolderId: number,
}

export class UploadManager extends EventEmitter
{
	#uploaderRegistry: {[tempMessageId: string]: Uploader} = {};
	#onUploadCancelHandler: Function;

	static eventNamespace = 'BX.Messenger.v2.Service.Sending.UploadManager';

	static events = {
		onFileAdd: 'onFileAdd',
		onFileUploadProgress: 'onFileUploadProgress',
		onFileUploadComplete: 'onFileUploadComplete',
		onFileUploadError: 'onFileUploadError',
		onFileUploadCancel: 'onFileUploadCancel',
		onMaxFileCountExceeded: 'onMaxFileCountExceeded',
	};

	constructor()
	{
		super();
		this.setEventNamespace(UploadManager.eventNamespace);

		this.#onUploadCancelHandler = this.#onUploadCancel.bind(this);
		EventEmitter.subscribe(EventType.uploader.cancel, this.#onUploadCancelHandler);
	}

	createUploader(options: UploadManagerOptions)
	{
		const { tempMessageId, diskFolderId } = options;

		this.#uploaderRegistry[tempMessageId] = new Uploader({
			autoUpload: true,
			controller: 'disk.uf.integration.diskUploaderController',
			multiple: true,
			controllerOptions: {
				folderId: diskFolderId,
			},
			imageResizeWidth: 1280,
			imageResizeHeight: 1280,
			imageResizeMode: 'contain',
			imageResizeFilter: (file: UploaderFile) => {
				return file.getExtension() !== 'gif';
			},
			imageResizeMimeType: 'image/png',
			imagePreviewHeight: 400,
			imagePreviewWidth: 400,
			events: {
				[UploaderEvent.FILE_ADD]: (event) => {
					this.emit(UploadManager.events.onFileAdd, event);
				},
				[UploaderEvent.FILE_UPLOAD_START]: (event) => {
					this.emit(UploadManager.events.onFileUploadProgress, event);
				},
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: (event) => {
					this.emit(UploadManager.events.onFileUploadProgress, event);
				},
				[UploaderEvent.FILE_UPLOAD_COMPLETE]: (event) => {
					this.emit(UploadManager.events.onFileUploadComplete, event);
				},
				[UploaderEvent.ERROR]: (event) => {
					this.emit(UploadManager.events.onFileUploadError, event);
				},
				[UploaderEvent.FILE_ERROR]: (event) => {
					this.emit(UploadManager.events.onFileUploadError, event);
				},
				[UploaderEvent.MAX_FILE_COUNT_EXCEEDED]: (event) => {
					this.emit(UploadManager.events.onMaxFileCountExceeded, event);
				},
				[UploaderEvent.UPLOAD_COMPLETE]: () => {
					this.#uploaderRegistry[tempMessageId].destroy({ removeFilesFromServer: false });
				},
			},
		});
	}

	addUploadTasks(tasks: MessageWithFile[])
	{
		tasks.forEach((task) => {
			this.addUploadTask(task);
		});
	}

	addUploadTask(task: MessageWithFile)
	{
		this.#uploaderRegistry[task.tempMessageId].addFile(
			task.file,
			{
				id: task.tempFileId,
				customData: {
					dialogId: task.dialogId,
					chatId: task.chatId,
					tempMessageId: task.tempMessageId,
				},
			},
		);
	}

	#onUploadCancel(event: BaseEvent)
	{
		const { tempFileId, tempMessageId } = event.getData();
		if (!tempFileId || !tempMessageId)
		{
			return;
		}

		this.#removeFileFromUploader(tempFileId);
		this.emit(UploadManager.events.onFileUploadCancel, { tempMessageId, tempFileId });
	}

	#removeFileFromUploader(tempFileId: string)
	{
		const uploaderList = Object.values(this.#uploaderRegistry);
		for (const uploader of uploaderList)
		{
			if (!uploader.getFile)
			{
				continue;
			}

			const file = uploader.getFile(tempFileId);
			if (file)
			{
				file.remove();

				break;
			}
		}
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.uploader.cancel, this.#onUploadCancelHandler);
	}
}
