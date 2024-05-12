import { Type, Event, Reflection, Dom, Extension, Text, type JsonObject } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import UploaderFile from './uploader-file';
import UploaderError from './uploader-error';
import Server from './backend/server';
import UploadController from './backend/upload-controller';

import Filter from './filters/filter';
import FileSizeFilter from './filters/file-size-filter';
import FileTypeFilter from './filters/file-type-filter';
import ImageSizeFilter from './filters/image-size-filter';
import ImagePreviewFilter from './filters/image-preview-filter';
import ImageResizeFilter from './filters/image-resize-filter';

import { UploaderStatus } from './enums/uploader-status';
import { UploaderEvent } from './enums/uploader-event';
import { FileStatus } from './enums/file-status';
import { FileOrigin } from './enums/file-origin';
import { FileEvent } from './enums/file-event';
import { FilterType } from './enums/filter-type';

import { getFilesFromDataTransfer, isFilePasted } from './helpers/get-files-from-data-transfer';
import canAppendFileToForm from './helpers/can-append-file-to-form';
import assignFileToInput from './helpers/assign-file-to-input';

import type { UploaderOptions } from './types/uploader-options';
import type { UploaderFileOptions } from './types/uploader-file-options';
import type { ServerOptions } from './types/server-options';
import type { RemoveFileOptions } from './types/remove-file-options';
import type { DestroyOptions } from './types/destroy-options';

const instances = new Map();

/**
 * @namespace BX.UI.Uploader
 */
export default class Uploader extends EventEmitter
{
	#id: ?string = null;
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
	#assignServerFile: boolean = true;

	#filters: Map<FilterType, Filter[]> = new Map();
	#status: UploaderStatus = UploaderStatus.STOPPED;

	#onBeforeUploadHandler: Function = null;
	#onFileStatusChangeHandler: Function = null;
	#onFileStateChangeHandler: Function = null;
	#onInputFileChangeHandler: Function = null;
	#onPasteHandler: Function = null;
	#onDropHandler: Function = null;

	#browsingNodes: Map<HTMLElement, ?Function> = new Map();
	#dropNodes: Set<HTMLElement> = new Set();
	#pastingNodes: Set<HTMLElement> = new Set();

	static getById(id: string): ?Uploader
	{
		return instances.get(id) || null;
	}

	static getInstances(): Uploader[]
	{
		return [...instances.values()];
	}

