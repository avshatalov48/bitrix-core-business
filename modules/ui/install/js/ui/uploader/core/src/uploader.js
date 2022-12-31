import { Type, Event, Reflection, Dom, Extension } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import UploaderFile from './uploader-file';
import UploaderError from './uploader-error';
import Server from './backend/server';

import Filter from './filters/filter';
import FileSizeFilter from './filters/file-size-filter';
import FileTypeFilter from './filters/file-type-filter';
import ImageSizeFilter from './filters/image-size-filter';
import ImagePreviewFilter from './filters/image-preview-filter';
import TransformImageFilter from './filters/transform-image-filter';

import { FileStatus } from './enums/file-status';
import { UploaderStatus } from './enums/uploader-status';
import { FileOrigin } from './enums/file-origin';
import { FilterType } from './enums/filter-type';

import getFilesFromDataTransfer from './helpers/get-files-from-data-transfer';
import canAppendFileToForm from './helpers/can-append-file-to-form';
import assignFileToInput from './helpers/assign-file-to-input';

import type { UploaderOptions } from './types/uploader-options';
import type { UploaderFileOptions } from './types/uploader-file-options';

export default class Uploader extends EventEmitter
{
	#files: UploaderFile[] = [];
	#multiple: boolean = false;
	#autoUpload: boolean = true;
	#allowReplaceSingle: boolean = true;
	#maxParallelUploads: number = 2;
	#maxParallelLoads: number = 10;
	#acceptOnlyImages: boolean = false;
	#acceptedFileTypes: string[] = [];
	#ignoredFileNames: string[] = ['.ds_store', 'thumbs.db', 'desktop.ini'];
	#maxFileCount: ?number = null;
	#server: Server = null;

	#hiddenFields: Map<string, HTMLInputElement> = new Map();
	#hiddenFieldsContainer: HTMLElement = null;
	#hiddenFieldName: string = 'file';
	#assignAsFile: boolean = false;

	#filters: Map<FilterType, Filter[]> = new Map();
	#status: UploaderStatus = UploaderStatus.STOPPED;

	#onBeforeUploadHandler: Function = null;
	#onPrepareFileAsyncHandler: Function = null;
	#onUploadStartHandler: Function = null;
	#onFileCancelHandler: Function = null;
	#onFileStatusChangeHandler: Function = null;
	#onFileStateChangeHandler: Function = null;
	#onInputFileChangeHandler: Function = null;
	#onPasteHandler: Function = null;
	#onDropHandler: Function = null;

	#browsingNodes: Map<HTMLElement, ?Function> = new Map();
	#dropNodes: Set<HTMLElement> = new Set();
	#pastingNodes: Set<HTMLElement> = new Set();

	constructor(uploaderOptions: UploaderOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Uploader');

		const options = Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};

		this.#multiple = Type.isBoolean(options.multiple) ? options.multiple : false;
		this.#acceptOnlyImages = Type.isBoolean(options.acceptOnlyImages) ? options.acceptOnlyImages : false;

		this.#onBeforeUploadHandler = this.#handleBeforeUpload.bind(this);
		this.#onPrepareFileAsyncHandler = this.#handlePrepareFileAsync.bind(this);
		this.#onUploadStartHandler = this.#handleUploadStart.bind(this);
		this.#onFileCancelHandler = this.#handleFileCancel.bind(this);
		this.#onFileStatusChangeHandler = this.#handleFileStatusChange.bind(this);
		this.#onFileStateChangeHandler = this.#handleFileStateChange.bind(this);
		this.#onInputFileChangeHandler = this.#handleInputFileChange.bind(this);
		this.#onPasteHandler = this.#handlePaste.bind(this);
		this.#onDropHandler = this.#handleDrop.bind(this);

		this.setAutoUpload(options.autoUpload);
		this.setMaxParallelUploads(options.maxParallelUploads);
		this.setMaxParallelLoads(options.maxParallelLoads);

