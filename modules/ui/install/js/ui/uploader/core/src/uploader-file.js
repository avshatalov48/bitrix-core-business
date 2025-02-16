// eslint-disable-next-line max-classes-per-file
import { Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { FileStatus } from './enums/file-status';
import { FileOrigin } from './enums/file-origin';
import { FileEvent } from './enums/file-event';
import isSupportedVideo from './helpers/is-supported-video';

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

import type { UploaderFileOptions } from './types/uploader-file-options';
import type { UploaderFileInfo } from './types/uploader-file-info';
import type { RemoveFileOptions } from './types/remove-file-options';

export default class UploaderFile extends EventEmitter
{
	#id: string = null;
	#file: File = null;
	#serverFileId: number | string = null;

	#name: string = null;
	#size: number = 0;
	#type: string = '';
	#width: ?number = null;
	#height: ?number = null;
	#animated: boolean = false;
	#treatImageAsFile: boolean = false;

	#clientPreview: ?Blob = null;
	#clientPreviewUrl: ?string = null;
	#clientPreviewWidth: ?number = null;
	#clientPreviewHeight: ?number = null;

	#serverPreviewUrl: ?string = null;
	#serverPreviewWidth: ?number = null;
	#serverPreviewHeight: ?number = null;

	#downloadUrl: ?string = null;

	#status: FileStatus = FileStatus.INIT;
	#origin: FileOrigin = FileOrigin.CLIENT;
	#errors: UploaderError[] = [];
	#progress: number = 0;
	#customData: Object<string, any> = Object.create(null);

	#uploadController: AbstractUploadController = null;
	#loadController: AbstractLoadController = null;
	#removeController: AbstractRemoveController = null;
	#forceServerLoad: boolean = false;

	#uploadCallbacks: CallbackCollection = new CallbackCollection(this);

	constructor(source: File | Blob | string | number | UploaderFileOptions, fileOptions: UploaderFileOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.File');

		const options: UploaderFileOptions = Type.isPlainObject(fileOptions) ? fileOptions : {};

		if (Type.isFile(source))
		{
			this.#file = source;
			this.update(options);
		}
		else if (Type.isBlob(source))
		{
			this.#file = createFileFromBlob(source, options.name || source.name);
			this.update(options);
		}
		else if (isDataUri(source))
		{
			const blob: Blob = createBlobFromDataUri(source);
			this.#file = createFileFromBlob(blob, options.name);
			this.update(options);
		}
		else if (Type.isNumber(source) || Type.isStringFilled(source))
		{
			this.#origin = FileOrigin.SERVER;
			this.#serverFileId = source;
			this.update(options);
		}
		else if (
			Type.isPlainObject(source)
			&& (Type.isNumber(source.serverFileId) || Type.isStringFilled(source.serverFileId))
		)
		{
			this.#origin = FileOrigin.SERVER;
			this.update(source);
		}

		this.#id = Type.isStringFilled(options.id) ? options.id : createUniqueId();
		if (this.#origin === FileOrigin.SERVER)
		{
			this.#forceServerLoad = options.preload === true || (Type.isPlainObject(source) && source.preload === true);
		}

		this.subscribeFromOptions({
			[FileEvent.ADD]: (): void => {
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

	shouldForceServerLoad(): boolean
	{
		return this.#forceServerLoad;
	}

	upload(callbacks: { onComplete: Function, onError: Function } = {}): void
	{
		this.#uploadCallbacks.subscribe(callbacks);
		if (this.isComplete() && this.isUploadable())
		{
			this.#uploadCallbacks.emit('onComplete');

			return;
		}

		if (this.isUploadFailed())
		{
			this.#uploadCallbacks.emit('onError', { error: this.getError() });

			return;
		}

		if (!this.canUpload())
		{
			this.#uploadCallbacks.emit('onError', { error: new UploaderError('FILE_UPLOAD_NOT_ALLOWED') });

			return;
		}

		const event: BaseEvent<{ file: UploaderFile }> = new BaseEvent({ data: { file: this } });
		this.emit(FileEvent.BEFORE_UPLOAD, event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.#setStatus(FileStatus.PREPARING);

		const prepareEvent: BaseEvent = new BaseEvent({ data: { file: this } });
		this.emitAsync(FileEvent.PREPARE_FILE_ASYNC, prepareEvent)
			.then((): void => {
				this.#setStatus(FileStatus.UPLOADING);
				this.emit(FileEvent.UPLOAD_START);
				this.#uploadController.upload(this);
			})
			.catch((prepareError) => {
				const error = this.addError(prepareError);
				this.#setStatus(FileStatus.UPLOAD_FAILED);
				this.emit(FileEvent.UPLOAD_ERROR, { error });
			})
		;
	}

	remove(options?: RemoveFileOptions): void
	{
		if (this.getStatus() === FileStatus.INIT)
		{
			return;
		}

		this.#setStatus(FileStatus.INIT);
		this.emit(FileEvent.REMOVE_COMPLETE);

		this.abort();

		// this.#setStatus(FileStatus.REMOVING);
		// this.#removeController.remove(this);

		const removeFromServer: boolean = !options || options.removeFromServer !== false;
		if (removeFromServer && this.#removeController !== null && this.getOrigin() === FileOrigin.CLIENT)
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

			const error: UploaderError = new UploaderError('FILE_LOAD_ABORTED');
			this.emit(FileEvent.LOAD_ERROR, { error });
		}
		else if (this.isUploading())
		{
			this.#setStatus(FileStatus.UPLOAD_FAILED);

			const error: UploaderError = new UploaderError('FILE_UPLOAD_ABORTED');
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
		if (!(controller instanceof AbstractUploadController) && !Type.isNull(controller))
		{
			return;
		}

		const changed = this.#uploadController !== controller;
		this.#uploadController = controller;

		if (this.#uploadController && changed)
		{
			this.#uploadController.subscribeOnce('onError', (event: BaseEvent): void => {
				const error: UploaderError = this.addError(event.getData().error);
				this.#setStatus(FileStatus.UPLOAD_FAILED);
				this.emit(FileEvent.UPLOAD_ERROR, { error });
				this.#uploadCallbacks.emit('onError', { error });
			});

			this.#uploadController.subscribe('onProgress', (event: BaseEvent): void => {
				const { progress } = event.getData();
				this.setProgress(progress);
				this.emit(FileEvent.UPLOAD_PROGRESS, { progress });
			});

			this.#uploadController.subscribeOnce('onUpload', (event: BaseEvent): void => {
				this.#setStatus(FileStatus.COMPLETE);
				this.update(event.getData().fileInfo);
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
			this.#loadController.subscribeOnce('onError', (event: BaseEvent): void => {
				const error: UploaderError = this.addError(event.getData().error);
				this.#setStatus(FileStatus.LOAD_FAILED);
				this.emit(FileEvent.LOAD_ERROR, { error });
			});

			this.#loadController.subscribe('onProgress', (event: BaseEvent): void => {
				const { progress } = event.getData();
				this.emit(FileEvent.LOAD_PROGRESS, { progress });
			});

			this.#loadController.subscribeOnce('onLoad', (event: BaseEvent): void => {
				if (this.getOrigin() === FileOrigin.CLIENT)
				{
					const validationEvent: BaseEvent = new BaseEvent({ data: { file: this } });
					this.emitAsync(FileEvent.VALIDATE_FILE_ASYNC, validationEvent)
						.then((): void => {
							if (this.isUploadable())
							{
								this.#setStatus(FileStatus.PENDING);
								this.emit(FileEvent.LOAD_COMPLETE);
							}
							else
							{
								const preparationEvent: BaseEvent = new BaseEvent({ data: { file: this } });
								this.emitAsync(FileEvent.PREPARE_FILE_ASYNC, preparationEvent)
									.then((): void => {
										this.#setStatus(FileStatus.COMPLETE);
										this.emit(FileEvent.LOAD_COMPLETE);
									})
									.catch((preparationError) => {
										const error = this.addError(preparationError);
										this.#setStatus(FileStatus.LOAD_FAILED);
										this.emit(FileEvent.LOAD_ERROR, { error });
									})
								;
							}
						})
						.catch((validationError) => {
							const error = this.addError(validationError);
							this.#setStatus(FileStatus.LOAD_FAILED);
							this.emit(FileEvent.LOAD_ERROR, { error });
						})
					;
				}
				else
				{
					this.update(event.getData().fileInfo);

					if (this.isUploadable())
					{
						this.#setStatus(FileStatus.PENDING);
					}
					else
					{
						this.#setStatus(FileStatus.COMPLETE);
					}

					this.emit(FileEvent.LOAD_COMPLETE);
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
				// const error = this.addError(event.getData().error);
				// this.emit(FileEvent.REMOVE_ERROR, { error });
			});

			this.#removeController.subscribeOnce('onRemove', (event: BaseEvent) => {
				// this.#setStatus(FileStatus.INIT);
				// this.emit(FileEvent.REMOVE_COMPLETE);
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

	isRemoveable(): boolean
	{
		return this.#removeController !== null;
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

	isPreparing(): boolean
	{
		return this.getStatus() === FileStatus.PREPARING;
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

	setFile(file: File | Blob): void
	{
		if (Type.isFile(file))
		{
			this.#file = file;
		}
		else if (Type.isBlob(file))
		{
			this.#file = createFileFromBlob(file, this.getName());
		}
	}

	update(options: UploaderFileOptions): void
	{
		if (Type.isPlainObject(options))
		{
			this.setName(options.name);
			this.setType(options.type);
			this.setSize(options.size);

			this.setServerFileId(options.serverFileId);
			this.setWidth(options.width);
			this.setHeight(options.height);
			this.setTreatImageAsFile(options.treatImageAsFile);

			this.setClientPreview(options.clientPreview, options.clientPreviewWidth, options.clientPreviewHeight);
			this.setServerPreview(options.serverPreviewUrl, options.serverPreviewWidth, options.serverPreviewHeight);

			this.setDownloadUrl(options.downloadUrl);
			this.setCustomData(options.customData);

			this.setLoadController(options.loadController);
			this.setUploadController(options.uploadController);
			this.setRemoveController(options.removeController);
		}
	}

	getName(): string
	{
		return this.#name === null ? (this.getBinary() ? this.getBinary().name : '') : this.#name;
	}

	setName(name: string | null): void
	{
		if (Type.isStringFilled(name) || Type.isNull(name))
		{
			this.#name = name;
			this.emit(FileEvent.STATE_CHANGE, { property: 'name', value: name });
		}
	}

	getExtension(): string
	{
		const name: string = this.getName();
		const position: number = name.lastIndexOf('.');

		return position >= 0 ? name.slice(Math.max(0, position + 1)).toLowerCase() : '';
	}

	getType(): string
	{
		return this.getBinary() ? this.getBinary().type : this.#type;
	}

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

	getServerFileId(): number | string | null
	{
		return this.#serverFileId;
	}

	/**
	 * @deprecated
	 * use getServerFileId
	 */
	getServerId(): number | string | null
	{
		return this.getServerFileId();
	}

	setServerFileId(id: number | string): void
	{
		if (Type.isNumber(id) || Type.isStringFilled(id))
		{
			this.#serverFileId = id;
			this.emit(FileEvent.STATE_CHANGE, { property: 'serverFileId', value: id });
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

	isAnimated(): boolean
	{
		return this.#animated;
	}

	setAnimated(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#animated = flag;
			this.emit(FileEvent.STATE_CHANGE, { property: 'animated', value: flag });
		}
	}

	setTreatImageAsFile(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#treatImageAsFile = flag;
			this.emit(FileEvent.STATE_CHANGE, { property: 'treatImageAsFile', value: flag });
		}
	}

	shouldTreatImageAsFile(): boolean
	{
		return this.#treatImageAsFile;
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
		if (this.shouldTreatImageAsFile())
		{
			return false;
		}

		// return isResizableImage(this.getName(), this.getType());
		return this.getWidth() > 0 && this.getHeight() > 0 && isResizableImage(this.getName(), this.getType());
	}

	isVideo(): boolean
	{
		return isSupportedVideo(this.getName());
	}

	getProgress(): number
	{
		return this.#progress;
	}

	setProgress(progress: ?number): void
	{
		if (Type.isNumber(progress))
		{
			this.#progress = progress;
			this.emit(FileEvent.STATE_CHANGE, { property: 'progress', value: progress });
		}
	}

	addError(error: Error | UploaderError): UploaderError
	{
		const uploaderError: UploaderError = error instanceof Error ? UploaderError.createFromError(error) : error;

		this.#errors.push(uploaderError);
		this.emit(FileEvent.STATE_CHANGE);

		return uploaderError;
	}

	getError(): ?UploaderError
	{
		return this.#errors[0] || null;
	}

	getErrors(): UploaderError[]
	{
		return this.#errors;
	}

	getState(): UploaderFileInfo
	{
		return JSON.parse(JSON.stringify(this));
	}

	setCustomData(property: ?string | { [key: string]: any }, value?: any): void
	{
		if (Type.isNull(property))
		{
			this.#customData = Object.create(null);
			this.emit(FileEvent.STATE_CHANGE, { property: 'customData', value: null });
		}
		else if (Type.isPlainObject(property))
		{
			Object.entries(property).forEach((item) => {
				const [currentKey, currentValue] = item;
				this.setCustomData(currentKey, currentValue);
			});
		}
		else if (Type.isString(property))
		{
			if (Type.isNull(value))
			{
				delete this.#customData[property];
				this.emit(FileEvent.STATE_CHANGE, { property: 'customData', customProperty: property, value: null });
			}
			else if (!Type.isUndefined(value))
			{
				this.#customData[property] = value;
				this.emit(FileEvent.STATE_CHANGE, { property: 'customData', customProperty: property, value });
			}
		}
	}

	getCustomData(property?: string): any
	{
		if (Type.isUndefined(property))
		{
			return this.#customData;
		}

		if (Type.isStringFilled(property))
		{
			return this.#customData[property];
		}

		return undefined;
	}

	toJSON(): UploaderFileInfo
	{
		return {
			id: this.getId(),
			serverFileId: this.getServerFileId(),
			serverId: this.getServerFileId(), // compatibility
			status: this.getStatus(),
			name: this.getName(),
			size: this.getSize(),
			sizeFormatted: this.getSizeFormatted(),
			type: this.getType(),
			extension: this.getExtension(),
			origin: this.getOrigin(),
			isImage: this.isImage(),
			isVideo: this.isVideo(),
			failed: this.isFailed(),
			width: this.getWidth(),
			height: this.getHeight(),
			animated: this.isAnimated(),
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
			customData: this.getCustomData(),
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

	subscribe(callbacks: { onComplete: Function, onError: Function } = {}): void
	{
		const handlers = Type.isPlainObject(callbacks) ? callbacks : {};
		if (Type.isFunction(handlers.onComplete))
		{
			this.getEmitter().subscribeOnce('onComplete', handlers.onComplete);
		}

		if (Type.isFunction(handlers.onError))
		{
			this.getEmitter().subscribeOnce('onError', handlers.onError);
		}
	}

	emit(eventName: string, event: BaseEvent | {[key: string]: any}): void
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
