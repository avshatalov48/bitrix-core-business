import { Type, Runtime } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { FileStatus } from './enums/file-status';
import { FileOrigin } from './enums/file-origin';

import type { FileInfo } from './types/file-info';
import type { UploaderFileOptions } from './types/uploader-file-options';

import AbstractUploadController from './backend/abstract-upload-controller';
import AbstractLoadController from './backend/abstract-load-controller';

import createUniqueId from './helpers/create-unique-id';
import createFileFromBlob from './helpers/create-file-from-blob';
import isDataUri from './helpers/is-data-uri';
import createBlobFromDataUri from './helpers/create-blob-from-data-uri';
import isResizableImage from './helpers/is-resizable-image';
import formatFileSize from './helpers/format-file-size';

export default class UploaderFile extends EventEmitter
{
	id: string = null;
	source: File | Blob | string | number;
	file: File = null;
	serverId: number | string = null;

	name: string = '';
	originalName: string = null;
	size: number = 0;
	type: string = '';
	width: ?number = null;
	height: ?number = null;

	clientPreview: ?File = null;
	clientPreviewUrl: ?string = null;
	clientPreviewWidth: ?number = null;
	clientPreviewHeight: ?number = null;

	serverPreviewUrl: ?string = null;
	serverPreviewWidth: ?number = null;
	serverPreviewHeight: ?number = null;

	downloadUrl: ?string = null;
	removeUrl: ?string = null;

	status: FileStatus = FileStatus.INIT;
	origin: FileOrigin = FileOrigin.CLIENT;

	uploadController: AbstractUploadController = null;
	loadController: AbstractLoadController = null;

	constructor(source: File | Blob | string | number, fileOptions: UploaderFileOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.File');

		const options = Type.isPlainObject(fileOptions) ? fileOptions : {};

		if (Type.isFile(source))
		{
			this.file = source;
		}
		else if (Type.isBlob(source))
		{
			this.file = createFileFromBlob(source, options.name || source.name);
		}
		else if (isDataUri(source))
		{
			const blob = createBlobFromDataUri(source);
			this.file = createFileFromBlob(blob, options.name);
		}
		else if (Type.isNumber(source) || Type.isStringFilled(source))
		{
			this.origin = FileOrigin.SERVER;
			this.serverId = source;
			if (Type.isPlainObject(options))
			{
				this.setFile(options);
			}
		}

		this.id = Type.isStringFilled(options.id) ? options.id : createUniqueId();
		this.subscribeFromOptions(options.events);

		//this.fireStateChangeEvent = Runtime.debounce(this.fireStateChangeEvent, 0, this);
	}

	load(): void
	{
		if (!this.canLoad())
		{
			return;
		}

		this.setStatus(FileStatus.LOADING);
		this.emit('onLoadStart');

		this.loadController.load(this);
	}

