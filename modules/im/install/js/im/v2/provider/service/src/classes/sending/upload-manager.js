import {BaseEvent, EventEmitter} from 'main.core.events';
import {Uploader, PreviewManager} from 'im.v2.lib.uploader';
import {EventType} from 'im.v2.const';
import {Core} from 'im.v2.application.core';

export class UploadManager extends EventEmitter
{
	#uploader: Uploader;
	#store: Object;
	#restClient: Object;

	static eventNamespace = 'BX.Messenger.v2.Textarea.UploadManager';

	static events = {
		onFileUploadProgress: 'onFileUploadProgress',
		onFileUploadComplete: 'onFileUploadComplete',
		onFileUploadError: 'onFileUploadError',
		onFileUploadCancel: 'onFileUploadCancel',
	};

	constructor()
	{
		super();
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
		this.setEventNamespace(UploadManager.eventNamespace);

		this.onUploadCancelHandler = this.#onUploadCancel.bind(this);
		EventEmitter.subscribe(EventType.uploader.cancel, this.onUploadCancelHandler);

		this.initUploader();
	}

	#getFilePreview(file: File): Promise<{preview?: Object}>
	{
		return PreviewManager.get(file).then((preview: {blob: Blob, width: number, height: number}) => {
			return {preview};
		}).catch(error => {
			console.warn(`Couldn't get preview for file ${file.name}. Error: ${error}`);

			return {};
		});
	}

	initUploader()
	{
		this.#uploader = new Uploader();

		this.#uploader.subscribe(Uploader.EVENTS.startUpload, this.#onStartUpload.bind(this));
		this.#uploader.subscribe(Uploader.EVENTS.progressUpdate, this.#onProgress.bind(this));
		this.#uploader.subscribe(Uploader.EVENTS.complete, this.#onComplete.bind(this));
		this.#uploader.subscribe(Uploader.EVENTS.fileMaxSizeExceeded, this.#onUploadError.bind(this));
		this.#uploader.subscribe(Uploader.EVENTS.uploadFileError, this.#onUploadError.bind(this));
		this.#uploader.subscribe(Uploader.EVENTS.createFileError, this.#onUploadError.bind(this));
	}

	#onStartUpload(event: BaseEvent)
	{
		this.emit(UploadManager.events.onFileUploadProgress, event);
	}

	#onProgress(event: BaseEvent)
	{
		this.emit(UploadManager.events.onFileUploadProgress, event);
	}

	#onComplete(event: BaseEvent)
	{
		this.emit(UploadManager.events.onFileUploadComplete, event);
	}

	#onUploadError(event: BaseEvent)
	{
		this.emit(UploadManager.events.onFileUploadError, event);
	}

	#onUploadCancel(event: BaseEvent)
	{
		this.emit(UploadManager.events.onFileUploadCancel, event);
	}

	addUploadTask(temporaryFileId: string, file: File, diskFolderId: number): Promise
	{
		return this.#getFilePreview(file).then(({preview}) => {
			const previewBlob = preview ? {previewBlob: preview.blob} : {};

			this.#uploader.addTask({
				taskId: temporaryFileId,
				fileData: file,
				fileName: file.name,
				diskFolderId: diskFolderId,
				generateUniqueName: true,
				...previewBlob
			});

			return {taskId: temporaryFileId, file: file, preview: preview};
		});
	}

	cancel(taskId: string)
	{
		this.#uploader.deleteTask(taskId);
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.uploader.cancel, this.onUploadCancelHandler);
	}
}