		if (this.#acceptOnlyImages)
		{
			const settings = Extension.getSettings('ui.uploader.core');
			const imageExtensions = settings.get('imageExtensions', 'jpg,bmp,jpeg,jpe,gif,png,webp');
			this.setAcceptedFileTypes(imageExtensions);
		}

		this.setAcceptedFileTypes(options.acceptedFileTypes);
		this.setIgnoredFileNames(options.ignoredFileNames);
		this.setMaxFileCount(options.maxFileCount);
		this.setAllowReplaceSingle(options.allowReplaceSingle);

		this.assignBrowse(options.browseElement);
		this.assignDropzone(options.dropElement);
		this.assignPaste(options.pasteElement);

		this.setHiddenFieldsContainer(options.hiddenFieldsContainer);
		this.setHiddenFieldName(options.hiddenFieldName);
		this.setAssignAsFile(options.assignAsFile);

		let serverOptions = Type.isPlainObject(options.serverOptions) ? options.serverOptions : {};
		serverOptions = Object.assign(
			{},
			{ controller: options.controller, controllerOptions: options.controllerOptions },
			serverOptions
		);

		this.#server = new Server(serverOptions);

		this.subscribeFromOptions(options.events);

		this.addFilter(FilterType.VALIDATION, new FileSizeFilter(this, options));
		this.addFilter(FilterType.VALIDATION, new FileTypeFilter(this, options));
		this.addFilter(FilterType.VALIDATION, new ImageSizeFilter(this, options));
		this.addFilter(FilterType.VALIDATION, new ImagePreviewFilter(this, options));
		this.addFilter(FilterType.PREPARATION, new TransformImageFilter(this, options));
		this.addFilters(options.filters);

		this.addFiles(options.files);
	}