	upload(): void
	{
		if (!this.canUpload())
		{
			return;
		}

		let event = new BaseEvent({ data: { file: this } });
		this.emit('onBeforeUpload', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.setStatus(FileStatus.UPLOADING);

		event = new BaseEvent({ data: { file: this.getFile() } });
		this.emitAsync('onPrepareFileAsync', event)
			.then((result) => {
				const file = Type.isArrayFilled(result) && Type.isFile(result[0]) ? result[0] : this.getFile();
				this.emit('onUploadStart');

				if (this.uploadController)
				{
					this.uploadController.upload(file);
				}
			})
			.catch(error => {
				console.error(error);
			})
		;
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
		if (this.uploadController)
		{
			this.uploadController.abort();
		}

		this.setStatus(FileStatus.ABORTED);
		this.emit('onAbort');
	}

	abortLoad(): void
	{
		if (this.loadController)
		{
			this.loadController.abort();
		}

		this.setStatus(FileStatus.ABORTED);
		this.emit('onAbort');
	}

	cancel(): void
	{
		this.abort();
		this.emit('onCancel');
	}

	setUploadController(controller: AbstractUploadController): void
	{
		this.uploadController = controller;
	}

	setLoadController(controller: AbstractLoadController): void
	{
		this.loadController = controller;
	}

	isReadyToUpload(): boolean
	{
		return this.getStatus() === FileStatus.PENDING;
	}

	isUploadable(): boolean
	{
		return this.uploadController !== null;
	}

	isLoadable(): boolean
	{
		return this.loadController !== null;
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

	getFile(): ?File
	{
		return this.file;
	}

	/**
	 * @internal
	 */
	setFile(file: File | FileInfo): void
	{
		if (Type.isFile(file))
		{
			this.file = file;
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
		return this.getFile() ? this.getFile().name : this.name;
	}

	/**
	 * @internal
	 */
	setName(name: string): void
	{
		if (Type.isStringFilled(name))
		{
			this.#setProperty('name', name);
		}
	}

	getOriginalName(): string
	{
		return this.originalName ? this.originalName : this.getName();
	}

	/**
	 * @internal
	 */
	setOriginalName(name: string): void
	{
		if (Type.isStringFilled(name))
		{
			this.#setProperty('originalName', name);
		}
	}

	getExtension(): string
	{
		const name = this.getOriginalName();
		const position = name.lastIndexOf('.');

		return position > 0 ? name.substring(position + 1).toLowerCase() : '';
	}

	getType(): string
	{
		return this.getFile() ? this.getFile().type : this.type;
	}

	/**
	 * internal
	 */
	setType(type: string): string
	{
		if (Type.isStringFilled(type))
		{
			this.#setProperty('type', type);
		}
	}

	getSize(): number
	{
		return this.getFile() ? this.getFile().size : this.size;
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
			this.#setProperty('size', size);
		}
	}

	getId(): string
	{
		return this.id;
	}

	getServerId(): number | string | null
	{
		return this.serverId;
	}

	setServerId(id: number | string): void
	{
		if (Type.isNumber(id) || Type.isStringFilled(id))
		{
			this.#setProperty('serverId', id);
		}
	}

	getStatus(): FileStatus
	{
		return this.status;
	}

	setStatus(status: FileStatus): void
	{
		this.#setProperty('status', status);
		this.emit('onStatusChange');
	}

	getOrigin(): FileOrigin
	{
		return this.origin;
	}

	getDownloadUrl(): ?string
	{
		return this.downloadUrl;
	}

	setDownloadUrl(url: string): void
	{
		if (Type.isStringFilled(url))
		{
			this.#setProperty('downloadUrl', url);
		}
	}

	getRemoveUrl(): ?string
	{
		return this.removeUrl;
	}

	setRemoveUrl(url: string)
	{
		if (Type.isStringFilled(url))
		{
			this.#setProperty('removeUrl', url);
		}
	}

	getWidth(): ?number
	{
		return this.width;
	}

	setWidth(width: number)
	{
		if (Type.isNumber(width))
		{
			this.#setProperty('width', width);
		}
	}

	getHeight(): ?number
	{
		return this.height;
	}

	setHeight(height: ?number)
	{
		if (Type.isNumber(height))
		{
			this.#setProperty('height', height);
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

	getClientPreview(): ?File
	{
		return this.clientPreview;
	}

	setClientPreview(file: ?File, width: number = null, height: number = null): void
	{
		if (Type.isFile(file) || Type.isNull(file))
		{
			this.revokeClientPreviewUrl();

			this.#setProperty('clientPreview', file);
			this.#setProperty('clientPreviewWidth', width);
			this.#setProperty('clientPreviewHeight', height);
		}
	}

	getClientPreviewUrl(): ?string
	{
		if (this.clientPreviewUrl === null && this.getClientPreview() !== null)
		{
			this.clientPreviewUrl = URL.createObjectURL(this.getClientPreview());
		}

		return this.clientPreviewUrl;
	}

	revokeClientPreviewUrl(): void
	{
		if (this.clientPreviewUrl !== null)
		{
			URL.revokeObjectURL(this.clientPreviewUrl);
		}

		this.clientPreviewUrl = null;
	}

	getClientPreviewWidth(): ?number
	{
		return this.clientPreviewWidth;
	}

	getClientPreviewHeight(): ?number
	{
		return this.clientPreviewHeight;
	}

	getServerPreviewUrl(): ?string
	{
		return this.serverPreviewUrl;
	}

	setServerPreview(url: ?string, width: number = null, height: number = null): ?string
	{
		if (Type.isStringFilled(url) || Type.isNull(url))
		{
			this.#setProperty('serverPreviewUrl', url);
			this.#setProperty('serverPreviewWidth', width);
			this.#setProperty('serverPreviewHeight', height);
		}
	}

	getServerPreviewWidth(): ?number
	{
		return this.serverPreviewWidth;
	}

	getServerPreviewHeight(): ?number
	{
		return this.serverPreviewHeight;
	}

	isImage(): boolean
	{
		return isResizableImage(this.getOriginalName(), this.getType());
	}

	getState(): { [key: string]: any }
	{
		return JSON.parse(JSON.stringify(this));
	}

	#setProperty(name, value)
	{
		this[name] = value;
		this.emit('onStateChange');
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