	constructor(uploaderOptions: UploaderOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Uploader');

		this.#onBeforeUploadHandler = this.#handleBeforeUpload.bind(this);
		this.#onFileStatusChangeHandler = this.#handleFileStatusChange.bind(this);
		this.#onFileStateChangeHandler = this.#handleFileStateChange.bind(this);
		this.#onInputFileChangeHandler = this.#handleInputFileChange.bind(this);
		this.#onPasteHandler = this.#handlePaste.bind(this);
		this.#onDropHandler = this.#handleDrop.bind(this);

		const options: UploaderOptions = Type.isPlainObject(uploaderOptions) ? ({ ...uploaderOptions }) : {};
		this.#id = Type.isStringFilled(options.id) ? options.id : `ui-uploader-${Text.getRandom().toLowerCase()}`;
		this.#multiple = Type.isBoolean(options.multiple) ? options.multiple : false;

		const acceptOnlyImages: ?boolean = Type.isBoolean(options.acceptOnlyImages) ? options.acceptOnlyImages : null;
		const acceptOnlyImagesGlobal: ?boolean = Uploader.getGlobalOption('acceptOnlyImages', null);
		this.setAcceptOnlyImages(acceptOnlyImages || acceptOnlyImagesGlobal);

		if (Type.isString(options.acceptedFileTypes) || Type.isArray(options.acceptedFileTypes))
		{
			this.setAcceptedFileTypes(options.acceptedFileTypes);
		}
		else if (acceptOnlyImages !== true)
		{
			const acceptedFileTypesGlobal = Uploader.getGlobalOption('acceptedFileTypes', null);
			this.setAcceptedFileTypes(acceptedFileTypesGlobal);
		}

		const ignoredFileNames: ?string[] = (
			Type.isArray(options.ignoredFileNames)
				? options.ignoredFileNames
				: Uploader.getGlobalOption('ignoredFileNames', null)
		);
		this.setIgnoredFileNames(ignoredFileNames);

		this.setMaxFileCount(options.maxFileCount);
		this.setAllowReplaceSingle(options.allowReplaceSingle);

		this.assignBrowse(options.browseElement);
		this.assignDropzone(options.dropElement);
		this.assignPaste(options.pasteElement);

		this.setHiddenFieldsContainer(options.hiddenFieldsContainer);
		this.setHiddenFieldName(options.hiddenFieldName);
		this.setAssignAsFile(options.assignAsFile);
		this.setAssignServerFile(options.assignServerFile);

		this.setAutoUpload(options.autoUpload);
		this.setMaxParallelUploads(options.maxParallelUploads);
		this.setMaxParallelLoads(options.maxParallelLoads);

		let serverOptions: ServerOptions = Type.isPlainObject(options.serverOptions) ? options.serverOptions : {};
		serverOptions = {
			controller: options.controller,
			controllerOptions: options.controllerOptions,
			...serverOptions,
		};

		this.#server = new Server(serverOptions);

		this.subscribeFromOptions(options.events);

		this.addFilter(FilterType.VALIDATION, new FileSizeFilter(this, options));
		this.addFilter(FilterType.VALIDATION, new FileTypeFilter(this, options));
		this.addFilter(FilterType.VALIDATION, new ImageSizeFilter(this, options));
		this.addFilter(FilterType.VALIDATION, new ImagePreviewFilter(this, options));
		this.addFilter(FilterType.PREPARATION, new ImageResizeFilter(this, options));
		this.addFilters(options.filters);

		this.addFiles(options.files);

		instances.set(this.#id, this);
	}

	static getGlobalOption(path: string, defaultValue: any = null): any
	{
		const globalOptions = Extension.getSettings('ui.uploader.core');

		return globalOptions.get(path, defaultValue);
	}

	addFiles(fileList: ArrayLike): UploaderFile[]
	{
		if (!Type.isArrayLike(fileList))
		{
			return [];
		}

		const files: UploaderFile[] = [];
		[...fileList].forEach((item) => {
			if (item instanceof UploaderFile)
			{
				if (item.getStatus() === FileStatus.INIT)
				{
					files.push(item);
				}
			}
			else if (Type.isArrayFilled(item))
			{
				files.push(new UploaderFile(item[0], item[1]));
			}
			else
			{
				files.push(new UploaderFile(item));
			}
		});

		const event: BaseEvent = new BaseEvent({ data: { files: [...files] } });
		this.emit(UploaderEvent.BEFORE_FILES_ADD, event);
		if (event.isDefaultPrevented())
		{
			const { error } = event.getData();
			if (error instanceof UploaderError)
			{
				this.emit(UploaderEvent.ERROR, { error });
			}

			return [];
		}

		if (this.#exceedsMaxFileCount(files))
		{
			return [];
		}

		const results = [];
		files.forEach((file) => {
			const result: UploaderFile | null = this.addFile(file);
			if (result !== null)
			{
				results.push(result);
			}
		});

		return results;
	}

	addFile(
		source: File | Blob | string | number | UploaderFileOptions,
		options: UploaderFileOptions,
	): UploaderFile | null
	{
		let file: UploaderFile = null;
		if (source instanceof UploaderFile)
		{
			if (source.getStatus() === FileStatus.INIT)
			{
				file = source;
			}
			else
			{
				return null;
			}
		}
		else
		{
			file = new UploaderFile(source, options);
		}

		if (this.getIgnoredFileNames().includes(file.getName().toLowerCase()))
		{
			return null;
		}

		if (this.#exceedsMaxFileCount([file]))
		{
			return null;
		}

		if (!this.isMultiple() && this.shouldReplaceSingle() && this.#files.length > 0)
		{
			const fileToReplace: UploaderFile = this.#files[0];
			this.removeFile(fileToReplace);
		}

		const event: BaseEvent = new BaseEvent({ data: { file } });
		this.emit(UploaderEvent.FILE_BEFORE_ADD, event);
		if (event.isDefaultPrevented())
		{
			return null;
		}

		file.subscribe(FileEvent.STATUS_CHANGE, this.#onFileStatusChangeHandler);
		file.subscribe(FileEvent.STATE_CHANGE, this.#onFileStateChangeHandler);

		this.#setUploadEvents(file);
		this.#setLoadEvents(file);
		this.#setRemoveEvents(file);

		if (!file.isLoadable())
		{
			if (file.getOrigin() === FileOrigin.SERVER)
			{
				const preloaded: boolean = Type.isStringFilled(file.getName());
				if (!preloaded || file.shouldForceServerLoad())
				{
					file.setLoadController(this.getServer().createServerLoadController());
				}
				else
				{
					file.setLoadController(this.getServer().createServerlessLoadController());
				}
			}
			else
			{
				file.setLoadController(this.getServer().createClientLoadController());
			}
		}

		if (!file.isUploadable() && file.getOrigin() === FileOrigin.CLIENT)
		{
			const uploadController: UploadController | null = this.getServer().createUploadController();
			file.setUploadController(uploadController);
		}

		if (!file.isRemoveable())
		{
			file.setRemoveController(this.getServer().createRemoveController());
		}

		this.#files.push(file);

		file.emit(FileEvent.ADD);
		this.emit(UploaderEvent.FILE_ADD_START, { file });

		if (file.getOrigin() === FileOrigin.CLIENT)
		{
			this.#loadNext();
		}
		else
		{
			file.load();
		}

		return file;
	}

	#setLoadEvents(file: UploaderFile): void
	{
		file.subscribeFromOptions({
			[FileEvent.LOAD_START]: (): void => {
				this.emit(UploaderEvent.FILE_LOAD_START, { file });
			},
			[FileEvent.LOAD_PROGRESS]: (event: BaseEvent): void => {
				const { progress } = event.getData();
				this.emit(UploaderEvent.FILE_LOAD_PROGRESS, { file, progress });
			},
			[FileEvent.LOAD_ERROR]: (event: BaseEvent): void => {
				const { error } = event.getData();
				this.emit(UploaderEvent.FILE_ERROR, { file, error });
				this.emit(UploaderEvent.FILE_ADD, { file, error });
				this.#loadNext();
			},
			[FileEvent.LOAD_COMPLETE]: (): void => {
				this.emit(UploaderEvent.FILE_ADD, { file });
				this.emit(UploaderEvent.FILE_LOAD_COMPLETE, { file });

				if (!file.isUploadable())
				{
					this.emit(UploaderEvent.FILE_COMPLETE, { file });
					this.#setHiddenField(file);
				}
				else if (this.shouldAutoUpload())
				{
					file.upload();
				}

				this.#loadNext();
			},
			[FileEvent.VALIDATE_FILE_ASYNC]: (event: BaseEvent) => {
				const file: UploaderFile = event.getData().file;

				return this.#applyFilters(FilterType.VALIDATION, file);
			},
			[FileEvent.PREPARE_FILE_ASYNC]: (event: BaseEvent) => {
				const file: UploaderFile = event.getData().file;

				return this.#applyFilters(FilterType.PREPARATION, file);
			},
		});
	}

	#setUploadEvents(file: UploaderFile): void
	{
		file.subscribeFromOptions({
			[FileEvent.BEFORE_UPLOAD]: this.#onBeforeUploadHandler,
			[FileEvent.UPLOAD_START]: (): void => {
				this.emit(UploaderEvent.FILE_UPLOAD_START, { file });
			},
			[FileEvent.UPLOAD_PROGRESS]: (event: BaseEvent): void => {
				const { progress } = event.getData();
				this.emit(UploaderEvent.FILE_UPLOAD_PROGRESS, { file, progress });
			},
			[FileEvent.UPLOAD_ERROR]: (event: BaseEvent): void => {
				const { error } = event.getData();
				this.emit(UploaderEvent.FILE_ERROR, { file, error });
				this.#uploadNext();
			},
			[FileEvent.UPLOAD_COMPLETE]: (): void => {
				this.emit(UploaderEvent.FILE_UPLOAD_COMPLETE, { file });
				this.emit(UploaderEvent.FILE_COMPLETE, { file });
				this.#setHiddenField(file);
				this.#uploadNext();
			},
		});
	}

	#setRemoveEvents(file: UploaderFile): void
	{
		file.subscribeOnce(FileEvent.REMOVE_ERROR, (event: BaseEvent): void => {
			const { error } = event.getData();
			this.emit(UploaderEvent.FILE_ERROR, { file, error });
		});

		file.subscribeOnce(FileEvent.REMOVE_COMPLETE, (): void => {
			this.#removeFile(file);
		});
	}

	#handleBeforeUpload(event: BaseEvent): void
	{
		if (this.getStatus() === UploaderStatus.STOPPED)
		{
			event.preventDefault();
			this.start();
		}
		else if (this.getUploadingFileCount() >= this.getMaxParallelUploads())
		{
			event.preventDefault();
		}
	}

	#handleFileStatusChange(event: BaseEvent): void
	{
		const file: UploaderFile = event.getTarget();
		this.emit(UploaderEvent.FILE_STATUS_CHANGE, { file });
	}

	#handleFileStateChange(event: BaseEvent): void
	{
		const file: UploaderFile = event.getTarget();
		const property: string = event.getData().property;
		const value: any = event.getData().value;

		if (property === 'serverFileId')
		{
			this.#updateHiddenField(file);
		}

		this.emit(UploaderEvent.FILE_STATE_CHANGE, { file, property, value });
	}

	#exceedsMaxFileCount(fileList: Array): boolean
	{
		const totalNewFiles: number = fileList.length;
		const totalFiles: number = this.#files.length;

		if (!this.isMultiple() && totalNewFiles > 1)
		{
			return true;
		}

		let maxFileCount = null;
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
			const error: UploaderError = new UploaderError('MAX_FILE_COUNT_EXCEEDED', { maxFileCount });
			this.emit(UploaderEvent.MAX_FILE_COUNT_EXCEEDED, { error });
			this.emit(UploaderEvent.ERROR, { error });

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
					firstFilter.apply(...args),
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
			this.emit(UploaderEvent.UPLOAD_START);
			this.#uploadNext();
		}
	}

	// stop(): void
	// {
	// 	this.#status = UploaderStatus.STOPPED;
	//
	// 	this.getFiles().forEach((file: UploaderFile) => {
	// 		if (file.isUploading())
	// 		{
	// 			file.abort();
	// 			file.setStatus(FileStatus.PENDING);
	// 		}
	// 	});
	//
	// 	this.emit('onStop');
	// }

	destroy(options?: DestroyOptions): void
	{
		this.emit(UploaderEvent.DESTROY);

		this.unassignBrowseAll();
		this.unassignDropzoneAll();
		this.unassignPasteAll();

		const removeFromServer: boolean = !options || options.removeFilesFromServer !== false;
		this.removeFiles({ removeFromServer });

		this.#resetHiddenFields();
		instances.delete(this.getId());

		this.#files = [];
		this.#server = null;
		this.#acceptedFileTypes = null;
		this.#ignoredFileNames = null;
		this.#filters = null;

		Object.setPrototypeOf(this, null);
	}

	removeFiles(options?: RemoveFileOptions): void
	{
		this.getFiles().forEach((file: UploaderFile): void => {
			file.remove(options);
		});
	}

	removeFile(fileOrId: UploaderFile | string, options?: RemoveFileOptions): void
	{
		const file: UploaderFile = Type.isString(fileOrId) ? this.getFile(fileOrId) : fileOrId;
		const index: number = this.#files.indexOf(file);
		if (index === -1)
		{
			return;
		}

		file.remove(options);
	}

	#removeFile(file: UploaderFile)
	{
		const index: number = this.#files.indexOf(file);
		if (index !== -1)
		{
			this.#files.splice(index, 1);
		}

		file.unsubscribeAll();

		this.emit(UploaderEvent.FILE_REMOVE, { file });
		this.#resetHiddenField(file);
	}

	getFile(id: string): ?UploaderFile
	{
		return this.#files.find((file: UploaderFile): boolean => file.getId() === id) || null;
	}

	getFiles(): UploaderFile[]
	{
		return [...this.#files];
	}

	getId(): string
	{
		return this.#id;
	}

	isMultiple(): boolean
	{
		return this.#multiple;
	}

	getStatus(): UploaderStatus
	{
		return this.#status;
	}

	addFilter(type: FilterType, filterEntity: Filter | Class<Filter> | string, filterOptions: JsonObject = {}): void
	{
		let filter: Filter = null;
		if (Type.isFunction(filterEntity) || Type.isString(filterEntity))
		{
			const ClassName: Class<Filter> = Type.isString(filterEntity) ? Reflection.getClass(filterEntity) : filterEntity;
			if (Type.isFunction(ClassName))
			{
				filter = new ClassName(this, filterOptions);
			}
		}
		else
		{
			filter = filterEntity;
		}

		if (filter instanceof Filter)
		{
			let filters: Filter[] = this.#filters.get(type);
			if (!Type.isArray(filters))
			{
				filters = [];
				this.#filters.set(type, filters);
			}

			filters.push(filter);
		}
		else
		{
			throw new TypeError('Uploader: a filter must be an instance of FileUploader.Filter.');
		}
	}

	addFilters(filters: Array): void
	{
		if (Type.isArray(filters))
		{
			filters.forEach((filter): void => {
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

	assignBrowse(htmlElement: HTMLElement | HTMLElement[]): void
	{
		const nodes: HTMLElement[] = Type.isElementNode(htmlElement) ? [htmlElement] : htmlElement;
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
				if (input.files && input.files.length > 0)
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
		const event: BaseEvent = new BaseEvent({ data: { input, node } });
		this.emit(UploaderEvent.BEFORE_BROWSE, event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		input.click();
	}

	#handleInputFileChange(event: Event): void
	{
		const input = event.currentTarget;

		this.addFiles([...input.files]);

		// reset file input
		input.value = '';
	}

	unassignBrowse(htmlElement: HTMLElement | HTMLElement[]): void
	{
		const nodes: HTMLElement[] = Type.isElementNode(htmlElement) ? [htmlElement] : htmlElement;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement | HTMLInputElement): void => {
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
		[...this.#browsingNodes.keys()].forEach((node: HTMLElement): void => {
			this.unassignBrowse(node);
		});
	}

	assignDropzone(htmlElement: HTMLElement | HTMLElement[]): void
	{
		const nodes = Type.isElementNode(htmlElement) ? [htmlElement] : htmlElement;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement): void => {
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

		const event: BaseEvent = new BaseEvent({ data: { dragEvent } });
		this.emit(UploaderEvent.BEFORE_DROP, event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		getFilesFromDataTransfer(dragEvent.dataTransfer).then((files: File[]): void => {
			this.addFiles(files);
		}).catch((error) => {
			console.error('Uploader: data transfer error', error);
		});
	}

	#preventDefault(event: DragEvent): void
	{
		event.preventDefault();
	}

	unassignDropzone(htmlElement: HTMLElement | HTMLElement[]): void
	{
		const nodes: HTMLElement[] = Type.isElementNode(htmlElement) ? [htmlElement] : htmlElement;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement): void => {
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
		[...this.#dropNodes].forEach((node: HTMLElement): void => {
			this.unassignDropzone(node);
		});
	}

	assignPaste(htmlElement: HTMLElement | HTMLElement[]): void
	{
		const nodes: HTMLElement[] = Type.isElementNode(htmlElement) ? [htmlElement] : htmlElement;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement): void => {
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
		const clipboardData: DataTransfer = clipboardEvent.clipboardData;
		if (!clipboardData)
		{
			return;
		}

		const event: BaseEvent = new BaseEvent({ data: { clipboardEvent } });
		this.emit(UploaderEvent.BEFORE_PASTE, event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		if (isFilePasted(clipboardData))
		{
			clipboardEvent.preventDefault();

			getFilesFromDataTransfer(clipboardData).then((files: File[]): void => {
				this.addFiles(files);
			}).catch((error) => {
				console.error('Uploader: data transfer error', error);
			});
		}
	}

	unassignPaste(htmlElement: HTMLElement | HTMLElement[]): void
	{
		const nodes: HTMLElement[] = Type.isElementNode(htmlElement) ? [htmlElement] : htmlElement;
		if (!Type.isArray(nodes))
		{
			return;
		}

		nodes.forEach((node: HTMLElement): void => {
			if (this.#pastingNodes.has(node))
			{
				Event.unbind(node, 'paste', this.#onPasteHandler);
				this.#pastingNodes.delete(node);
			}
		});
	}

	unassignPasteAll(): void
	{
		[...this.#pastingNodes].forEach((node: HTMLElement): void => {
			this.unassignPaste(node);
		});
	}

	getHiddenFieldsContainer(): ?HTMLElement
	{
		let element = null;
		if (Type.isStringFilled(this.#hiddenFieldsContainer))
		{
			element = document.querySelector(this.#hiddenFieldsContainer);
			if (!Type.isElementNode(element))
			{
				console.error(`Uploader: a hidden field container was not found (${this.#hiddenFieldsContainer}).`);
			}
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

	setHiddenFieldName(name: string): void
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

	shouldAssignServerFile(): boolean
	{
		return this.#assignServerFile;
	}

	setAssignServerFile(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#assignServerFile = flag;
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
		return this.#files.filter((file: UploaderFile): boolean => file.isUploading()).length;
	}

	getPendingFileCount(): number
	{
		return this.#files.filter((file: UploaderFile): boolean => file.isReadyToUpload()).length;
	}

	static getImageExtensions(): Array<string>
	{
		return this.getGlobalOption(
			'imageExtensions',
			['.jpg', '.bmp', '.jpeg', '.jpe', '.gif', '.png', '.webp'],
		);
	}

	setAcceptOnlyImages(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#acceptOnlyImages = flag;
			if (flag)
			{
				this.acceptOnlyImages();
			}
		}
	}

	acceptOnlyImages(): void
	{
		const imageExtensions: string[] = Uploader.getImageExtensions();
		this.setAcceptedFileTypes(imageExtensions);
		this.#acceptOnlyImages = true;
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
		const types: string[] = Type.isString(fileTypes) ? fileTypes.split(',') : fileTypes;
		if (Type.isArray(types))
		{
			this.#acceptedFileTypes = [];
			this.#acceptOnlyImages = false;

			types.forEach((type: string) => {
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

			fileNames.forEach((fileName: string): void => {
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

		const maxParallelUploads: number = this.getMaxParallelUploads();
		const currentUploads: number = this.getUploadingFileCount();
		const pendingFiles: UploaderFile[] = this.#files.filter((file: UploaderFile): boolean => file.isReadyToUpload());
		const pendingUploads: number = pendingFiles.length;

		if (currentUploads < maxParallelUploads)
		{
			const limit: number = Math.min(maxParallelUploads - currentUploads, pendingFiles.length);
			for (let i = 0; i < limit; i++)
			{
				const pendingFile: UploaderFile = pendingFiles[i];
				pendingFile.upload();
			}
		}

		// All files are COMPLETE or FAILED
		if (currentUploads === 0 && pendingUploads === 0)
		{
			this.#status = UploaderStatus.STOPPED;
			this.emit(UploaderEvent.UPLOAD_COMPLETE);
		}
	}

	#loadNext(): void
	{
		const maxParallelLoads: number = this.getMaxParallelLoads();
		const currentLoads: number = this.#files.filter((file: UploaderFile): boolean => file.isLoading()).length;
		const pendingFiles: UploaderFile[] = this.#files.filter((file: UploaderFile) => {
			return file.getStatus() === FileStatus.ADDED && file.getOrigin() === FileOrigin.CLIENT;
		});

		if (currentLoads < maxParallelLoads)
		{
			const limit: number = Math.min(maxParallelLoads - currentLoads, pendingFiles.length);
			for (let i = 0; i < limit; i++)
			{
				const pendingFile: UploaderFile = pendingFiles[i];
				pendingFile.load();
			}
		}
	}

	#setHiddenField(file: UploaderFile): void
	{
		const container: ?HTMLElement = this.getHiddenFieldsContainer();
		if (!container || this.#hiddenFields.has(file.getId()))
		{
			return;
		}

		if (file.getOrigin() === FileOrigin.SERVER && !this.shouldAssignServerFile())
		{
			return;
		}

		const assignAsFile: boolean = (
			file.getOrigin() === FileOrigin.CLIENT
			&& !file.isUploadable()
			&& this.shouldAssignAsFile()
			&& canAppendFileToForm()
		);

		const input: HTMLInputElement = document.createElement('input');
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

			assignFileToInput(input, file.getBinary());
		}
		else if (file.getServerFileId() !== null)
		{
			input.value = file.getServerFileId();
		}

		Dom.append(input, container);
		this.#hiddenFields.set(file.getId(), input);

		this.#syncInputPositions();
	}

	#updateHiddenField(file: UploaderFile): void
	{
		const input: ?HTMLInputElement = this.#hiddenFields.get(file.getId());
		if (input && input.type === 'hidden')
		{
			if (file.getServerFileId() === null)
			{
				this.#resetHiddenField(file);
			}
			else
			{
				input.value = file.getServerFileId();
			}
		}
	}

	#resetHiddenField(file: UploaderFile): void
	{
		const input: ?HTMLInputElement = this.#hiddenFields.get(file.getId());
		if (input)
		{
			Dom.remove(input);
			this.#hiddenFields.delete(file.getId());
		}
	}

	#resetHiddenFields(): void
	{
		[...this.#hiddenFields.values()].forEach((input: HTMLInputElement): void => {
			Dom.remove(input);
		});

		this.#hiddenFields = [];
	}

	#syncInputPositions(): void
	{
		const container: ?HTMLElement = this.getHiddenFieldsContainer();
		if (!container)
		{
			return;
		}

		this.getFiles().forEach((file: UploaderFile): void => {
			const input: ?HTMLInputElement = this.#hiddenFields.get(file.getId());
			if (input)
			{
				Dom.append(input, container);
			}
		});
	}
}