	addFiles(fileList: ArrayLike): void
	{
		if (!Type.isArrayLike(fileList))
		{
			return;
		}

		const files = Array.from(fileList);
		if (this.#exceedsMaxFileCount(files))
		{
			return;
		}

		files.forEach(file => {
			if (Type.isArrayFilled(file))
			{
				this.addFile(file[0], file[1]);
			}
			else
			{
				this.addFile(file);
			}
		});
	}

	addFile(source: File | Blob | string | number, options: UploaderFileOptions)
	{
		const file = new UploaderFile(source, options);

		if (this.getIgnoredFileNames().includes(file.getName().toLowerCase()))
		{
			return;
		}

		if (this.#exceedsMaxFileCount([file]))
		{
			return;
		}

		if (!this.isMultiple() && this.shouldReplaceSingle() && this.#files.length > 0)
		{
			const fileToReplace: UploaderFile = this.#files[0];
			this.removeFile(fileToReplace);
		}

		const event = new BaseEvent({ data: { file: file } });
		this.emit('File:onBeforeAdd', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.#setLoadController(file);
		this.#setUploadController(file);

		this.#files.push(file);
		file.setStatus(FileStatus.ADDED);

		this.emit('File:onAddStart', { file });

		file.subscribe('onBeforeUpload', this.#onBeforeUploadHandler);
		file.subscribe('onPrepareFileAsync', this.#onPrepareFileAsyncHandler);
		file.subscribe('onUploadStart', this.#onUploadStartHandler);
		file.subscribe('onCancel', this.#onFileCancelHandler);
		file.subscribe('onStatusChange', this.#onFileStatusChangeHandler);
		file.subscribe('onStateChange', this.#onFileStateChangeHandler);

		if (file.getOrigin() === FileOrigin.SERVER)
		{
			file.load();
		}
		else
		{
			this.#loadNext();
		}
	}

	#setLoadController(file: UploaderFile): void
	{
		const loadController =
			file.getOrigin() === FileOrigin.SERVER
				? this.getServer().createLoadController()
				: this.getServer().createClientLoadController()
		;

		loadController.subscribeFromOptions({
			'onError': (event: BaseEvent) => {
				const { error } = event.getData();
				file.addError(error);
				file.setStatus(FileStatus.LOAD_FAILED);
				this.emit('File:onError', { file, error });

				this.#loadNext();
			},
			'onAbort': (event: BaseEvent) => {
				if (file.getOrigin() === FileOrigin.SERVER)
				{
					file.setStatus(FileStatus.ABORTED);
				}
				else
				{
					file.setStatus(FileStatus.LOAD_FAILED);
				}

				this.emit('File:onAbort', { file });
				this.#loadNext();
			},
			'onProgress': (event: BaseEvent) => {
				this.emit('File:onLoadProgress', { file, progress: event.getData().progress });
			},
			'onLoad': (event: BaseEvent) => {
				if (file.getOrigin() === FileOrigin.SERVER)
				{
					file.setFile(event.getData().fileInfo);
					file.setStatus(FileStatus.COMPLETE);
					this.emit('File:onAdd', { file });
					this.emit('File:onLoadComplete', { file });
					this.emit('File:onComplete', { file });

					this.#setHiddenField(file);

					return;
				}

				// Validation
				this.#applyFilters(FilterType.VALIDATION, file)
					.then(() => {
						if (file.isUploadable())
						{
							file.setStatus(FileStatus.PENDING);
							this.emit('File:onAdd', { file });
							this.emit('File:onLoadComplete', { file });

							if (this.shouldAutoUpload())
							{
								file.upload();
							}
						}
						else
						{
							file.setStatus(FileStatus.COMPLETE);
							this.emit('File:onAdd', { file });
							this.emit('File:onLoadComplete', { file });
							this.emit('File:onComplete', { file });
						}

						this.#loadNext();
					})
					.catch(error => {
						file.addError(error);
						file.setStatus(FileStatus.LOAD_FAILED);
						this.emit('File:onError', { file, error });
						this.emit('File:onAdd', { file, error });
						this.#loadNext();
					})
				;
			},
		});

		file.setLoadController(loadController);
	}

	#setUploadController(file: UploaderFile): void
	{
		const uploadController = this.getServer().createUploadController();
		if (!uploadController)
		{
			return;
		}

		uploadController.subscribeFromOptions({
			'onError': (event: BaseEvent) => {
				const { error } = event.getData();
				file.addError(error);
				file.setStatus(FileStatus.UPLOAD_FAILED);
				this.emit('File:onError', { file, error });
				this.#uploadNext();
			},
			'onAbort': (event: BaseEvent) => {
				file.setStatus(FileStatus.ABORTED);
				this.emit('File:onAbort', { file });
				this.#uploadNext();
			},
			'onProgress': (event: BaseEvent) => {
				const { progress } = event.getData();
				file.setProgress(progress);
				this.emit('File:onUploadProgress', { file, progress });
			},
			'onUpload': (event: BaseEvent) => {
				file.setStatus(FileStatus.COMPLETE);
				file.setFile(event.getData().fileInfo);
				this.emit('File:onUploadComplete', { file });
				this.emit('File:onComplete', { file });

				this.#setHiddenField(file);
				this.#uploadNext();
			},
		});

		file.setUploadController(uploadController);
	}

	#exceedsMaxFileCount(fileList: Array): boolean
	{
		const totalNewFiles = fileList.length;
		const totalFiles = this.#files.length;

		if (!this.isMultiple() && totalNewFiles > 1)
		{
			return true;
		}

		let maxFileCount;
		if (this.isMultiple())
		{
			maxFileCount = this.getMaxFileCount();
		}
		else
		{
			maxFileCount = this.shouldReplaceSingle() ? null : 1;
		}

		if (maxFileCount !== null && totalFiles + totalNewFiles > maxFileCount)
		{
			const error = new UploaderError('MAX_FILE_COUNT_EXCEEDED', { maxFileCount });
			this.emit('onMaxFileCountExceeded', { error });
			this.emit('onError', { error });

			return true;
		}

		return false;
	}

	#applyFilters(type: FilterType, ...args): Promise
	{
		return new Promise((resolve, reject) => {
			const filters = [...(this.#filters.get(type) || [])];
			if (filters.length === 0)
			{
				resolve();
				return;
			}

			const firstFilter: Filter = filters.shift();

			// chain filters
			filters
				.reduce(
					(current: Promise, next: Filter) => {
						return current.then(() => next.apply(...args));
					},
					firstFilter.apply(...args)
				)
				.then((result) => resolve(result))
				.catch(error => reject(error))
			;
		});
	}

	start(): void
	{
		if (this.getStatus() !== UploaderStatus.STARTED && this.getPendingFileCount() > 0)
		{
			this.#status = UploaderStatus.STARTED;
			this.emit('onUploadStart');
			this.#uploadNext();
		}
	}

	stop(): void
	{
		this.#status = UploaderStatus.STOPPED;

		this.#files.forEach((file: UploaderFile) => {
			if (file.isUploading())
			{
				file.abort();
				file.setStatus(FileStatus.PENDING);
			}
		});

		this.emit('onStop');
	}

	cancel(): void
	{
		this.#files.forEach((file: UploaderFile) => {
			file.cancel();
		});
	}

	destroy(): void
	{
		this.emit('onDestroy');

		this.unassignBrowseAll();
		this.unassignDropzoneAll();
		this.unassignPasteAll();

		this.#files.forEach((file: UploaderFile) => {
			file.cancel();
		});

		this.#files = [];
		this.#server = null;
		this.#acceptedFileTypes = null;
		this.#ignoredFileNames = null;
		this.#filters = null;
		// #hiddenFields

		Object.setPrototypeOf(this, null);
	}

	removeFile(file: UploaderFile | string): void
	{
		if (Type.isString(file))
		{
			file = this.getFile(file);
		}

		const index = this.#files.findIndex(element => element === file);
		if (index >= 0)
		{
			this.#files.splice(index, 1);

			file.abort();
			file.setStatus(FileStatus.INIT);
			this.emit('File:onRemove', { file });

			this.#resetHiddenField(file);
		}
	}

	getFile(id: string): ?UploaderFile
	{
		return this.#files.find((file: UploaderFile) => file.getId() === id) || null;
	}

	getFiles(): UploaderFile[]
	{
		return Array.from(this.#files);
	}

	isMultiple(): boolean
	{
		return this.#multiple;
	}

	getStatus(): UploaderStatus
	{
		return this.#status;
	}

	addFilter(type: FilterType, filter: Filter | Function | string, filterOptions: { [key: string]: any } = {}): void
	{
		if (Type.isFunction(filter) || Type.isString(filter))
		{
			const className = Type.isString(filter) ? Reflection.getClass(filter) : filter;
			if (Type.isFunction(className))
			{
				filter = new className(this, filterOptions);
			}
		}

		if (filter instanceof Filter)
		{
			let filters = this.#filters.get(type);
			if (!Type.isArray(filters))
			{
				filters = [];
				this.#filters.set(type, filters);
			}

			filters.push(filter);
		}
		else
		{
			throw new Error('Uploader: a filter must be an instance of FileUploader.Filter.');
		}
	}

	addFilters(filters: Array): void
	{
		if (Type.isArray(filters))
		{
			filters.forEach(filter => {
				if (Type.isPlainObject(filter))
				{
					this.addFilter(filter.type, filter.filter, filter.options);
				}
			});
		}
	}

	getServer(): Server
	{
		return this.#server;
	}

	assignBrowse(nodes: HTMLElement | HTMLElement[]): void
	{
		nodes = Type.isElementNode(nodes) ? [nodes] : nodes;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement | HTMLInputElement) => {
			if (!Type.isElementNode(node) || this.#browsingNodes.has(node))
			{
				return;
			}

			let input: HTMLInputElement = null;
			if (node.tagName === 'INPUT' && node.type === 'file')
			{
				input = node;

				// Add already selected files
				if (input.files && input.files.length)
				{
					this.addFiles(input.files);
				}

				const acceptAttr = input.getAttribute('accept');
				if (Type.isStringFilled(acceptAttr))
				{
					this.setAcceptedFileTypes(acceptAttr);
				}

				this.#browsingNodes.set(node, null);
			}
			else
			{
				input = document.createElement('input');
				input.setAttribute('type', 'file');

				const onBrowseClickHandler = this.#handleBrowseClick.bind(this, input, node);
				this.#browsingNodes.set(node, onBrowseClickHandler);
				Event.bind(node, 'click', onBrowseClickHandler);
			}

			if (this.isMultiple())
			{
				input.setAttribute('multiple', 'multiple');
			}

			if (Type.isArrayFilled(this.getAcceptedFileTypes()))
			{
				input.setAttribute('accept', this.getAcceptedFileTypes().join(','));
			}

			Event.bind(input, 'change', this.#onInputFileChangeHandler);
		});
	}

	#handleBrowseClick(input: HTMLInputElement, node: HTMLElement): void
	{
		const event = new BaseEvent({ data: { input, node } });
		this.emit('onBeforeBrowse', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		input.click();
	}

	#handleInputFileChange(event: Event): void
	{
		const input = event.currentTarget;

		this.addFiles(Array.from(input.files));

		// reset file input
		input.value = '';
	}

	unassignBrowse(nodes: HTMLElement | HTMLElement[]): void
	{
		nodes = Type.isElementNode(nodes) ? [nodes] : nodes;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement | HTMLInputElement) => {
			if (this.#browsingNodes.has(node))
			{
				Event.unbind(node, 'click', this.#browsingNodes.get(node));
				Event.unbind(node, 'change', this.#onInputFileChangeHandler);
				this.#browsingNodes.delete(node);
			}
		});
	}

	unassignBrowseAll(): void
	{
		Array.from(this.#browsingNodes.keys()).forEach(node => {
			this.unassignBrowse(node);
		});
	}

	assignDropzone(nodes: HTMLElement | HTMLElement[]): void
	{
		nodes = Type.isElementNode(nodes) ? [nodes] : nodes;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement) => {
			if (!Type.isElementNode(node) || this.#dropNodes.has(node))
			{
				return;
			}

			Event.bind(node, 'dragover', this.#preventDefault);
			Event.bind(node, 'dragenter', this.#preventDefault);
			Event.bind(node, 'drop', this.#onDropHandler);

			this.#dropNodes.add(node);
		});
	}

	#handleDrop(dragEvent: DragEvent): void
	{
		dragEvent.preventDefault();

		const event = new BaseEvent({ data: { dragEvent } });
		this.emit('onBeforeDrop', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		getFilesFromDataTransfer(dragEvent.dataTransfer).then((files: File[]) => {
			this.addFiles(files);
		});
	}

	#preventDefault(event: DragEvent): void
	{
		event.preventDefault();
	}

	unassignDropzone(nodes: HTMLElement | HTMLElement[]): void
	{
		nodes = Type.isElementNode(nodes) ? [nodes] : nodes;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement) => {
			if (this.#dropNodes.has(node))
			{
				Event.unbind(node, 'dragover', this.#preventDefault);
				Event.unbind(node, 'dragenter', this.#preventDefault);
				Event.unbind(node, 'drop', this.#onDropHandler);
				this.#dropNodes.delete(node);
			}
		});
	}

	unassignDropzoneAll(): void
	{
		Array.from(this.#dropNodes).forEach(node => {
			this.unassignDropzone(node);
		});
	}

	assignPaste(nodes: HTMLElement | HTMLElement[]): void
	{
		nodes = Type.isElementNode(nodes) ? [nodes] : nodes;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement) => {
			if (!Type.isElementNode(node) || this.#pastingNodes.has(node))
			{
				return;
			}

			Event.bind(node, 'paste', this.#onPasteHandler);
			this.#pastingNodes.add(node);
		});
	}

	#handlePaste(clipboardEvent: ClipboardEvent): void
	{
		clipboardEvent.preventDefault();

		const clipboardData: DataTransfer = clipboardEvent.clipboardData;
		if (!clipboardData)
		{
			return;
		}

		const event = new BaseEvent({ data: { clipboardEvent } });
		this.emit('onBeforePaste', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		getFilesFromDataTransfer(clipboardData).then((files: File[]) => {
			this.addFiles(files);
		});
	}

	unassignPaste(nodes: HTMLElement | HTMLElement[]): void
	{
		nodes = Type.isElementNode(nodes) ? [nodes] : nodes;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement) => {
			if (this.#pastingNodes.has(node))
			{
				Event.unbind(node, 'paste', this.#onPasteHandler);
				this.#pastingNodes.delete(node);
			}
		});
	}

	unassignPasteAll(): void
	{
		Array.from(this.#pastingNodes).forEach(node => {
			this.unassignPaste(node);
		});
	}

	getHiddenFieldsContainer(): ?HTMLElement
	{
		let element = null;
		if (Type.isStringFilled(this.#hiddenFieldsContainer))
		{
			element = document.querySelector(this.#hiddenFieldsContainer);
		}
		else if (Type.isElementNode(this.#hiddenFieldsContainer))
		{
			element = this.#hiddenFieldsContainer;
		}

		return element;
	}

	setHiddenFieldsContainer(container: string | HTMLElement): void
	{
		if (Type.isStringFilled(container) || Type.isElementNode(container) || Type.isNull(container))
		{
			this.#hiddenFieldsContainer = container;
		}
	}

	getHiddenFieldName(): string
	{
		return this.#hiddenFieldName;
	}

	setHiddenFieldName(name: string)
	{
		if (Type.isStringFilled(name))
		{
			this.#hiddenFieldName = name;
		}
	}

	shouldAssignAsFile(): boolean
	{
		return this.#assignAsFile;
	}

	setAssignAsFile(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#assignAsFile = flag;
		}
	}

	getTotalSize(): number
	{
		return this.#files.reduce((totalSize: number, file: UploaderFile) => {
			return totalSize + file.getSize();
		}, 0);
	}

	shouldAutoUpload(): boolean
	{
		return this.#autoUpload;
	}

	setAutoUpload(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#autoUpload = flag;
		}
	}

	getMaxParallelUploads(): number
	{
		return this.#maxParallelUploads;
	}

	setMaxParallelUploads(number: number): void
	{
		if (Type.isNumber(number) && number > 0)
		{
			this.#maxParallelUploads = number;
		}
	}

	getMaxParallelLoads(): number
	{
		return this.#maxParallelLoads;
	}

	setMaxParallelLoads(number: number): void
	{
		if (Type.isNumber(number) && number > 0)
		{
			this.#maxParallelLoads = number;
		}
	}

	getUploadingFileCount(): number
	{
		return this.#files.filter(file => file.isUploading()).length;
	}

	getPendingFileCount(): number
	{
		return this.#files.filter(file => file.isReadyToUpload()).length;
	}

	shouldAcceptOnlyImages(): boolean
	{
		return this.#acceptOnlyImages;
	}

	getAcceptedFileTypes(): string[]
	{
		return this.#acceptedFileTypes;
	}

	setAcceptedFileTypes(fileTypes: string | string[]): void
	{
		if (Type.isString(fileTypes))
		{
			fileTypes = fileTypes.split(',');
		}

		if (Type.isArray(fileTypes))
		{
			this.#acceptedFileTypes = [];

			fileTypes.forEach(type => {
				if (Type.isStringFilled(type))
				{
					this.#acceptedFileTypes.push(type);
				}
			});
		}
	}

	getIgnoredFileNames(): string[]
	{
		return this.#ignoredFileNames;
	}

	setIgnoredFileNames(fileNames: string[]): void
	{
		if (Type.isArray(fileNames))
		{
			this.#ignoredFileNames = [];

			fileNames.forEach(fileName => {
				if (Type.isStringFilled(fileName))
				{
					this.#ignoredFileNames.push(fileName.toLowerCase());
				}
			});
		}
	}

	setMaxFileCount(maxFileCount: ?number): void
	{
		if ((Type.isNumber(maxFileCount) && maxFileCount > 0) || maxFileCount === null)
		{
			this.#maxFileCount = maxFileCount;
		}
	}

	getMaxFileCount(): ?number
	{
		return this.#maxFileCount;
	}

	setAllowReplaceSingle(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#allowReplaceSingle = flag;
		}
	}

	shouldReplaceSingle(): boolean
	{
		return this.#allowReplaceSingle;
	}

	#uploadNext(): void
	{
		if (this.getStatus() !== UploaderStatus.STARTED)
		{
			return;
		}

		const maxParallelUploads = this.getMaxParallelUploads();
		const currentUploads = this.getUploadingFileCount();
		const pendingFiles = this.#files.filter(file => file.isReadyToUpload());
		const pendingUploads = pendingFiles.length;

		if (currentUploads < maxParallelUploads)
		{
			const limit = Math.min(maxParallelUploads - currentUploads, pendingFiles.length);
			for (let i = 0; i < limit; i++)
			{
				const pendingFile = pendingFiles[i];
				pendingFile.upload();
			}
		}

		// All files are COMPLETE or FAILED
		if (currentUploads === 0 && pendingUploads === 0)
		{
			this.#status = UploaderStatus.STOPPED;
			this.emit('onUploadComplete');
		}
	}

	#loadNext(): void
	{
		const maxParallelLoads = this.getMaxParallelLoads();
		const currentLoads = this.#files.filter(file => file.isLoading()).length;
		const pendingFiles = this.#files.filter(file => {
			return file.getStatus() === FileStatus.ADDED && file.getOrigin() === FileOrigin.CLIENT;
		});

		if (currentLoads < maxParallelLoads)
		{
			const limit = Math.min(maxParallelLoads - currentLoads, pendingFiles.length);
			for (let i = 0; i < limit; i++)
			{
				const pendingFile = pendingFiles[i];
				pendingFile.load();
			}
		}
	}

	#handleBeforeUpload(event: BaseEvent): void
	{
		if (this.getStatus() === UploaderStatus.STOPPED)
		{
			event.preventDefault();
			this.start();
		}
		else
		{
			if (this.getUploadingFileCount() >= this.getMaxParallelUploads())
			{
				event.preventDefault();
			}
		}
	}

	#handlePrepareFileAsync(event: BaseEvent): void
	{
		return new Promise((resolve, reject) => {
			const { file } = event.getData();
			this.#applyFilters(FilterType.PREPARATION, file)
				.then((transformedFile: File) => {
					if (Type.isFile(transformedFile))
					{
						resolve(transformedFile);
					}
					else
					{
						resolve(file);
					}
				})
				.catch(error => reject(error))
			;
		});
	}

	#handleUploadStart(event: BaseEvent): void
	{
		const file: UploaderFile = event.getTarget();
		this.emit('File:onUploadStart', { file });
	}

	#handleFileCancel(event: BaseEvent): void
	{
		const file: UploaderFile = event.getTarget();
		this.emit('File:onCancel', { file });

		this.removeFile(file);
	}

	#handleFileStatusChange(event: BaseEvent): void
	{
		const file: UploaderFile = event.getTarget();
		this.emit('File:onStatusChange', { file });
	}

	#handleFileStateChange(event: BaseEvent): void
	{
		const file: UploaderFile = event.getTarget();
		this.emit('File:onStateChange', { file });
	}

	#setHiddenField(file: UploaderFile): void
	{
		const container = this.getHiddenFieldsContainer();
		if (!container || this.#hiddenFields.has(file.getId()))
		{
			return;
		}

		// TODO: is it needed?
		const isExistingServerFile = Type.isNumber(file.getServerId());
		if (isExistingServerFile)
		{
			return;
		}

		const assignAsFile =
			file.getOrigin() === FileOrigin.CLIENT
			&& !file.isUploadable()
			&& this.shouldAssignAsFile()
			&& canAppendFileToForm()
		;

		const input = document.createElement('input');
		input.type = assignAsFile ? 'file' : 'hidden';
		input.name = this.getHiddenFieldName() + (this.isMultiple() ? '[]' : '');

		if (assignAsFile)
		{
			Dom.style(input, {
				visibility: 'hidden',
				left: 0,
				top: 0,
				width: 0,
				height: 0,
				position: 'absolute',
				'pointer-events': 'none',
			});

			assignFileToInput(input, file.getFile());
		}
		else if (file.getServerId() !== null)
		{
			input.value = file.getServerId();
		}

		container.appendChild(input);
		this.#hiddenFields.set(file.getId(), input);

		this.#syncInputPositions();
	}

	#resetHiddenField(file: UploaderFile): void
	{
		const input = this.#hiddenFields.get(file.getId());
		if (input)
		{
			Dom.remove(input);
			this.#hiddenFields.delete(file.getId());
		}
	}

	#syncInputPositions(): void
	{
		const container = this.getHiddenFieldsContainer();
		if (!container)
		{
			return;
		}

		this.#files.forEach((file: UploaderFile) => {
			const input = this.#hiddenFields.get(file.getId());
			if (input)
			{
				container.appendChild(input);
			}
		});
	}
}
