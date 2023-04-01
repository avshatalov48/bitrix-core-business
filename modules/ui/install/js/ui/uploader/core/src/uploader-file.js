import { Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { FileStatus } from './enums/file-status';
import { FileOrigin } from './enums/file-origin';
import { FileEvent } from './enums/file-event';

import type { FileInfo } from './types/file-info';
import type { UploaderFileOptions } from './types/uploader-file-options';

import UploaderError from './uploader-error';
import AbstractUploadController from './backend/abstract-upload-controller';
import AbstractLoadController from './backend/abstract-load-controller';
import AbstractRemoveController from './backend/abstract-remove-controller';

import createUniqueId from './helpers/create-unique-id';
import createFileFromBlob from './helpers/create-file-from-blob';
import isDataUri from './helpers/is-data-uri';
import createBlobFromDataUri from './helpers/create-blob-from-data-uri';
import isResizableImage from './helpers/is-resizable-image';
import formatFileSize from './helpers/format-file-size';

export default class UploaderFile extends EventEmitter
{
	#id: string = null;
	#file: File = null;
	#serverId: number | string = null;

	#name: string = '';
	#originalName: string = null;
	#size: number = 0;
	#type: string = '';
	#width: ?number = null;
	#height: ?number = null;

	#clientPreview: ?Blob = null;
	#clientPreviewUrl: ?string = null;
	#clientPreviewWidth: ?number = null;
	#clientPreviewHeight: ?number = null;

	#serverPreviewUrl: ?string = null;
	#serverPreviewWidth: ?number = null;
	#serverPreviewHeight: ?number = null;

	#downloadUrl: ?string = null;
	#removeUrl: ?string = null;

	#status: FileStatus = FileStatus.INIT;
	#origin: FileOrigin = FileOrigin.CLIENT;
	#errors: UploaderError[] = [];
	#progress: number = 0;

	#uploadController: AbstractUploadController = null;
	#loadController: AbstractLoadController = null;
	#removeController: AbstractRemoveController = null;

	#uploadCallbacks: CallbackCollection = new CallbackCollection(this);

	constructor(source: File | Blob | string | number, fileOptions: UploaderFileOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.File');

		const options = Type.isPlainObject(fileOptions) ? fileOptions : {};

		if (Type.isFile(source))
		{
			this.#file = source;
		}
		else if (Type.isBlob(source))
		{
			this.#file = createFileFromBlob(source, options.name || source.name);
		}
		else if (isDataUri(source))
		{
			const blob = createBlobFromDataUri(source);
			this.#file = createFileFromBlob(blob, options.name);
		}
		else if (Type.isNumber(source) || Type.isStringFilled(source))
		{
			this.#origin = FileOrigin.SERVER;
			this.#serverId = source;
			if (Type.isPlainObject(options))
			{
				this.setFile(options);
			}
		}

		this.#id = Type.isStringFilled(options.id) ? options.id : createUniqueId();

		this.subscribeFromOptions({
			[FileEvent.ADD]: () => {
				this.#setStatus(FileStatus.ADDED);
			},
		});

		this.subscribeFromOptions(options.events);
	}

	load(): void
	{
		if (!this.canLoad())
		{
			return;
		}

		this.#setStatus(FileStatus.LOADING);
		this.emit(FileEvent.LOAD_START);

		this.#loadController.load(this);
	}

	upload(callbacks: { onComplete: Function, onError: Function } = {}): void
	{
		this.#uploadCallbacks.subscribe(callbacks);
		if (this.isComplete() && this.isUploadable())
		{
			return this.#uploadCallbacks.emit('onComplete');
		}
		else if (this.isUploadFailed())
		{
			return this.#uploadCallbacks.emit('onError', { error: this.getError() });
		}
		else if (!this.canUpload())
		{
			return this.#uploadCallbacks.emit('onError', { error: new UploaderError('FILE_UPLOAD_NOT_ALLOWED') });
		}

		const event = new BaseEvent({ data: { file: this } });
		this.emit(FileEvent.BEFORE_UPLOAD, event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.#setStatus(FileStatus.UPLOADING);
		this.emit(FileEvent.UPLOAD_START);
		this.#uploadController.upload(this);
	}

	remove(): void
	{
		if (this.getStatus() === FileStatus.INIT)
		{
			return;
		}

		this.#setStatus(FileStatus.INIT);
		this.emit(FileEvent.REMOVE_COMPLETE);

		this.abort();

		//this.#setStatus(FileStatus.REMOVING);
		//this.#removeController.remove(this);

		if (this.#removeController !== null && this.getOrigin() === FileOrigin.CLIENT)
		{
			this.#removeController.remove(this);
		}

		this.#uploadController = null;
		this.#loadController = null;
		this.#removeController = null;
	}

	// stop(): void
	// {
	// 	if (this.isUploading())
	// 	{
	// 		this.abort();
	// 		this.setStatus(FileStatus.PENDING);
	// 	}
	// }
	//
	// resume(): void
	// {
	//
	// }
	// retry(): void
	// {
	// 	// TODO
	// }

	abort(): void
	{
		if (this.isLoading())
		{
			this.#setStatus(FileStatus.LOAD_FAILED);

			const error = new UploaderError('FILE_LOAD_ABORTED');
			this.emit(FileEvent.LOAD_ERROR, { error });
		}
		else if (this.isUploading())
		{
			this.#setStatus(FileStatus.UPLOAD_FAILED);

			const error = new UploaderError('FILE_UPLOAD_ABORTED');
			this.emit('onUploadError', { error });
			this.#uploadCallbacks.emit('onError', { error });
		}

		if (this.#loadController)
		{
			this.#loadController.abort();
		}

		if (this.#uploadController)
		{
			this.#uploadController.abort();
		}
	}

	getUploadController(): ?AbstractUploadController
	{
		return this.#uploadController;
	}

	setUploadController(controller: ?AbstractUploadController): void
	{
		if (this.getOrigin() === FileOrigin.SERVER)
		{
			return;
		}

		if (!(controller instanceof AbstractUploadController) && !Type.isNull(controller))
		{
			return;
		}

		const changed = this.#uploadController !== controller;
		this.#uploadController = controller;

		if (this.#uploadController && changed)
		{
			this.#uploadController.subscribeOnce('onError', (event: BaseEvent) => {
				const error = this.addError(event.getData().error);
				this.#setStatus(FileStatus.UPLOAD_FAILED);
				this.emit(FileEvent.UPLOAD_ERROR, { error });
				this.#uploadCallbacks.emit('onError', { error });
			});

			this.#uploadController.subscribe('onProgress', (event: BaseEvent) => {
				const { progress } = event.getData();
				this.setProgress(progress);
				this.emit(FileEvent.UPLOAD_PROGRESS, { progress });
			});

			this.#uploadController.subscribeOnce('onUpload', (event: BaseEvent) => {
				this.#setStatus(FileStatus.COMPLETE);
				this.setFile(event.getData().fileInfo);
				this.emit(FileEvent.UPLOAD_COMPLETE);

				this.#uploadCallbacks.emit('onComplete');
			});
		}

		if (changed)
		{
			this.emit(FileEvent.UPLOAD_CONTROLLER_INIT, { controller });
		}
	}

	setLoadController(controller: AbstractLoadController): void
	{
		if (!(controller instanceof AbstractLoadController))
		{
			return;
		}

		const changed = this.#loadController !== controller;
		this.#loadController = controller;

		if (this.#loadController && changed)
		{
			this.#loadController.subscribeOnce('onError', (event: BaseEvent) => {
				const error = this.addError(event.getData().error);
				this.#setStatus(FileStatus.LOAD_FAILED);
				this.emit(FileEvent.LOAD_ERROR, { error });
			});

			this.#loadController.subscribe('onProgress', (event: BaseEvent) => {
				const { progress } = event.getData();
				this.emit(FileEvent.LOAD_PROGRESS, { progress });
			});

			this.#loadController.subscribeOnce('onLoad', (event: BaseEvent) => {
				if (this.getOrigin() === FileOrigin.SERVER)
				{
					this.setFile(event.getData().fileInfo);
					this.#setStatus(FileStatus.COMPLETE);
					this.emit(FileEvent.LOAD_COMPLETE);
				}
				else
				{
					const event = new BaseEvent({ data: { file: this } });
					this.emitAsync(FileEvent.PREPARE_FILE_ASYNC, event)
						.then(() => {
							if (this.isUploadable())
							{
								this.#setStatus(FileStatus.PENDING);
							}
							else
							{
								this.#setStatus(FileStatus.COMPLETE);
							}

							this.emit(FileEvent.LOAD_COMPLETE);
						})
						.catch(error => {
							error = this.addError(error);
							this.#setStatus(FileStatus.LOAD_FAILED);
							this.emit(FileEvent.LOAD_ERROR, { error });
						})
					;
				}
			});
		}

		if (changed)
		{
			this.emit(FileEvent.LOAD_CONTROLLER_INIT, { controller });
		}
	}

	setRemoveController(controller: ?AbstractRemoveController): void
	{
		if (!(controller instanceof AbstractRemoveController) && !Type.isNull(controller))
		{
			return;
		}

		const changed = this.#removeController !== controller;
		this.#removeController = controller;

		if (this.#removeController && changed)
		{
			this.#removeController.subscribeOnce('onError', (event: BaseEvent) => {
				//const error = this.addError(event.getData().error);
				//this.emit(FileEvent.REMOVE_ERROR, { error });
			});

			this.#removeController.subscribeOnce('onRemove', (event: BaseEvent) => {
				//this.#setStatus(FileStatus.INIT);
				//this.emit(FileEvent.REMOVE_COMPLETE);
			});
		}

		if (changed)
		{
			this.emit(FileEvent.REMOVE_CONTROLLER_INIT, { controller });
		}
	}

	isReadyToUpload(): boolean
	{
		return this.getStatus() === FileStatus.PENDING;
	}

	isUploadable(): boolean
	{
		return this.#uploadController !== null;
	}

	isLoadable(): boolean
	{
		return this.#loadController !== null;
	}

	canUpload(): boolean
	{
		return this.isReadyToUpload() && this.isUploadable();
	}

	canLoad(): boolean
	{
		return this.getStatus() === FileStatus.ADDED && this.isLoadable();
	}

	isUploading(): boolean
	{
		return this.getStatus() === FileStatus.UPLOADING;
	}

	isLoading(): boolean
	{
		return this.getStatus() === FileStatus.LOADING;
	}

	isComplete(): boolean
	{
		return this.getStatus() === FileStatus.COMPLETE;
	}

	isFailed(): boolean
	{
		return this.getStatus() === FileStatus.LOAD_FAILED || this.getStatus() === FileStatus.UPLOAD_FAILED;
	}

	isLoadFailed(): boolean
	{
		return this.getStatus() === FileStatus.LOAD_FAILED;
	}

	isUploadFailed(): boolean
	{
		return this.getStatus() === FileStatus.UPLOAD_FAILED;
	}

	getBinary(): ?File
	{
		return this.#file;
	}

	/**
	 * @internal
	 */
	setFile(file: File | FileInfo): void
	{
		if (Type.isFile(file))
		{
			this.#file = file;
		}
		else if (Type.isBlob(file))
		{
			this.#file = createFileFromBlob(file, this.getName());
		}
		else if (Type.isPlainObject(file))
		{
			this.setName(file.name);
			this.setOriginalName(file.originalName);
			this.setType(file.type);
			this.setSize(file.size);

			this.setServerId(file.serverId);
			this.setWidth(file.width);
			this.setHeight(file.height);

			this.setClientPreview(file.clientPreview, file.clientPreviewWidth, file.clientPreviewHeight);
			this.setServerPreview(file.serverPreviewUrl, file.serverPreviewWidth, file.serverPreviewHeight);

			this.setDownloadUrl(file.downloadUrl);
			this.setRemoveUrl(file.removeUrl);
		}
	}

	getName(): string
	{
		return this.getBinary() ? this.getBinary().name : this.#name;
	}

	/**
	 * @internal
	 */
	setName(name: string): void
	{
		if (Type.isStringFilled(name))
		{
			this.#name = name;
			this.emit(FileEvent.STATE_CHANGE, { property: 'name', value: name });
		}
	}

	getOriginalName(): string
	{
		return this.#originalName ? this.#originalName : this.getName();
	}

	/**
	 * @internal
	 */
	setOriginalName(name: string): void
	{
		if (Type.isStringFilled(name))
		{
			this.#originalName = name;
			this.emit(FileEvent.STATE_CHANGE, { property: 'originalName', value: name });
		}
	}

	getExtension(): string
	{
		const name = this.getOriginalName();
		const position = name.lastIndexOf('.');

		return position >= 0 ? name.substring(position + 1).toLowerCase() : '';
	}

	getType(): string
	{
		return this.getBinary() ? this.getBinary().type : this.#type;
	}

	/**
	 * internal
	 */
	setType(type: string): string
	{
		if (Type.isStringFilled(type))
		{
			this.#type = type;
			this.emit(FileEvent.STATE_CHANGE, { property: 'type', value: type });
		}
	}

	getSize(): number
	{
		return this.getBinary() ? this.getBinary().size : this.#size;
	}

	getSizeFormatted(): string
	{
		return formatFileSize(this.getSize());
	}

	/**
	 * @internal
	 */
	setSize(size: number): void
	{
		if (Type.isNumber(size) && size >= 0)
		{
			this.#size = size;
			this.emit(FileEvent.STATE_CHANGE, { property: 'size', value: size });
		}
	}

	getId(): string
	{
		return this.#id;
	}

	getServerId(): number | string | null
	{
		return this.#serverId;
	}

	setServerId(id: number | string): void
	{
		if (Type.isNumber(id) || Type.isStringFilled(id))
		{
			this.#serverId = id;
			this.emit(FileEvent.STATE_CHANGE, { property: 'serverId', value: id });
		}
	}

	getStatus(): FileStatus
	{
		return this.#status;
	}

	#setStatus(status: FileStatus): void
	{
		this.#status = status;
		this.emit(FileEvent.STATE_CHANGE, { property: 'status', value: status });
		this.emit(FileEvent.STATUS_CHANGE);
	}

	getOrigin(): FileOrigin
	{
		return this.#origin;
	}

	getDownloadUrl(): ?string
	{
		return this.#downloadUrl;
	}

	setDownloadUrl(url: string): void
	{
		if (Type.isStringFilled(url))
		{
			this.#downloadUrl = url;
			this.emit(FileEvent.STATE_CHANGE, { property: 'downloadUrl', value: url });
		}
	}

	getRemoveUrl(): ?string
	{
		return this.#removeUrl;
	}

	setRemoveUrl(url: string)
	{
		if (Type.isStringFilled(url))
		{
			this.#removeUrl = url;
			this.emit(FileEvent.STATE_CHANGE, { property: 'removeUrl', value: url });
		}
	}

	getWidth(): ?number
	{
		return this.#width;
	}

	setWidth(width: number)
	{
		if (Type.isNumber(width))
		{
			this.#width = width;
			this.emit(FileEvent.STATE_CHANGE, { property: 'width', value: width });
		}
	}

	getHeight(): ?number
	{
		return this.#height;
	}

	setHeight(height: ?number)
	{
		if (Type.isNumber(height))
		{
			this.#height = height;
			this.emit(FileEvent.STATE_CHANGE, { property: 'height', value: height });
		}
	}

	getPreviewUrl(): ?string
	{
		return this.getClientPreview() ? this.getClientPreviewUrl() : this.getServerPreviewUrl();
	}

	getPreviewWidth(): ?number
	{
		return this.getClientPreview() ? this.getClientPreviewWidth() : this.getServerPreviewWidth();
	}

	getPreviewHeight(): ?number
	{
		return this.getClientPreview() ? this.getClientPreviewHeight() : this.getServerPreviewHeight();
	}

	getClientPreview(): ?Blob
	{
		return this.#clientPreview;
	}

	setClientPreview(file: ?Blob, width: number = null, height: number = null): void
	{
		if (Type.isBlob(file) || Type.isNull(file))
		{
			this.revokeClientPreviewUrl();

			const url = Type.isNull(file) ? null : URL.createObjectURL(file);
			this.#clientPreview = file;
			this.#clientPreviewUrl = url;
			this.#clientPreviewWidth = width;
			this.#clientPreviewHeight = height;

			this.emit(FileEvent.STATE_CHANGE, { property: 'clientPreviewUrl', value: url });
			this.emit(FileEvent.STATE_CHANGE, { property: 'clientPreviewWidth', value: width });
			this.emit(FileEvent.STATE_CHANGE, { property: 'clientPreviewHeight', value: height });
		}
	}

	getClientPreviewUrl(): ?string
	{
		return this.#clientPreviewUrl;
	}

	revokeClientPreviewUrl(): void
	{
		if (this.#clientPreviewUrl !== null)
		{
			URL.revokeObjectURL(this.#clientPreviewUrl);

			this.#clientPreviewUrl = null;
			this.emit(FileEvent.STATE_CHANGE, { property: 'clientPreviewUrl', value: null });
		}
	}

	getClientPreviewWidth(): ?number
	{
		return this.#clientPreviewWidth;
	}

	getClientPreviewHeight(): ?number
	{
		return this.#clientPreviewHeight;
	}

	getServerPreviewUrl(): ?string
	{
		return this.#serverPreviewUrl;
	}

	setServerPreview(url: ?string, width: number = null, height: number = null): ?string
	{
		if (Type.isStringFilled(url) || Type.isNull(url))
		{
			this.#serverPreviewUrl = url;
			this.#serverPreviewWidth = width;
			this.#serverPreviewHeight = height;

			this.emit(FileEvent.STATE_CHANGE, { property: 'serverPreviewUrl', value: url });
			this.emit(FileEvent.STATE_CHANGE, { property: 'serverPreviewWidth', value: width });
			this.emit(FileEvent.STATE_CHANGE, { property: 'serverPreviewHeight', value: height });
		}
	}

	getServerPreviewWidth(): ?number
	{
		return this.#serverPreviewWidth;
	}

	getServerPreviewHeight(): ?number
	{
		return this.#serverPreviewHeight;
	}

	isImage(): boolean
	{
		return isResizableImage(this.getOriginalName(), this.getType());
	}

	getProgress(): number
	{
		return this.#progress;
	}

	setProgress(progress: ?number)
	{
		if (Type.isNumber(progress))
		{
			this.#progress = progress;
			this.emit(FileEvent.STATE_CHANGE, { property: 'progress', value: progress });
		}
	}

	addError(error: Error | UploaderError): UploaderError
	{
		if (error instanceof Error)
		{
			error = UploaderError.createFromError(error);
		}

		this.#errors.push(error);
		this.emit(FileEvent.STATE_CHANGE);

		return error;
	}

	getError(): ?UploaderError
	{
		return this.#errors[0] || null;
	}

	getErrors(): UploaderError[]
	{
		return this.#errors;
	}

	getState(): { [key: string]: any }
	{
		return JSON.parse(JSON.stringify(this));
	}

	toJSON(): { [key: string]: any }
	{
		return {
			id: this.getId(),
			serverId: this.getServerId(),
			status: this.getStatus(),
			name: this.getName(),
			originalName: this.getOriginalName(),
			size: this.getSize(),
			sizeFormatted: this.getSizeFormatted(),
			type: this.getType(),
			extension: this.getExtension(),
			origin: this.getOrigin(),
			isImage: this.isImage(),
			failed: this.isFailed(),
			width: this.getWidth(),
			height: this.getHeight(),
			progress: this.getProgress(),
			error: this.getError(),
			errors: this.getErrors(),

			previewUrl: this.getPreviewUrl(),
			previewWidth: this.getPreviewWidth(),
			previewHeight: this.getPreviewHeight(),

			clientPreviewUrl: this.getClientPreviewUrl(),
			clientPreviewWidth: this.getClientPreviewWidth(),
			clientPreviewHeight: this.getClientPreviewHeight(),

			serverPreviewUrl: this.getServerPreviewUrl(),
			serverPreviewWidth: this.getServerPreviewWidth(),
			serverPreviewHeight: this.getServerPreviewHeight(),

			downloadUrl: this.getDownloadUrl(),
			removeUrl: this.getRemoveUrl(),
		};
	}
}

class CallbackCollection
{
	#emitter: EventEmitter = null;
	constructor(file: UploaderFile)
	{
		this.#emitter = new EventEmitter(file, 'BX.UI.Uploader.File.UploadCallbacks');
	}

	subscribe(callbacks: { onComplete: Function, onError: Function } = {})
	{
		callbacks = Type.isPlainObject(callbacks) ? callbacks : {};
		if (Type.isFunction(callbacks.onComplete))
		{
			this.getEmitter().subscribeOnce('onComplete', callbacks.onComplete);
		}

		if (Type.isFunction(callbacks.onError))
		{
			this.getEmitter().subscribeOnce('onError', callbacks.onError);
		}
	}

	emit(eventName: string, event: BaseEvent | {[key: string]: any})
	{
		if (this.#emitter)
		{
			this.#emitter.emit(eventName, event);
			this.#emitter.unsubscribeAll();
		}
	}

	getEmitter(): EventEmitter
	{
		if (Type.isNull(this.#emitter))
		{
			this.#emitter = new EventEmitter(this, 'BX.UI.Uploader.File.UploadCallbacks');
		}

		return this.#emitter;
	}
}
