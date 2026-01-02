import { Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import {
	Uploader,
	UploaderEvent,
	FilterType,
	type UploaderFile,
	type UploaderFileOptions,
	FileStatus as UploaderFileStatus,
} from 'ui.uploader.core';

import { UploaderVideoCompressionFilter } from './video-compression-filter';

import type { UploaderWrapperFileOptions, UploaderWrapperOptions } from './types/uploader-wrapper';

export class UploaderWrapper extends EventEmitter
{
	static EVENT_NAMESPACE = 'BX.Messenger.v2.Service.Uploading.UploaderWrapper';
	static CONTROLLER = 'disk.uf.integration.diskUploaderController';
	static Event = {
		onFileAddStart: 'onFileAddStart',
		onFileAdd: 'onFileAdd',
		onFileLoadComplete: 'onFileLoadComplete',
		onFileStateChange: 'onFileStateChange',
		onFileStatusChange: 'onFileStatusChange',
		onFileUploadStart: 'onFileUploadStart',
		onFileUploadProgress: 'onFileUploadProgress',
		onFileUploadComplete: 'onFileUploadComplete',
		onFileUploadError: 'onFileUploadError',
		onFileUploadCancel: 'onFileUploadCancel',
		onMaxFileCountExceeded: 'onMaxFileCountExceeded',
		onUploadComplete: 'onUploadComplete',
	};

	#id: string;
	#uploader: Uploader;
	#customData: { [key: string]: any } = {};

	constructor({ uploaderId, uploaderOptions, events, customData }: UploaderWrapperOptions)
	{
		super();
		this.setEventNamespace(UploaderWrapper.EVENT_NAMESPACE);
		this.subscribeFromOptions(events);

		this.#id = uploaderId;
		this.#uploader = new Uploader({
			controller: UploaderWrapper.CONTROLLER,
			multiple: true,
			imageResizeWidth: 1280,
			imageResizeHeight: 1280,
			imageResizeMode: 'contain',
			imageResizeMimeType: 'image/jpeg',
			imageResizeMimeTypeMode: 'force',
			imagePreviewHeight: 720,
			imagePreviewWidth: 720,
			treatOversizeImageAsFile: true,
			ignoreUnknownImageTypes: true,
			maxFileSize: null,
			imageResizeFilter: (file: UploaderFile) => {
				return !file.getCustomData('sendAsFile') && !file.isAnimated();
			},
			...uploaderOptions,
			events: {
				[UploaderEvent.FILE_ADD_START]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileAddStart, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_ADD]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileAdd, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_LOAD_COMPLETE]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileLoadComplete, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_STATE_CHANGE]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileStateChange, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_STATUS_CHANGE]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileStatusChange, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_UPLOAD_START]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileUploadStart, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_UPLOAD_PROGRESS]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileUploadProgress, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_UPLOAD_COMPLETE]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileUploadComplete, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.ERROR]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileUploadError, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.FILE_ERROR]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onFileUploadError, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.MAX_FILE_COUNT_EXCEEDED]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onMaxFileCountExceeded, { ...event.getData(), uploaderId });
				},
				[UploaderEvent.UPLOAD_COMPLETE]: (event: BaseEvent) => {
					this.emit(UploaderWrapper.Event.onUploadComplete, { ...event.getData(), uploaderId });
				},
			},
			filters: [
				{
					type: FilterType.PREPARATION,
					filter: UploaderVideoCompressionFilter,
				},
			],
		});

		if (Type.isPlainObject(customData))
		{
			this.#customData = customData;
		}
	}

	getId(): string
	{
		return this.#id;
	}

	setCustomData(key, value)
	{
		this.#customData[key] = value;
	}

	getCustomData(key: string): any
	{
		return this.#customData[key];
	}

	addFiles(filesOptions: Array<UploaderWrapperFileOptions>): Array<UploaderFile>
	{
		const filesEntries: Array<Array<File | Blob, UploaderFileOptions>> = filesOptions.map((fileOption) => {
			return Object.values(fileOption);
		});

		return this.#uploader.addFiles(filesEntries);
	}

	removeFile(fileId: string)
	{
		this.#uploader.getFile(fileId)?.remove?.();
	}

	getFiles(): Array<UploaderFile>
	{
		return this.#uploader.getFiles();
	}

	getFilesIds(): Array<string>
	{
		return this.getFiles().map((file: UploaderFile) => {
			return file.getId();
		});
	}

	isAllPending(): boolean
	{
		return this.getFiles().every((currentFile: UploaderFile) => {
			return currentFile.getStatus() === UploaderFileStatus.PENDING;
		});
	}

	isAllCompleted(): boolean
	{
		return this.getFiles().every((currentFile: UploaderFile) => {
			return currentFile.getStatus() === UploaderFileStatus.COMPLETE;
		});
	}

	getServerFilesIds(): Array<string>
	{
		return this.getFiles().map((file: UploaderFile) => {
			return file.getServerFileId().toString().slice(1);
		});
	}

	getBinaryFiles(): Array<File>
	{
		return this.getFiles().map((file: UploaderFile) => {
			return file.getBinary();
		});
	}

	start()
	{
		this.#uploader.setAutoUpload(true);
		this.#uploader.start();
	}

	stop()
	{
		this.#uploader.stop();
	}

	destroy()
	{
		this.#uploader.destroy({ removeFilesFromServer: false });
	}
}
